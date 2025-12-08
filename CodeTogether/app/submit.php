<?php
header('Content-Type: application/json');
session_start();
require_once __DIR__ . '/dao/UserDAO.php';
require_once __DIR__ . '/dao/DailySubmissionDAO.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Invalid request method']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if (!$input || !isset($input['code'], $input['language'], $input['problem'])) {
    echo json_encode(['error' => 'Invalid input']);
    exit;
}

// Handle streak-only check
if (isset($input['checkStreakOnly']) && $input['checkStreakOnly']) {
    $user = $_SESSION['usercreds']['username'] ?? 'guest';
    if ($user !== 'guest') {
        $userDAO = new UserDAO();
        $userObj = $userDAO->getUserByName($user);
        if ($userObj) {
            $dailySubmissionDAO = new DailySubmissionDAO();
            $streakInfo = $dailySubmissionDAO->getUserStreak($userObj->getUserID());
            echo json_encode(['streak' => $streakInfo]);
            exit;
        }
    }
    echo json_encode(['streak' => ['current' => 0, 'longest' => 0]]);
    exit;
}

$user = $_SESSION['usercreds']['username'] ?? 'guest';
$code = $input['code'];
$language = $input['language'];
$problem = $input['problem'];

if (!$problem) {
    echo json_encode(['error' => 'Problem data invalid']);
    exit;
}

// Get user object
$userDAO = new UserDAO();
$userObj = $userDAO->getUserByName($user);
if (!$userObj) {
    echo json_encode(['error' => 'User not found']);
    exit;
}

// Check if user already submitted today for this problem
$dailySubmissionDAO = new DailySubmissionDAO();
if ($dailySubmissionDAO->hasSubmittedToday($userObj->getUserID(), $problem['title'])) {
    // Get user's current streak info
    $streakInfo = $dailySubmissionDAO->getUserStreak($userObj->getUserID());
    
    echo json_encode([
        'error' => 'You have already submitted a solution for today\'s problem. Try again tomorrow!',
        'alreadySubmitted' => true,
        'streak' => [
            'current' => $streakInfo['current'],
            'longest' => $streakInfo['longest'],
            'isNewRecord' => false,
            'milestone' => false
        ]
    ]);
    exit;
}

// AI judge
$apiKey = getenv('API_KEY');
if (!$apiKey) {
    echo json_encode(['error' => 'API key missing']);
    exit;
}

$prompt = <<<EOT
You are a coding challenge judge.  
Problem:
Title: {$problem['title']}
Description: {$problem['description']}
Example Input: {$problem['exampleInput']}
Example Output: {$problem['exampleOutput']}

User submission in {$language}:

{$code}

Return JSON ONLY:
{
  "score": <0-100>,
  "correct": <true|false>,
  "feedback": "string"
}
EOT;

$ch = curl_init("https://api.openai.com/v1/chat/completions");
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json",
    "Authorization: Bearer $apiKey"
]);

$messages = [
    ["role" => "system", "content" => "You are a strict but fair coding judge."],
    ["role" => "user", "content" => $prompt]
];

curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POSTFIELDS => json_encode([
        "model" => "gpt-5-nano",
        "messages" => $messages
    ])
]);

$response = curl_exec($ch);
curl_close($ch);

$result = json_decode($response, true);
$aiReply = $result['choices'][0]['message']['content'] ?? '{}';
$judgement = json_decode($aiReply, true);

if (!$judgement) {
    echo json_encode(['error' => 'AI did not return valid JSON']);
    exit;
}

// Update user points in database
$score = $judgement['score'] ?? 0;
if ($userObj) {
    $userDAO->addPoints($userObj->getUserID(), $score);
    
    // Record the submission and update streak
    $streakData = $dailySubmissionDAO->recordSubmission(
        $userObj->getUserID(),
        $problem['title'],
        $score,
        $judgement['correct'] ?? false,
        $code,
        $language
    );
} else {
    $streakData = [
        'current' => 0,
        'longest' => 0,
        'isNewRecord' => false,
        'milestone' => false
    ];
}

// Get top users from database
$topUsers = $userDAO->getTopUsersByPoints(3);
$leaderboard = [];
foreach ($topUsers as $topUser) {
    $leaderboard[] = [
        'username' => $topUser->getUsername(),
        'points' => $topUser->getPoints()
    ];
}

// Return result + top 3 + streak info
echo json_encode([
    'score' => $judgement['score'] ?? 0,
    'correct' => $judgement['correct'] ?? false,
    'feedback' => $judgement['feedback'] ?? 'No feedback provided',
    'leaderboard' => array_slice($leaderboard, 0, 3, true),
    'streak' => $streakData,
    'submission' => [
        'alreadySubmitted' => false,
        'canSubmit' => true
    ]
]);
?>
