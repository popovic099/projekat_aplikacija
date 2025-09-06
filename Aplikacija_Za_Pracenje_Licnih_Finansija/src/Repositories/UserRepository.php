<?php
namespace App\Repositories;

class UserRepository extends BaseRepository {
    protected string $table = 'users';
    
    // Pronađi korisnika po email adresi
    public function findByEmail(string $email): ?array {
        $sql = "SELECT * FROM {$this->table} WHERE email = :email";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['email' => $email]);
        
        $result = $stmt->fetch();
        return $result ?: null;
    }
    
    // Ažuriraj lozinku
    public function updatePassword(int $userId, string $newPasswordHash): bool {
        return $this->update($userId, ['password_hash' => $newPasswordHash]);
    }
}