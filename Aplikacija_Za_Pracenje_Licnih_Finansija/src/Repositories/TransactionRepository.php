<?php
namespace App\Repositories;

use PDO;

class TransactionRepository extends BaseRepository {
    protected string $table = 'transactions';
    
    // Dobij transakcije sa vezanim podacima
    public function getUserTransactions(int $userId, array $filters = []): array {
        $sql = "SELECT t.*, c.name as category_name, c.color as category_color, a.name as account_name
                FROM {$this->table} t
                JOIN categories c ON t.category_id = c.id
                JOIN accounts a ON t.account_id = a.id
                WHERE t.user_id = :user_id";
        
        $params = ['user_id' => $userId];
        
        // Dodaj filtere
        if (!empty($filters['date_from'])) {
            $sql .= " AND t.occurred_on >= :date_from";
            $params['date_from'] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $sql .= " AND t.occurred_on <= :date_to";
            $params['date_to'] = $filters['date_to'];
        }
        
        if (!empty($filters['type'])) {
            $sql .= " AND t.type = :type";
            $params['type'] = $filters['type'];
        }
        
        if (!empty($filters['category_id'])) {
            $sql .= " AND t.category_id = :category_id";
            $params['category_id'] = $filters['category_id'];
        }
        
        $sql .= " ORDER BY t.occurred_on DESC, t.id DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll();
    }
    
    // Dobij sumu po tipu za period
    public function getSumByType(int $userId, string $type, string $startDate, string $endDate): float {
        $sql = "SELECT COALESCE(SUM(amount), 0) as total
                FROM {$this->table}
                WHERE user_id = :user_id 
                AND type = :type
                AND occurred_on BETWEEN :start_date AND :end_date";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'user_id' => $userId,
            'type' => $type,
            'start_date' => $startDate,
            'end_date' => $endDate
        ]);
        
        return (float) $stmt->fetchColumn();
    }
    
    // Poslednje transakcije
    public function getLatest(int $userId, int $limit = 10): array {
        $sql = "SELECT t.*, c.name as category_name, c.color as category_color, a.name as account_name
                FROM {$this->table} t
                JOIN categories c ON t.category_id = c.id
                JOIN accounts a ON t.account_id = a.id
                WHERE t.user_id = :user_id
                ORDER BY t.occurred_on DESC, t.id DESC
                LIMIT :limit";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue('user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
}