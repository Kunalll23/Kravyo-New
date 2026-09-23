<?php
/**
 * Kravyo - Admin Model
 * Handles CRUD operations for the `admins` table
 */

class Admin extends Model {
    protected string $table = 'admins';

    /**
     * Find an admin by their email address
     */
    public function findByEmail(string $email): ?array {
        $sql  = "SELECT * FROM {$this->table} WHERE email = :email LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['email' => $email]);
        $record = $stmt->fetch();
        return $record ?: null;
    }

    /**
     * Find an admin by their ID
     */
    public function findById(int $id): ?array {
        $sql  = "SELECT * FROM {$this->table} WHERE id = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        $record = $stmt->fetch();
        return $record ?: null;
    }

    /**
     * Verify a plain-text password against the stored hash
     */
    public function verifyPassword(string $plainPassword, string $hashedPassword): bool {
        return password_verify($plainPassword, $hashedPassword);
    }

    /**
     * Update the last_login_at timestamp for an admin
     */
    public function touchLastLogin(int $id): void {
        $sql  = "UPDATE {$this->table} SET last_login_at = NOW() WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
    }

    /**
     * Create a new admin account with a hashed password
     */
    public function createAdmin(array $data): int|string {
        $data['password_hash'] = password_hash($data['password'], PASSWORD_DEFAULT);
        unset($data['password']);
        return $this->create($data);
    }
}
