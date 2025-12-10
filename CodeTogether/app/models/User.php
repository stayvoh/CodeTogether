<?php
declare(strict_types=1);

class User implements JsonSerializable
{
    private int $userID;
    private int $roleID;
    private string $username;
    private string $email;
    private string $profilePicture;
    private int $points;
    private bool $isDeleted;
    private string $status;
    private string $aboutMe;
    private ?DateTime $createdOn;
    private ?DateTime $latestUpdate;
    private string $password;
    private int $requestInitiatorID;
    private ?string $profileMusic = null;

    public function __construct(int $userID = -1, int $roleID = -1, string $username = '', int $points = -1, string $status = '', string $email = '', string $password = '', bool $isDeleted = false, ?DateTime $createdOn = null, ?DateTime $latestUpdate = null, int $requestInitiatorID = -1, string $aboutMe = '', string $profilePicture = '')
    {
        $this->userID = $userID;
        $this->roleID = $roleID;
        $this->username = $username;
        $this->points = $points;
        $this->status = $status;
        $this->email = $email;
        $this->password = $password;
        $this->isDeleted = $isDeleted;
        $this->createdOn = $createdOn ?? new DateTime();
        $this->latestUpdate = $latestUpdate ?? new DateTime();
        $this->requestInitiatorID = $requestInitiatorID;
        $this->aboutMe = $aboutMe;
        $this->profilePicture = $profilePicture;
    }

    public function load(array $row): void
    {
        $this->userID = $row['user_id'];
        $this->roleID = $row['role_id'];
        $this->username = $row['username'];
        $this->email = $row['email'];
        $this->points = $row['points'];
        $this->status = $row['status'];
        $this->isDeleted = (bool) $row['is_deleted'];
        $this->createdOn = new DateTime($row['created_on']);
        $this->latestUpdate = new DateTime($row['latest_update']);
        $this->password = $row['password'];
        $this->aboutMe = $row['about_me'];
        $this->profilePicture = $row['profile_picture'];
        if (array_key_exists('profile_music', $row)) {
            $this->profileMusic = $row['profile_music'];
        }
    }

    public function jsonSerialize(): array
    {
        return array(
            'userID' => $this->userID,
            'roleID' => $this->roleID,
            'username' => $this->username,
            'email' => $this->email,
            'points' => $this->points,
            'status' => $this->status,
            'isDeleted' => $this->isDeleted,
            'createdOn' => $this->createdOn,
            'latestUpdate' => $this->latestUpdate,
            'password' => $this->password,
            'requestInitiatorID' => $this->requestInitiatorID,
            'aboutMe' => $this->aboutMe,
            'profilePicture' => $this->profilePicture
        );
    }


    public function setAboutMe(string $aboutMe): void
    {
        $this->aboutMe = $aboutMe;
    }
    public function getAboutme(): string
    {
        return $this->aboutMe;
    }
    public function setRequestInitiatorID(int $id): void
    {
        $this->requestInitiatorID = $id;
    }

    public function getRequestInitiatorID(): int
    {
        return $this->requestInitiatorID;
    }

    public function setUserID(int $userID): void
    {
        $this->userID = $userID;
    }

    public function getUserID(): int
    {
        return $this->userID;
    }

    public function setRoleID(int $roleID): void
    {
        $this->roleID = $roleID;
    }

    public function getRoleID(): int
    {
        return $this->roleID;
    }

    public function setUsername(string $username): void
    {
        $this->username = $username;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setPoints(int $points): void
    {
        $this->points = $points;
    }

    public function getPoints(): int
    {
        return $this->points;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setCreatedOn(DateTime $createdOn): void
    {
        $this->createdOn = $createdOn;
    }

    public function getCreatedOn(): DateTime
    {
        return $this->createdOn;
    }

    public function setLatestUpdate(DateTime $latestUpdate): void
    {
        $this->latestUpdate = $latestUpdate;
    }

    public function getLatestUpdate(): DateTime
    {
        return $this->latestUpdate;
    }

    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setIsDeleted(bool $isDeleted): void
    {
        $this->isDeleted = $isDeleted;
    }

    public function getIsDeleted(): bool
    {
        return $this->isDeleted;
    }

    public function setProfilePicture(string $profilePicture): void
    {
        $this->profilePicture = $profilePicture;
    }

    public function getProfilePicture(): string
    {
        return $this->profilePicture;
    }

    public function getProfileMusic(): ?string {
        return $this->profileMusic;
    }

    public function setProfileMusic(?string $m): void {
        $this->profileMusic = $m;
    }


}
?>