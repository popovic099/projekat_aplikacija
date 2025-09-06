<?php
namespace App\Core;

use App\Repositories\UserRepository;

class Auth {
    private UserRepository $userRepo;
    
    public function __construct() {
        $this->userRepo = new UserRepository();
    }
    
    // Registracija korisnika
    public function register(array $data): bool {
        // Validacija
        if (empty($data['name']) || empty($data['email']) || empty($data['password'])) {
            return false;
        }
        
        // Proveri da li email već postoji
        if ($this->userRepo->findByEmail($data['email'])) {
            return false;
        }
        
        // Hash lozinke
        $data['password_hash'] = password_hash($data['password'], PASSWORD_DEFAULT);
        unset($data['password']);
        
        // Kreiraj korisnika
        $userId = $this->userRepo->create($data);
        return $userId > 0;
    }
    
    // Prijava korisnika
    public function login(string $email, string $password): bool {
        $user = $this->userRepo->findByEmail($email);
        
        if ($user && password_verify($password, $user['password_hash'])) {
            // Regeneriši sesiju
            session_regenerate_id(true);
            
            // Sačuvaj korisničke podatke u sesiju
            Session::set('user_id', $user['id']);
            Session::set('user_name', $user['name']);
            Session::set('user_email', $user['email']);
            Session::set('user_role', $user['role']);
            
            return true;
        }
        
        return false;
    }
    
    // Odjava korisnika
    public function logout(): void {
        Session::destroy();
        session_regenerate_id(true);
    }
    
    // Proveri da li je korisnik ulogovan
    public function isLoggedIn(): bool {
        return Session::has('user_id');
    }
    
    // Dobij trenutnog korisnika
    public function currentUser(): ?array {
        if (!$this->isLoggedIn()) {
            return null;
        }
        
        return [
            'id' => Session::get('user_id'),
            'name' => Session::get('user_name'),
            'email' => Session::get('user_email'),
            'role' => Session::get('user_role')
        ];
    }
    
    // Proveri da li je admin
    public function isAdmin(): bool {
        return Session::get('user_role') === 'admin';
    }
}