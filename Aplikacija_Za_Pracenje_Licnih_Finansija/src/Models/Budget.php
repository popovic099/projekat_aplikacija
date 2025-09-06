<?php
namespace App\Models;

class Budget {
    public int $id;
    public int $user_id;
    public int $category_id;
    public string $period;
    public string $period_start;
    public float $limit_amount;
    public string $created_at;
    
    public function __construct(array $data) {
        $this->id = $data['id'] ?? 0;
        $this->user_id = $data['user_id'] ?? 0;
        $this->category_id = $data['category_id'] ?? 0;
        $this->period = $data['period'] ?? 'mesec';
        $this->period_start = $data['period_start'] ?? date('Y-m-01');
        $this->limit_amount = (float) ($data['limit_amount'] ?? 0);
        $this->created_at = $data['created_at'] ?? date('Y-m-d H:i:s');
    }
}