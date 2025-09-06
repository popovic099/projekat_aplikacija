<?php
namespace App\Models;

class Category {
    public int $id;
    public int $user_id;
    public string $name;
    public string $type;
    public ?string $color;
    public string $created_at;
    
    public function __construct(array $data) {
        $this->id = $data['id'] ?? 0;
        $this->user_id = $data['user_id'] ?? 0;
        $this->name = $data['name'] ?? '';
        $this->type = $data['type'] ?? 'rashod';
        $this->color = $data['color'] ?? null;
        $this->created_at = $data['created_at'] ?? date('Y-m-d H:i:s');
    }
}