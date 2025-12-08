<?php
require_once __DIR__ . '/../config/DbConn.php';
require_once __DIR__ . '/../dao/PostDAO.php';
require_once __DIR__ . '/../config/EventDispatcher.php';

class ProfileDAO
{
    private mysqli $conn;
    

    public function __construct()
    {
        $this->conn = Database::getConnection();
    }

    public function getUserData(int $userId): ?array
    {
        $stmt = $this->conn->prepare("SELECT username, email, points, status FROM user WHERE user_id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $result ?: null;
    }

    public function getFollowerCount(int $userId): int
    {
        $stmt = $this->conn->prepare(
            "SELECT 
                COUNT(*) AS friends
            FROM friend_list
            WHERE 
                (user_id_1 = ? OR user_id_2 = ?)
            AND 
                status = 'friends';"
        );
        $stmt->bind_param("ii", $userId, $userId);
        $stmt->execute();
        $count = $stmt->get_result()->fetch_assoc()['friends'] ?? 0;
        $stmt->close();
        return (int) $count;
    }

    public function getUserPosts(int $userId): array
    {
        $postDAO = new PostDAO();
        return $postDAO->getPostsByUser($userId);
    }

    public function __destruct()
    {
        Database::close();
    }
}
?>