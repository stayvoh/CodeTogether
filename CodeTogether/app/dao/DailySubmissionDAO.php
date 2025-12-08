<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/DbConn.php';

class DailySubmissionDAO
{
    public function hasSubmittedToday(int $userId, string $problemTitle): bool
    {
        $conn = Database::getConnection();
        $today = date('Y-m-d');
        
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM daily_submissions WHERE user_id = ? AND submission_date = ? AND problem_title = ?");
        $stmt->bind_param("iss", $userId, $today, $problemTitle);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        return $result['count'] > 0;
    }
    
    public function recordSubmission(int $userId, string $problemTitle, int $score, bool $correct, string $code, string $language): array
    {
        $conn = Database::getConnection();
        $today = date('Y-m-d');
        
        // Record the submission
        $stmt = $conn->prepare("INSERT INTO daily_submissions (user_id, submission_date, problem_title, score, correct, code_text, language) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issiiss", $userId, $today, $problemTitle, $score, $correct, $code, $language);
        $stmt->execute();
        $stmt->close();
        
        // Update streak information
        return $this->updateUserStreak($userId);
    }
    
    public function getUserSubmissionHistory(int $userId, int $limit = 10): array
    {
        $conn = Database::getConnection();
        
        $stmt = $conn->prepare("SELECT * FROM daily_submissions WHERE user_id = ? ORDER BY submission_date DESC, submission_time DESC LIMIT ?");
        $stmt->bind_param("ii", $userId, $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $submissions = [];
        while ($row = $result->fetch_assoc()) {
            $submissions[] = $row;
        }
        
        $stmt->close();
        return $submissions;
    }
    
    public function updateUserStreak(int $userId): array
    {
        $conn = Database::getConnection();
        $today = date('Y-m-d');
        $yesterday = date('Y-m-d', strtotime('-1 day'));
        
        // Get user's current streak info
        $stmt = $conn->prepare("SELECT current_streak, longest_streak, last_submission_date FROM user WHERE user_id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        $currentStreak = $result['current_streak'] ?? 0;
        $longestStreak = $result['longest_streak'] ?? 0;
        $lastSubmissionDate = $result['last_submission_date'] ?? null;
        
        // Calculate new streak
        if ($lastSubmissionDate === $yesterday) {
            // Submitted yesterday, increment streak
            $currentStreak++;
        } elseif ($lastSubmissionDate !== $today) {
            // Didn't submit yesterday, reset streak to 1
            $currentStreak = 1;
        }
        // If already submitted today, streak doesn't change
        
        // Update longest streak if needed
        $isNewRecord = false;
        if ($currentStreak > $longestStreak) {
            $longestStreak = $currentStreak;
            $isNewRecord = true;
        }
        
        // Update user table
        $stmt = $conn->prepare("UPDATE user SET current_streak = ?, longest_streak = ?, last_submission_date = ? WHERE user_id = ?");
        $stmt->bind_param("iisi", $currentStreak, $longestStreak, $today, $userId);
        $stmt->execute();
        $stmt->close();
        
        return [
            'current' => $currentStreak,
            'longest' => $longestStreak,
            'isNewRecord' => $isNewRecord,
            'milestone' => $this->isMilestone($currentStreak)
        ];
    }
    
    public function getUserStreak(int $userId): array
    {
        $conn = Database::getConnection();
        
        $stmt = $conn->prepare("SELECT current_streak, longest_streak, last_submission_date FROM user WHERE user_id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        if (!$result) {
            return [
                'current' => 0,
                'longest' => 0,
                'lastSubmissionDate' => null
            ];
        }
        
        return [
            'current' => $result['current_streak'] ?? 0,
            'longest' => $result['longest_streak'] ?? 0,
            'lastSubmissionDate' => $result['last_submission_date']
        ];
    }
    
    public function getDailyStats(string $date): array
    {
        $conn = Database::getConnection();
        
        $stmt = $conn->prepare("SELECT COUNT(*) as total_submissions, COUNT(DISTINCT user_id) as unique_users, AVG(score) as avg_score FROM daily_submissions WHERE submission_date = ?");
        $stmt->bind_param("s", $date);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        return [
            'totalSubmissions' => $result['total_submissions'] ?? 0,
            'uniqueUsers' => $result['unique_users'] ?? 0,
            'averageScore' => round($result['avg_score'] ?? 0, 2)
        ];
    }
    
    private function isMilestone(int $streak): bool
    {
        $milestones = [1, 3, 5, 7, 10, 14, 21, 30, 50, 75, 100, 150, 200, 365];
        return in_array($streak, $milestones);
    }
}