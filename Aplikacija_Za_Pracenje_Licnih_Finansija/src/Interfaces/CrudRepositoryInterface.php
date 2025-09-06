<?php
namespace App\Interfaces;

interface CrudRepositoryInterface {
    // Kreiranje novog zapisa
    public function create(array $data): int;
    
    // Pronađi po ID-ju
    public function findById(int $id): ?array;
    
    // Ažuriranje zapisa
    public function update(int $id, array $data): bool;
    
    // Brisanje zapisa
    public function delete(int $id): bool;
    
    // Paginacija sa filterima
    public function paginate(int $page = 1, int $perPage = 10, array $filters = []): array;
}