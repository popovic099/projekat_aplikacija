<?php
namespace App\Services;

use App\Repositories\TransactionRepository;
use App\Repositories\GoalRepository;

class ReportService {
    private TransactionRepository $transactionRepo;
    private GoalRepository $goalRepo;
    
    public function __construct() {
        $this->transactionRepo = new TransactionRepository();
        $this->goalRepo = new GoalRepository();
    }
    
    // Dashboard statistike
    public function getDashboardStats(int $userId): array {
        $currentMonth = date('Y-m-01');
        $endMonth = date('Y-m-t');
        
        // Mesečni prihodi i rashodi
        $income = $this->transactionRepo->getSumByType($userId, 'prihod', $currentMonth, $endMonth);
        $expense = $this->transactionRepo->getSumByType($userId, 'rashod', $currentMonth, $endMonth);
        $balance = $income - $expense;
        
        // Ciljevi - progress
        $goals = $this->goalRepo->getUserGoals($userId);
        $goalsProgress = 0;
        if (count($goals) > 0) {
            $totalProgress = 0;
            foreach ($goals as $goal) {
                $progress = $goal['target_amount'] > 0 
                    ? ($goal['current_amount'] / $goal['target_amount']) * 100 
                    : 0;
                $totalProgress += min($progress, 100);
            }
            $goalsProgress = $totalProgress / count($goals);
        }
        
        return [
            'income' => $income,
            'expense' => $expense,
            'balance' => $balance,
            'goalsProgress' => round($goalsProgress, 1)
        ];
    }
}