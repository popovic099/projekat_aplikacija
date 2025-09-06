<?php
namespace App\Repositories;

class AccountRepository extends BaseRepository {
    protected string $table = 'accounts';
    
    // Dobij račune korisnika
    public function getUserAccounts(int $userId): array {
        $sql = "SELECT * FROM {$this->table} 
                WHERE user_id = :user_id
                ORDER BY name ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['user_id' => $userId]);
        
        return $stmt->fetchAll();
    }
    
    // Kreiraj osnovni račun za novog korisnika
    public function createDefaultAccount(int $userId): int {
        return $this->create([
            'user_id' => $userId,
            'name' => 'Glavni račun',
            'starting_balance' => 0
        ]);
    }
}