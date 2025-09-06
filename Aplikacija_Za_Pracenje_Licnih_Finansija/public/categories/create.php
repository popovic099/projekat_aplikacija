<?php
require_once '../../config/config.php';
require_once '../../src/Helpers/functions.php';

use App\Core\Auth;
use App\Core\Session;
use App\Repositories\CategoryRepository;

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

$categoryRepo = new CategoryRepository();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF verifikacija
    if (!Session::verifyCsrf($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Nevaljan zahtev.';
    }
    
    $name = trim($_POST['name'] ?? '');
    $type = $_POST['type'] ?? '';
    $color = $_POST['color'] ?? null;
    
    // Validacija
    if (empty($name)) {
        $errors[] = 'Naziv je obavezan.';
    }
    
    if (!in_array($type, ['prihod', 'rashod'])) {
        $errors[] = 'Tip mora biti prihod ili rashod.';
    }
    
    // Ako nema grešaka, kreiraj
    if (empty($errors)) {
        try {
            $categoryRepo->create([
                'user_id' => $user['id'],
                'name' => $name,
                'type' => $type,
                'color' => $color
            ]);
            
            Session::flash('success', 'Kategorija je kreirana.');
            redirect('/categories/');
        } catch (Exception $e) {
            $errors[] = 'Kategorija sa tim nazivom već postoji.';
        }
    }
}

$title = 'Nova kategorija';
include '../../views/partials/header.php';
?>

<h1 class="mb-4">Nova kategorija</h1>

<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        <?php foreach ($errors as $error): ?>
                        <li><?= e($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>
                
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?= Session::generateCsrf() ?>">
                    
                    <div class="mb-3">
                        <label for="name" class="form-label">Naziv</label>
                        <input type="text" class="form-control" id="name" name="name" 
                               value="<?= e($_POST['name'] ?? '') ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="type" class="form-label">Tip</label>
                        <select class="form-select" id="type" name="type" required>
                            <option value="">Izaberite tip</option>
                            <option value="prihod" <?= ($_POST['type'] ?? '') === 'prihod' ? 'selected' : '' ?>>
                                Prihod
                            </option>
                            <option value="rashod" <?= ($_POST['type'] ?? '') === 'rashod' ? 'selected' : '' ?>>
                                Rashod
                            </option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="color" class="form-label">Boja (opciono)</label>
                        <input type="color" class="form-control form-control-color" id="color" name="color" 
                               value="<?= e($_POST['color'] ?? '#0d6efd') ?>">
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i> Sačuvaj
                        </button>
                        <a href="/categories/" class="btn btn-secondary">Otkaži</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include '../../views/partials/footer.php'; ?>