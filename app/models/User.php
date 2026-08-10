<?php
/**
 * Kravyo - User Model
 */

class User extends Model {
    protected string $table = 'users';

    /**
     * Find a user by email address
     */
    public function findByEmail(string $email): ?array {
        $sql = "SELECT * FROM {$this->table} WHERE email = :email LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['email' => $email]);
        $record = $stmt->fetch();
        return $record ?: null;
    }

    /**
     * Find a user by phone number
     */
    public function findByPhone(string $phone): ?array {
        $sql = "SELECT * FROM {$this->table} WHERE phone = :phone LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['phone' => $phone]);
        $record = $stmt->fetch();
        return $record ?: null;
    }

    /**
     * Register a new user with hashed password
     */
    public function register(array $data): int|string {
        // Hash the password securely before saving
        $data['password_hash'] = password_hash($data['password'], PASSWORD_DEFAULT);
        unset($data['password']); // Remove plain text password

        return $this->create($data);
    }

    /**
     * Verify user password
     */
    public function verifyPassword(string $plainPassword, string $hashedPassword): bool {
        return password_verify($plainPassword, $hashedPassword);
    }
}
