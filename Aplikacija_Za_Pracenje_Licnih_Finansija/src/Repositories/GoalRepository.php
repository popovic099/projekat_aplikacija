<?php
namespace App\Repositories;

class GoalRepository extends BaseRepository {
    protected string $table = 'goals';

    // Dobij ciljeve korisnika (bez MySQL "NULLS LAST" — koristi trik sa (deadline IS NULL))
    public function getUserGoals(int $userId): array {
        $sql = "SELECT * FROM {$this->table}
                WHERE user_id = :user_id
                ORDER BY (deadline IS NULL) ASC, deadline ASC, id DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }

    // Ažuriraj trenutni iznos cilja
    public function updateCurrentAmount(int $goalId, float $amount): bool {
        $sql = "UPDATE {$this->table}
                SET current_amount = current_amount + :amount
                WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'id' => $goalId,
            'amount' => $amount
        ]);
    }

    // Proveri da li cilj pripada korisniku
    public function belongsToUser(int $goalId, int $userId): bool {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE id = :id AND user_id = :user_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $goalId, 'user_id' => $userId]);
        return $stmt->fetchColumn() > 0;
    }
}
