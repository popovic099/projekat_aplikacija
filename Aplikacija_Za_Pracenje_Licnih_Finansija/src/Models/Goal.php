<?php
namespace App\Models;

class Goal {
    public int $id;
    public int $user_id;
    public string $title;
    public float $target_amount;
    public float $current_amount;
    public ?string $deadline;
    public string $created_at;
    
    public function __construct(array $data) {
        $this->id = $data['id'] ?? 0;
        $this->user_id = $data['user_id'] ?? 0;
        $this->title = $data['title'] ?? '';
        $this->target_amount = (float) ($data['target_amount'] ?? 0);
        $this->current_amount = (float) ($data['current_amount'] ?? 0);
        $this->deadline = $data['deadline'] ?? null;
        $this->created_at = $data['created_at'] ?? date('Y-m-d H:i:s');
    }
}