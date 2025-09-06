<?php
namespace App\Repositories;

use App\Core\Database;
use App\Interfaces\CrudRepositoryInterface;
use PDO;

abstract class BaseRepository implements CrudRepositoryInterface {
    protected PDO $db;
    protected string $table;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    // Osnovno kreiranje
    public function create(array $data): int {
        $fields = array_keys($data);
        $placeholders = array_map(fn($f) => ":$f", $fields);
        
        $sql = "INSERT INTO {$this->table} (" . implode(', ', $fields) . ") 
                VALUES (" . implode(', ', $placeholders) . ")";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($data);
        
        return (int) $this->db->lastInsertId();
    }
    
    // Pronađi po ID-ju
    public function findById(int $id): ?array {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        
        $result = $stmt->fetch();
        return $result ?: null;
    }
    
    // Ažuriranje
    public function update(int $id, array $data): bool {
        $fields = array_map(fn($f) => "$f = :$f", array_keys($data));
        
        $sql = "UPDATE {$this->table} SET " . implode(', ', $fields) . " WHERE id = :id";
        
        $data['id'] = $id;
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute($data);
    }
    
    // Brisanje
    public function delete(int $id): bool {
        $sql = "DELETE FROM {$this->table} WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute(['id' => $id]);
    }
    
    // Paginacija sa filterima
    public function paginate(int $page = 1, int $perPage = 10, array $filters = []): array {
        $offset = ($page - 1) * $perPage;
        
        // Osnovni SELECT
        $sql = "SELECT * FROM {$this->table}";
        $countSql = "SELECT COUNT(*) FROM {$this->table}";
        
        // Dodaj WHERE klauzule iz filtera
        $whereClauses = [];
        $params = [];
        
        foreach ($filters as $field => $value) {
            if ($value !== null && $value !== '') {
                $whereClauses[] = "$field = :$field";
                $params[$field] = $value;
            }
        }
        
        if (!empty($whereClauses)) {
            $whereString = " WHERE " . implode(' AND ', $whereClauses);
            $sql .= $whereString;
            $countSql .= $whereString;
        }
        
        // Ukupan broj zapisa
        $countStmt = $this->db->prepare($countSql);
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();
        
        // Dodaj LIMIT i OFFSET
        $sql .= " ORDER BY id DESC LIMIT :limit OFFSET :offset";
        $params['limit'] = $perPage;
        $params['offset'] = $offset;
        
        $stmt = $this->db->prepare($sql);
        
        // Binduj parametre eksplicitno za LIMIT i OFFSET
        foreach ($params as $key => $value) {
            if ($key === 'limit' || $key === 'offset') {
                $stmt->bindValue($key, $value, PDO::PARAM_INT);
            } else {
                $stmt->bindValue($key, $value);
            }
        }
        
        $stmt->execute();
        
        return [
            'data' => $stmt->fetchAll(),
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
            'totalPages' => ceil($total / $perPage)
        ];
    }
}