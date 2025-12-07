<?php
declare(strict_types=1);
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../config/DbConn.php';

class UserDAO
{

    public function addUser(string $username, string $password, string $email, int $roleID): void
    {
        $conn = Database::getConnection();
        $stmt = $conn->prepare("INSERT INTO user (username, password, email,role_id) VALUES (?, ?, ?,?)");
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt->bind_param("sssi", $username, $hashedPassword, $email, $roleID);
        $stmt->execute();

        $stmt->close();
    }

    public function getUserByID(int $userID): User|null
    {
        $conn = Database::getConnection();
        $stmt = $conn->prepare("SELECT * FROM user WHERE user_id = ?;");
        $stmt->bind_param("i", $userID);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = null;
        if ($row = $result->fetch_assoc()) {
            $user = new User();
            $user->load($row);
        }
        $stmt->close();

        return $user;
    }

    public function updateUsername(int $userID, string $newUsername): bool
    {
        $conn = Database::getConnection();
        $stmt = $conn->prepare("UPDATE user SET username = ? WHERE user_id = ?");
        $stmt->bind_param("si", $newUsername, $userID);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    public function updateEmail(int $userID, string $newEmail): bool
    {
        $conn = Database::getConnection();
        $stmt = $conn->prepare("UPDATE user SET email = ? WHERE user_id = ?");
        $stmt->bind_param("si", $newEmail, $userID);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    public function updatePassword(int $userID, string $newPassword): bool
    {
        $conn = Database::getConnection();
        $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE user SET password = ? WHERE user_id = ?");
        $stmt->bind_param("si", $hashed, $userID);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    public function updatePreferences(int $userID, int $rainEnabled, string $theme): bool
    {
        $conn = Database::getConnection();
        $stmt = $conn->prepare("UPDATE user SET rain_enabled = ?, theme = ? WHERE user_id = ?");
        $stmt->bind_param("isi", $rainEnabled, $theme, $userID);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    public function getPreferences(int $userID): array
    {
        $conn = Database::getConnection();
        $stmt = $conn->prepare("SELECT theme, rain_enabled FROM user WHERE user_id = ?");
        $stmt->bind_param("i", $userID);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return [
            'theme' => $result['theme'] ?? 'dark',
            'rain_enabled' => (bool) ($result['rain_enabled'] ?? 0)
        ];
    }



    public function updateAboutMe(int $userID, string $aboutMe): void
    {
        $conn = Database::getConnection();
        $stmt = $conn->prepare("UPDATE user SET about_me = ? WHERE user_id = ?;");
        $stmt->bind_param("si", $aboutMe, $userID);
        $stmt->execute();
        $stmt->close();
    }

    public function getAboutMe(int $userId): string
    {
        $conn = Database::getConnection();
        $stmt = $conn->prepare("SELECT about_me FROM user WHERE user_id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        return $result['about_me'];
    }

    public function getFriendUsers(array $friends): array
    {
        if (empty($friends))
            return [];

        $ids = [];
        foreach ($friends as $friend)
            $ids[] = $friend->getFriendID();
        $inClause = "('" . implode("','", $ids) . "')";

        $conn = Database::getConnection();
        $stmt = $conn->prepare("SELECT * FROM user WHERE user_id in $inClause order by username asc;");
        $stmt->execute();
        $result = $stmt->get_result();
        $users = [];
        while ($row = $result->fetch_assoc()) {
            $user = new User();
            $user->load($row);
            $users[] = $user;
        }
        $stmt->close();

        return $users;
    }


    public function getUserByName(string $username): User|null
    {
        $conn = Database::getConnection();
        $stmt = $conn->prepare("SELECT * FROM user WHERE username = ?;");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = null;
        if ($row = $result->fetch_assoc()) {
            $user = new User();
            $user->load($row);
        }
        $stmt->close();

        return $user;
    }

    public function updateUserStatus(string $status, int $userID): bool
    {
        $conn = Database::getConnection();
        $stmt = $conn->prepare("UPDATE user SET status = ?, latest_update = NOW() WHERE user_id = ?");
        $stmt->bind_param("si", $status, $userID);
        $success = $stmt->execute();
        $stmt->close();
        return $success;
    }


    public function getUserStatus(int $userID): ?array
    {
        $conn = Database::getConnection();
        $stmt = $conn->prepare("SELECT status, latest_update FROM user WHERE user_id = ?");
        $stmt->bind_param("i", $userID);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        return $row ?: null;
    }


    public function updateUser(User $user): void
    {
        $conn = Database::getConnection();

        $stmt = $conn->prepare("SELECT password FROM user WHERE user_id = ?");
        $stmt->bind_param("i", $user->getUserID());
        $stmt->execute();
        $result = $stmt->get_result();
        $existing = $result->fetch_assoc();
        $stmt->close();

        $newPassword = $user->getPassword();

        if ($newPassword !== $existing['password'] && !password_get_info($newPassword)['algo']) {
            $newPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        }

        $stmt = $conn->prepare("UPDATE user SET username = ?, password = ?, email = ?, points = ?, status = ? WHERE user_id = ?");
        $stmt->bind_param(
            "sssiis",
            $user->getUsername(),
            $newPassword,
            $user->getEmail(),
            $user->getPoints(),
            $user->getStatus(),
            $user->getUserID()
        );
        $stmt->execute();

        $stmt->close();
    }

    public function checkExistingEmail(string $email): bool
    {
        $conn = Database::getConnection();
        $stmt = $conn->prepare("SELECT count(*) as cnt FROM user WHERE email = ?;");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $result['cnt'] > 0;
    }

    public function checkExistingUser(string $user): bool
    {
        $conn = Database::getConnection();
        $stmt = $conn->prepare("SELECT count(*) as cnt FROM user WHERE username = ?;");
        $stmt->bind_param("s", $user);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $result['cnt'] > 0;

    }

    public function addPoints(int $userID, int $points): bool
    {
        $conn = Database::getConnection();
        $query = "UPDATE user SET points = points + ? WHERE user_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ii", $points, $userID);
        return $stmt->execute();
    }



    public function authenticate(string $identefier, string $passwd): User|null
    {
        $conn = Database::getConnection();
        $stmt = $conn->prepare("SELECT * FROM user WHERE (email = ? OR username = ?);");
        $stmt->bind_param("ss", $identefier, $identefier);
        $stmt->execute();
        $result = $stmt->get_result();

        $user = null;

        if ($row = $result->fetch_assoc()) {
            if (password_verify($passwd, $row['password'])) {
                $user = new User();
                $user->load($row);
            }
        }

        $stmt->close();


        return $user;
    }


    public function searchUsersByName(string $searchTerm): array
    {
        $conn = Database::getConnection();

        $stmt = $conn->prepare("SELECT * FROM user WHERE username LIKE CONCAT('%', ?, '%');");
        $stmt->bind_param("s", $searchTerm);
        $stmt->execute();

        $result = $stmt->get_result();
        $users = [];


        while ($row = $result->fetch_assoc()) {
            $user = new User();
            $user->load($row);
            $users[] = $user;
        }

        $stmt->close();

        return $users;
    }

    public function updateProfilePicture(int $userID, string $fileName): bool
    {
        $conn = Database::getConnection();
        $stmt = $conn->prepare("UPDATE user SET profile_picture = ? WHERE user_id = ?");
        $stmt->bind_param("si", $fileName, $userID);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    public function getProfilePicture(int $userID): ?string
    {
        $conn = Database::getConnection();
        $stmt = $conn->prepare("SELECT profile_picture FROM user WHERE user_id = ?");
        $stmt->bind_param("i", $userID);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        return $row['profile_picture'];
    }

    public function getTopUsersByPoints(int $limit = 3): array
    {
        $conn = Database::getConnection();
        $stmt = $conn->prepare("SELECT * FROM user ORDER BY points DESC LIMIT ?");
        $stmt->bind_param("i", $limit);
        $stmt->execute();

        $result = $stmt->get_result();
        $users = [];

        while ($row = $result->fetch_assoc()) {
            $user = new User();
            $user->load($row);
            $users[] = $user;
        }

        $stmt->close();

        return $users;
    }

    public function updateProfileMusic(int $userID, ?string $fileName): bool
    {
        $conn = Database::getConnection();
        $stmt = $conn->prepare("UPDATE user SET profile_music = ? WHERE user_id = ?");
        $stmt->bind_param("si", $fileName, $userID);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    public function getProfileMusic(int $userID): ?string
    {
        $conn = Database::getConnection();
        $stmt = $conn->prepare("SELECT profile_music FROM user WHERE user_id = ?");
        $stmt->bind_param("i", $userID);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        return $row['profile_music'] ?? null;
    }


}
?>