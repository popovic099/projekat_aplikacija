<?php
namespace App\Repositories;

class CategoryRepository extends BaseRepository {
    protected string $table = 'categories';
    
    // Dobij kategorije korisnika
    public function getUserCategories(int $userId, ?string $type = null): array {
        $sql = "SELECT * FROM {$this->table} WHERE user_id = :user_id";
        $params = ['user_id' => $userId];
        
        if ($type) {
            $sql .= " AND type = :type";
            $params['type'] = $type;
        }
        
        $sql .= " ORDER BY name ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll();
    }
    
    // Proveri da li kategorija pripada korisniku
    public function belongsToUser(int $categoryId, int $userId): bool {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE id = :id AND user_id = :user_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $categoryId, 'user_id' => $userId]);
        
        return $stmt->fetchColumn() > 0;
    }
}