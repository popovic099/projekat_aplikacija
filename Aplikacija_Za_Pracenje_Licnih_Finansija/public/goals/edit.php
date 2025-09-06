<?php
require_once '../../config/config.php';
require_once '../../src/Helpers/functions.php';

use App\Core\Auth;
use App\Core\Session;
use App\Repositories\GoalRepository;

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

$goalRepo = new GoalRepository();

$id = (int) ($_GET['id'] ?? 0);
$goal = $goalRepo->findById($id);

// Proveri da li cilj postoji i pripada korisniku
if (!$goal || !$goalRepo->belongsToUser($id, $user['id'])) {
    Session::flash('danger', 'Cilj nije pronađen.');
    redirect('/goals/');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF verifikacija
    if (!Session::verifyCsrf($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Nevaljan zahtev.';
    }
    
    $title = trim($_POST['title'] ?? '');
    $target_amount = (float) ($_POST['target_amount'] ?? 0);
    $current_amount = (float) ($_POST['current_amount'] ?? 0);
    $deadline = $_POST['deadline'] ?? null;
    
    // Validacija
    if (empty($title)) {
        $errors[] = 'Naziv cilja je obavezan.';
    }
    
    if ($target_amount <= 0) {
        $errors[] = 'Ciljani iznos mora biti veći od 0.';
    }
    
    if ($current_amount < 0) {
        $errors[] = 'Trenutni iznos ne može biti negativan.';
    }
    
    // Ako nema grešaka, ažuriraj
    if (empty($errors)) {
        $goalRepo->update($id, [
            'title' => $title,
            'target_amount' => $target_amount,
            'current_amount' => $current_amount,
            'deadline' => $deadline ?: null
        ]);
        
        Session::flash('success', 'Cilj je ažuriran.');
        redirect('/goals/');
    }
}

$title = 'Izmena cilja';
include '../../views/partials/header.php';
?>

<h1 class="mb-4">Izmena cilja</h1>

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
                        <label for="title" class="form-label">Naziv cilja</label>
                        <input type="text" class="form-control" id="title" name="title" 
                               value="<?= e($goal['title']) ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="target_amount" class="form-label">Ciljani iznos (RSD)</label>
                        <input type="number" class="form-control" id="target_amount" name="target_amount" 
                               step="0.01" min="0.01" value="<?= $goal['target_amount'] ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="current_amount" class="form-label">Trenutno uštećeno (RSD)</label>
                        <input type="number" class="form-control" id="current_amount" name="current_amount" 
                               step="0.01" min="0" value="<?= $goal['current_amount'] ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label for="deadline" class="form-label">Rok (opciono)</label>
                        <input type="date" class="form-control" id="deadline" name="deadline" 
                               value="<?= $goal['deadline'] ?>">
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i> Sačuvaj izmene
                        </button>
                        <a href="/goals/" class="btn btn-secondary">Otkaži</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include '../../views/partials/footer.php'; ?>