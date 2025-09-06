<?php
namespace App\Repositories;

use PDO;

class BudgetRepository extends BaseRepository {
    protected string $table = 'budgets';

    // Budžeti sa potrošnjom: sabira rashode od period_start do kraja perioda (mesec/kvartal/godina)
    public function getUserBudgetsWithSpent(int $userId): array {
        $sql = "SELECT b.*,
                       c.name AS category_name,
                       COALESCE(SUM(CASE
                            WHEN t.type = 'rashod'
                              AND t.occurred_on >= b.period_start
                              AND t.occurred_on < 
                                  CASE b.period
                                    WHEN 'mesec'   THEN DATE_ADD(b.period_start, INTERVAL 1 MONTH)
                                    WHEN 'kvartal' THEN DATE_ADD(b.period_start, INTERVAL 3 MONTH)
                                    WHEN 'godina'  THEN DATE_ADD(b.period_start, INTERVAL 1 YEAR)
                                  END
                            THEN t.amount ELSE 0 END), 0) AS spent
                FROM {$this->table} b
                JOIN categories c ON b.category_id = c.id
                LEFT JOIN transactions t 
                       ON t.user_id = b.user_id 
                      AND t.category_id = b.category_id
                WHERE b.user_id = :user_id
                GROUP BY b.id
                ORDER BY c.name";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }

    // Proveri da li budžet pripada korisniku
    public function belongsToUser(int $budgetId, int $userId): bool {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE id = :id AND user_id = :user_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $budgetId, 'user_id' => $userId]);
        return $stmt->fetchColumn() > 0;
    }
}
