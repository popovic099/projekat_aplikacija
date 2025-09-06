<?php
require_once '../../config/config.php';
require_once '../../src/Helpers/functions.php';

use App\Core\Auth;
use App\Core\Session;
use App\Repositories\UserRepository;

// Autoload
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = __DIR__ . '/../../src/';
    
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    
    if (file_exists($file)) {
        require $file;
    }
});

requireAuth();

$auth = new Auth();
$user = $auth->currentUser();

$userRepo = new UserRepository();

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF verifikacija
    if (!Session::verifyCsrf($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Nevaljan zahtev.';
    }
    
    $action = $_POST['action'] ?? '';
    
    if ($action === 'update_profile') {
        $name = trim($_POST['name'] ?? '');
        
        if (empty($name)) {
            $errors[] = 'Ime je obavezno.';
        } else {
            $userRepo->update($user['id'], ['name' => $name]);
            Session::set('user_name', $name);
            $success = 'Profil je uspešno ažuriran.';
        }
    }
    
    if ($action === 'change_password') {
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        // Dobij korisnika iz baze
        $dbUser = $userRepo->findById($user['id']);
        
        if (!password_verify($current_password, $dbUser['password_hash'])) {
            $errors[] = 'Trenutna lozinka nije tačna.';
        } elseif (strlen($new_password) < 6) {
            $errors[] = 'Nova lozinka mora imati minimum 6 karaktera.';
        } elseif ($new_password !== $confirm_password) {
            $errors[] = 'Nove lozinke se ne poklapaju.';
        } else {
            $hash = password_hash($new_password, PASSWORD_DEFAULT);
            $userRepo->updatePassword($user['id'], $hash);
            $success = 'Lozinka je uspešno promenjena.';
        }
    }
}

$title = 'Profil';
include '../../views/partials/header.php';
?>

<h1 class="mb-4">Moj profil</h1>

<div class="row">
    <div class="col-md-6">
        <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php foreach ($errors as $error): ?>
                <li><?= e($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
        <div class="alert alert-success"><?= e($success) ?></div>
        <?php endif; ?>
        
        <!-- Osnovni podaci -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Osnovni podaci</h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?= Session::generateCsrf() ?>">
                    <input type="hidden" name="action" value="update_profile">
                    
                    <div class="mb-3">
                        <label for="name" class="form-label">Ime i prezime</label>
                        <input type="text" class="form-control" id="name" name="name" 
                               value="<?= e($user['name']) ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Email adresa</label>
                        <input type="email" class="form-control" value="<?= e($user['email']) ?>" disabled>
                        <small class="text-muted">Email adresa se ne može menjati.</small>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle"></i> Sačuvaj izmene
                    </button>
                </form>
            </div>
        </div>
        
        <!-- Promena lozinke -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Promena lozinke</h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?= Session::generateCsrf() ?>">
                    <input type="hidden" name="action" value="change_password">
                    
                    <div class="mb-3">
                        <label for="current_password" class="form-label">Trenutna lozinka</label>
                        <input type="password" class="form-control" id="current_password" 
                               name="current_password" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="new_password" class="form-label">Nova lozinka</label>
                        <input type="password" class="form-control" id="new_password" 
                               name="new_password" minlength="6" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Potvrdi novu lozinku</label>
                        <input type="password" class="form-control" id="confirm_password" 
                               name="confirm_password" minlength="6" required>
                    </div>
                    
                    <button type="submit" class="btn btn-warning">
                        <i class="bi bi-key"></i> Promeni lozinku
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include '../../views/partials/footer.php'; ?>