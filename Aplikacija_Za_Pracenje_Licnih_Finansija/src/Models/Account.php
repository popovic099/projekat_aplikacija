<?php
namespace App\Models;

class Account {
    public int $id;
    public int $user_id;
    public string $name;
    public float $starting_balance;
    public string $created_at;
    
    public function __construct(array $data) {
        $this->id = $data['id'] ?? 0;
        $this->user_id = $data['user_id'] ?? 0;
        $this->name = $data['name'] ?? '';
        $this->starting_balance = (float) ($data['starting_balance'] ?? 0);
        $this->created_at = $data['created_at'] ?? date('Y-m-d H:i:s');
    }
}