<?php
namespace App\Services;

use App\Repositories\GoalRepository;

class GoalService {
    private GoalRepository $goalRepo;
    
    public function __construct() {
        $this->goalRepo = new GoalRepository();
    }
    
    // Izračunaj procenat napretka cilja
    public function calculateProgress(float $current, float $target): float {
        if ($target <= 0) return 0;
        return min(($current / $target) * 100, 100);
    }
    
    // Proveri da li je cilj istekao
    public function isExpired(?string $deadline): bool {
        if (!$deadline) return false;
        return strtotime($deadline) < strtotime('today');
    }
}