<?php
namespace App\Services;

use App\Repositories\BudgetRepository;

class BudgetService {
    private BudgetRepository $budgetRepo;
    
    public function __construct() {
        $this->budgetRepo = new BudgetRepository();
    }
    
    // Izračunaj procenat iskorišćenosti budžeta
    public function calculateUsagePercentage(float $spent, float $limit): float {
        if ($limit <= 0) return 0;
        return min(($spent / $limit) * 100, 100);
    }
    
    // Dobij status budžeta
    public function getBudgetStatus(float $percentage): array {
        if ($percentage >= 100) {
            return ['class' => 'danger', 'text' => 'Premašen'];
        } elseif ($percentage >= 80) {
            return ['class' => 'warning', 'text' => 'Upozorenje'];
        } else {
            return ['class' => 'success', 'text' => 'OK'];
        }
    }
}