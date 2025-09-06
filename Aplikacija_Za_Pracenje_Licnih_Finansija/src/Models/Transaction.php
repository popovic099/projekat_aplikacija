<?php
namespace App\Models;

class Transaction {
    public int $id;
    public int $user_id;
    public int $account_id;
    public int $category_id;
    public float $amount;
    public string $type;
    public string $occurred_on;
    public ?string $note;
    public string $created_at;
    
    public function __construct(array $data) {
        $this->id = $data['id'] ?? 0;
        $this->user_id = $data['user_id'] ?? 0;
        $this->account_id = $data['account_id'] ?? 0;
        $this->category_id = $data['category_id'] ?? 0;
        $this->amount = (float) ($data['amount'] ?? 0);
        $this->type = $data['type'] ?? 'rashod';
        $this->occurred_on = $data['occurred_on'] ?? date('Y-m-d');
        $this->note = $data['note'] ?? null;
        $this->created_at = $data['created_at'] ?? date('Y-m-d H:i:s');
    }
}