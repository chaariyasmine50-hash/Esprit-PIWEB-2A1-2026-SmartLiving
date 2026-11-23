<?php
/**
 * controllers/AuthController.php
 * Authentication, sessions, roles and security (CSRF).
 * All SQL queries use prepared statements (protection against SQL injection).
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/UserModel.php';

class AuthController
{
    private PDO $db;

    public const ROLES    = ['user', 'admin'];
    public const STATUSES = ['active', 'blocked'];

    public function __construct()
    {
        $this->db = Database::getConnexion();
    }

    // ==============================================================
    // SESSION STATE
    // ==============================================================

    public static function isAuthenticated(): bool
    {
        return !empty($_SESSION['user_id']);
    }

    // ==============================================================
    // INPUT VALIDATION (server side, shared with UserController)
    // ==============================================================

    /**
     * Validates the identity fields of a user.
     * Returns an array of error messages (empty array = valid).
     */
    public static function validateIdentity(array $data): array
    {
        $errors = [];

        $firstName = trim((string) ($data['first_name'] ?? ''));
        $lastName  = trim((string) ($data['last_name'] ?? ''));
        $username  = trim((string) ($data['username'] ?? ''));
        $email     = trim((string) ($data['email'] ?? ''));

        if ($firstName === '' || $lastName === '' || $username === '' || $email === '') {
            $errors[] = "All fields are required.";
        }
        if ($firstName !== '' && !preg_match("/^[\p{L}][\p{L}\s'\-]{1,49}$/u", $firstName)) {
            $errors[] = "First name must be 2 to 50 letters.";
        }
        if ($lastName !== '' && !preg_match("/^[\p{L}][\p{L}\s'\-]{1,49}$/u", $lastName)) {
            $errors[] = "Last name must be 2 to 50 letters.";
        }
        if (!preg_match('/^[a-zA-Z0-9_\-]{3,50}$/', $username)) {
            $errors[] = "Username must be 3 to 50 characters (letters, digits, _ or -).";
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 150) {
            $errors[] = "Invalid email address.";
        }

        return $errors;
    }

    /**
     * Validates a new password and its confirmation.
     */
    public static function validatePassword(string $password, string $confirm): array
    {
        $errors = [];
        if (strlen($password) < 8) {
            $errors[] = "Password must be at least 8 characters long.";
        } elseif (!preg_match('/[A-Za-z]/', $password) || !preg_match('/[0-9]/', $password)) {
            $errors[] = "Password must contain at least one letter and one digit.";
        }
        if ($password !== $confirm) {
            $errors[] = "Passwords do not match.";
        }
        return $errors;
    }

    // ==============================================================
    // DATA ACCESS (prepared statements - SQL injection protection)
    // ==============================================================

    private function existsByEmailOrUsername(string $email, string $username): bool
    {
        $stmt = $this->db->prepare('SELECT id FROM users WHERE email = :email OR username = :username LIMIT 1');
        $stmt->execute(['email' => $email, 'username' => $username]);
        return (bool) $stmt->fetch();
    }

    private function insertUser(UserModel $user): bool
    {
        $stmt = $this->db->prepare(
            'INSERT INTO users (username, first_name, last_name, email, password, role)
             VALUES (:username, :first_name, :last_name, :email, :password, :role)'
        );

        return $stmt->execute([
            'username'   => $user->getUsername(),
            'first_name' => $user->getFirstName(),
            'last_name'  => $user->getLastName(),
            'email'      => $user->getEmail(),
            'password'   => $user->getPassword(),
            'role'       => $user->getRole(),
        ]);
    }

    // ==============================================================
    // REGISTER
    // ==============================================================

    public function register(array $post): array
    {
        $errors = [];

        if (!$this->verifyCsrfToken($post['csrf_token'] ?? null)) {
            $errors[] = "Invalid security token. Please try again.";
            return $errors;
        }

        $firstName       = trim($post['first_name'] ?? '');
        $lastName        = trim($post['last_name'] ?? '');
        $username        = trim($post['username'] ?? '');
        $email           = trim($post['email'] ?? '');
        $password        = $post['password'] ?? '';
        $passwordConfirm = $post['password_confirm'] ?? '';

        $errors = array_merge(
            self::validateIdentity($post),
            self::validatePassword($password, $passwordConfirm)
        );

        if (!empty($errors)) {
            return $errors;
        }

        if ($this->existsByEmailOrUsername($email, $username)) {
            $errors[] = "This email or username is already taken.";
            return $errors;
        }

        // Secure password hashing (bcrypt via password_hash)
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Build the entity (role forced to 'user' - no self-promotion to admin)
        $user = new UserModel();
        $user->setUsername($username);
        $user->setFirstName($firstName);
        $user->setLastName($lastName);
        $user->setEmail($email);
        $user->setPassword($hashedPassword);
        $user->setRole('user');

        if (!$this->insertUser($user)) {
            $errors[] = "An error occurred while creating your account.";
        }

        return $errors; // empty array = success
    }

    // ==============================================================
    // LOGIN
    // ==============================================================

    private function findByEmail(string $email): ?UserModel
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => $email]);
        $row = $stmt->fetch();

        return $row ? UserModel::fromArray($row) : null;
    }

    public function login(array $post): array
    {
        $errors = [];

        if (!$this->verifyCsrfToken($post['csrf_token'] ?? null)) {
            $errors[] = "Invalid security token. Please try again.";
            return $errors;
        }

        $email    = trim($post['email'] ?? '');
        $password = $post['password'] ?? '';

        if ($email === '' || $password === '') {
            $errors[] = "Please enter your email and password.";
            return $errors;
        }

        $user = $this->findByEmail($email);

        // Deliberately generic message (never reveal whether the email exists)
        if (!$user || !password_verify($password, $user->getPassword())) {
            $errors[] = "Incorrect email or password.";
            return $errors;
        }

        // Business rule: a blocked account cannot log in
        if ($user->getStatus() === 'blocked') {
            $errors[] = "Your account has been blocked. Please contact the administrator.";
            return $errors;
        }

        // Regenerate the session ID: protects against session fixation
        session_regenerate_id(true);

        $_SESSION['user_id']       = $user->getId();
        $_SESSION['username']      = $user->getUsername();
        $_SESSION['role']          = $user->getRole();
        $_SESSION['last_activity'] = time();

        return []; // success
    }

    // ==============================================================
    // LOGOUT
    // ==============================================================

    public function logout(): void
    {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        session_destroy();
    }

    // ==============================================================
    // USER SPACE (frontoffice)
    // ==============================================================

    public function findById(int $id): ?UserModel
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        return $row ? UserModel::fromArray($row) : null;
    }

    /**
     * Redirects and stops execution if the user is not authenticated
     */
    public static function requireAuth(): void
    {
        if (!self::isAuthenticated()) {
            header('Location: index.php?action=login');
            exit;
        }
    }

    // ==============================================================
    // ROLE CONTROL
    // ==============================================================

    public static function hasRole(string $role): bool
    {
        return self::isAuthenticated() && ($_SESSION['role'] ?? null) === $role;
    }

    /**
     * Stops execution (HTTP 403) if the user doesn't have the required role
     */
    public static function requireRole(string $role): void
    {
        self::requireAuth();
        if (($_SESSION['role'] ?? null) !== $role) {
            http_response_code(403);
            require __DIR__ . '/../views/error_403.php';
            exit;
        }
    }

    // ==============================================================
    // ADMIN BACKOFFICE DATA
    // ==============================================================

    /**
     * Retrieves all users (used by the admin backoffice)
     * @return UserModel[]
     */
    public function getAllUsers(): array
    {
        $stmt = $this->db->query('SELECT * FROM users ORDER BY id ASC');
        $rows = $stmt->fetchAll();

        return array_map(fn(array $row) => UserModel::fromArray($row), $rows);
    }

    /**
     * Counts users by role (used by the admin dashboard stats)
     */
    public function countByRole(string $role): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM users WHERE role = :role');
        $stmt->execute(['role' => $role]);
        return (int) $stmt->fetchColumn();
    }

    public function countByStatus(string $status): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM users WHERE status = :status');
        $stmt->execute(['status' => $status]);
        return (int) $stmt->fetchColumn();
    }

    public function countAllUsers(): int
    {
        return (int) $this->db->query('SELECT COUNT(*) FROM users')->fetchColumn();
    }

    // ==============================================================
    // CSRF
    // ==============================================================

    public function getCsrfToken(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public function verifyCsrfToken(?string $token): bool
    {
        if (!$token || empty($_SESSION['csrf_token'])) {
            return false;
        }
        return hash_equals($_SESSION['csrf_token'], $token);
    }

    // ==============================================================
    // FLASH MESSAGES (one-time messages shown after a redirect)
    // ==============================================================

    public static function flash(string $type, string $message): void
    {
        $_SESSION['flash'] = ['type' => $type, 'message' => $message];
    }

    public static function takeFlash(): ?array
    {
        $flash = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);
        return $flash;
    }
}
