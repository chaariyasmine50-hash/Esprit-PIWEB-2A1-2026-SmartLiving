<?php
/**
 * models/UserModel.php
 * Represents ONE user. Only private attributes + getters/setters.
 * No SQL query and no business logic here (that is the Controller's job).
 */

class UserModel
{
    // ------------------------------------------------------------
    // ATTRIBUTES
    // ------------------------------------------------------------

    private int $id = 0;
    private string $username = '';
    private string $firstName = '';
    private string $lastName = '';
    private string $email = '';
    private string $password = '';
    private string $role = 'user';      // 'user' (citizen) or 'admin'
    private string $status = 'active';  // 'active' or 'blocked'
    private int $points = 0;            // green points (Green Rewards module)
    private string $createdAt = '';

    // ------------------------------------------------------------
    // CONSTRUCTOR
    // ------------------------------------------------------------

    public function __construct()
    {
    }

    // ------------------------------------------------------------
    // GETTERS
    // ------------------------------------------------------------

    public function getId(): int
    {
        return $this->id;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function getFullName(): string
    {
        return trim($this->firstName . ' ' . $this->lastName);
    }

    public function getInitials(): string
    {
        $f = $this->firstName !== '' ? $this->firstName : $this->username;
        $l = $this->lastName;
        return strtoupper(mb_substr($f, 0, 1) . mb_substr($l, 0, 1));
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getRole(): string
    {
        return $this->role;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getPoints(): int
    {
        return $this->points;
    }

    public function getCreatedAt(): string
    {
        return $this->createdAt;
    }

    // ------------------------------------------------------------
    // SETTERS
    // ------------------------------------------------------------

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function setUsername(string $username): void
    {
        $this->username = $username;
    }

    public function setFirstName(string $firstName): void
    {
        $this->firstName = $firstName;
    }

    public function setLastName(string $lastName): void
    {
        $this->lastName = $lastName;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    public function setRole(string $role): void
    {
        $this->role = $role;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    public function setPoints(int $points): void
    {
        $this->points = $points;
    }

    public function setCreatedAt(string $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    // ------------------------------------------------------------
    // CONVERT ARRAY (SQL row) TO OBJECT
    // ------------------------------------------------------------

    public static function fromArray(array $row): self
    {
        $user = new self();

        $user->setId((int) ($row['id'] ?? 0));
        $user->setUsername((string) ($row['username'] ?? ''));
        $user->setFirstName((string) ($row['first_name'] ?? ''));
        $user->setLastName((string) ($row['last_name'] ?? ''));
        $user->setEmail((string) ($row['email'] ?? ''));
        $user->setPassword((string) ($row['password'] ?? ''));
        $user->setRole((string) ($row['role'] ?? 'user'));
        $user->setStatus((string) ($row['status'] ?? 'active'));
        $user->setPoints((int) ($row['points'] ?? 0));
        $user->setCreatedAt((string) ($row['created_at'] ?? ''));

        return $user;
    }
}
