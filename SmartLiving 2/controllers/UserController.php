<?php
/**
 * controllers/UserController.php
 * User management: full CRUD (admin backoffice) + profile management (frontoffice).
 * Business logic + prepared SQL queries only. Views never touch the database.
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/AuthController.php';

class UserController
{
    private PDO $db;
    private AuthController $auth;

    public function __construct(AuthController $auth)
    {
        $this->db   = Database::getConnexion();
        $this->auth = $auth;
    }

    // ==============================================================
    // READ
    // ==============================================================

    public function findById(int $id): ?UserModel
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        return $row ? UserModel::fromArray($row) : null;
    }

    /**
     * Search users with optional filters (keyword, role, status).
     * @return UserModel[]
     */
    public function search(string $keyword = '', string $role = '', string $status = ''): array
    {
        $sql    = 'SELECT * FROM users WHERE 1 = 1';
        $params = [];

        if ($keyword !== '') {
            $sql .= ' AND (username LIKE :kw1 OR email LIKE :kw2 OR first_name LIKE :kw3 OR last_name LIKE :kw4)';
            $like = '%' . $keyword . '%';
            $params['kw1'] = $like;
            $params['kw2'] = $like;
            $params['kw3'] = $like;
            $params['kw4'] = $like;
        }
        if (in_array($role, AuthController::ROLES, true)) {
            $sql .= ' AND role = :role';
            $params['role'] = $role;
        }
        if (in_array($status, AuthController::STATUSES, true)) {
            $sql .= ' AND status = :status';
            $params['status'] = $status;
        }
        $sql .= ' ORDER BY id DESC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return array_map(fn(array $row) => UserModel::fromArray($row), $stmt->fetchAll());
    }

    /**
     * Latest registered users (admin dashboard)
     * @return UserModel[]
     */
    public function latest(int $limit = 5): array
    {
        $stmt = $this->db->prepare('SELECT * FROM users ORDER BY id DESC LIMIT :lim');
        $stmt->bindValue('lim', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return array_map(fn(array $row) => UserModel::fromArray($row), $stmt->fetchAll());
    }

    private function isTaken(string $field, string $value, int $excludeId = 0): bool
    {
        // $field comes from code only (never from the user)
        $column = $field === 'email' ? 'email' : 'username';
        $stmt   = $this->db->prepare("SELECT id FROM users WHERE $column = :value AND id <> :id LIMIT 1");
        $stmt->execute(['value' => $value, 'id' => $excludeId]);
        return (bool) $stmt->fetch();
    }

    // ==============================================================
    // CREATE (admin)
    // ==============================================================

    public function create(array $post): array
    {
        if (!$this->auth->verifyCsrfToken($post['csrf_token'] ?? null)) {
            return ["Invalid security token. Please try again."];
        }

        $role   = (string) ($post['role'] ?? 'user');
        $status = (string) ($post['status'] ?? 'active');

        $errors = array_merge(
            AuthController::validateIdentity($post),
            AuthController::validatePassword((string) ($post['password'] ?? ''), (string) ($post['password_confirm'] ?? ''))
        );
        if (!in_array($role, AuthController::ROLES, true)) {
            $errors[] = "Invalid role.";
        }
        if (!in_array($status, AuthController::STATUSES, true)) {
            $errors[] = "Invalid status.";
        }
        if (!empty($errors)) {
            return $errors;
        }

        $email    = trim($post['email']);
        $username = trim($post['username']);
        if ($this->isTaken('email', $email) || $this->isTaken('username', $username)) {
            return ["This email or username is already taken."];
        }

        $stmt = $this->db->prepare(
            'INSERT INTO users (username, first_name, last_name, email, password, role, status)
             VALUES (:username, :first_name, :last_name, :email, :password, :role, :status)'
        );
        $ok = $stmt->execute([
            'username'   => $username,
            'first_name' => trim($post['first_name']),
            'last_name'  => trim($post['last_name']),
            'email'      => $email,
            'password'   => password_hash((string) $post['password'], PASSWORD_DEFAULT),
            'role'       => $role,
            'status'     => $status,
        ]);

        return $ok ? [] : ["An error occurred while creating the account."];
    }

    // ==============================================================
    // UPDATE
    // ==============================================================

    /**
     * Update a user.
     * $byAdmin = true  : admin edits any account (role, status, points, optional new password)
     * $byAdmin = false : the user edits his own profile (identity fields only)
     */
    public function update(int $id, array $post, bool $byAdmin): array
    {
        if (!$this->auth->verifyCsrfToken($post['csrf_token'] ?? null)) {
            return ["Invalid security token. Please try again."];
        }

        $user = $this->findById($id);
        if (!$user) {
            return ["User not found."];
        }

        $errors   = AuthController::validateIdentity($post);
        $email    = trim((string) ($post['email'] ?? ''));
        $username = trim((string) ($post['username'] ?? ''));

        $role     = $user->getRole();
        $status   = $user->getStatus();
        $points   = $user->getPoints();
        $password = (string) ($post['password'] ?? '');

        if ($byAdmin) {
            $role   = (string) ($post['role'] ?? $role);
            $status = (string) ($post['status'] ?? $status);
            $pointsRaw = trim((string) ($post['points'] ?? (string) $points));

            if (!in_array($role, AuthController::ROLES, true)) {
                $errors[] = "Invalid role.";
            }
            if (!in_array($status, AuthController::STATUSES, true)) {
                $errors[] = "Invalid status.";
            }
            if (!preg_match('/^\d{1,7}$/', $pointsRaw)) {
                $errors[] = "Points must be a positive whole number.";
            } else {
                $points = (int) $pointsRaw;
            }
            // An admin cannot remove his own admin role or block himself
            if ($id === (int) ($_SESSION['user_id'] ?? 0) && ($role !== 'admin' || $status !== 'active')) {
                $errors[] = "You cannot change your own role or block your own account.";
            }
            if ($password !== '') {
                $errors = array_merge($errors, AuthController::validatePassword($password, (string) ($post['password_confirm'] ?? '')));
            }
        }

        if (!empty($errors)) {
            return $errors;
        }

        if ($this->isTaken('email', $email, $id) || $this->isTaken('username', $username, $id)) {
            return ["This email or username is already taken."];
        }

        $stmt = $this->db->prepare(
            'UPDATE users SET username = :username, first_name = :first_name, last_name = :last_name,
                    email = :email, role = :role, status = :status, points = :points
             WHERE id = :id'
        );
        $stmt->execute([
            'username'   => $username,
            'first_name' => trim((string) $post['first_name']),
            'last_name'  => trim((string) $post['last_name']),
            'email'      => $email,
            'role'       => $role,
            'status'     => $status,
            'points'     => $points,
            'id'         => $id,
        ]);

        if ($byAdmin && $password !== '') {
            $this->savePassword($id, $password);
        }

        // Keep the session in sync when the user edits his own account
        if ($id === (int) ($_SESSION['user_id'] ?? 0)) {
            $_SESSION['username'] = $username;
            $_SESSION['role']     = $role;
        }

        return [];
    }

    /**
     * The user changes his own password (current password required)
     */
    public function changePassword(int $id, array $post): array
    {
        if (!$this->auth->verifyCsrfToken($post['csrf_token'] ?? null)) {
            return ["Invalid security token. Please try again."];
        }
        $user = $this->findById($id);
        if (!$user) {
            return ["User not found."];
        }
        if (!password_verify((string) ($post['current_password'] ?? ''), $user->getPassword())) {
            return ["Your current password is incorrect."];
        }
        $new    = (string) ($post['new_password'] ?? '');
        $errors = AuthController::validatePassword($new, (string) ($post['new_password_confirm'] ?? ''));
        if (!empty($errors)) {
            return $errors;
        }
        $this->savePassword($id, $new);
        session_regenerate_id(true);
        return [];
    }

    private function savePassword(int $id, string $plain): void
    {
        $stmt = $this->db->prepare('UPDATE users SET password = :password WHERE id = :id');
        $stmt->execute(['password' => password_hash($plain, PASSWORD_DEFAULT), 'id' => $id]);
    }

    /**
     * Admin: block / unblock an account
     */
    public function toggleStatus(int $id, array $post): array
    {
        if (!$this->auth->verifyCsrfToken($post['csrf_token'] ?? null)) {
            return ["Invalid security token. Please try again."];
        }
        if ($id === (int) ($_SESSION['user_id'] ?? 0)) {
            return ["You cannot block your own account."];
        }
        $user = $this->findById($id);
        if (!$user) {
            return ["User not found."];
        }
        $new  = $user->getStatus() === 'active' ? 'blocked' : 'active';
        $stmt = $this->db->prepare('UPDATE users SET status = :status WHERE id = :id');
        $stmt->execute(['status' => $new, 'id' => $id]);
        return [];
    }

    // ==============================================================
    // DELETE
    // ==============================================================

    /**
     * Admin deletes an account (never his own)
     */
    public function delete(int $id, array $post): array
    {
        if (!$this->auth->verifyCsrfToken($post['csrf_token'] ?? null)) {
            return ["Invalid security token. Please try again."];
        }
        if ($id === (int) ($_SESSION['user_id'] ?? 0)) {
            return ["You cannot delete your own account from the admin panel."];
        }
        $stmt = $this->db->prepare('DELETE FROM users WHERE id = :id');
        $stmt->execute(['id' => $id]);
        return $stmt->rowCount() ? [] : ["User not found."];
    }

    /**
     * The user deletes his own account (password confirmation required)
     */
    public function deleteOwnAccount(int $id, array $post): array
    {
        if (!$this->auth->verifyCsrfToken($post['csrf_token'] ?? null)) {
            return ["Invalid security token. Please try again."];
        }
        $user = $this->findById($id);
        if (!$user || !password_verify((string) ($post['password'] ?? ''), $user->getPassword())) {
            return ["Incorrect password. Your account was not deleted."];
        }
        if ($user->getRole() === 'admin' && $this->countAdmins() <= 1) {
            return ["You are the last administrator: you cannot delete your account."];
        }
        $stmt = $this->db->prepare('DELETE FROM users WHERE id = :id');
        $stmt->execute(['id' => $id]);
        return [];
    }

    private function countAdmins(): int
    {
        return (int) $this->db->query("SELECT COUNT(*) FROM users WHERE role = 'admin'")->fetchColumn();
    }
}
