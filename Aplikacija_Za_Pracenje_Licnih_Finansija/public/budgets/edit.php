<?php
require_once '../../config/config.php';
require_once '../../src/Helpers/functions.php';

use App\Core\Auth;
use App\Core\Session;
use App\Repositories\BudgetRepository;
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

$budgetRepo = new BudgetRepository();
$categoryRepo = new CategoryRepository();

$id = (int) ($_GET['id'] ?? 0);
$budget = $budgetRepo->findById($id);

// Proveri da li budžet postoji i pripada korisniku
if (!$budget || !$budgetRepo->belongsToUser($id, $user['id'])) {
    Session::flash('danger', 'Budžet nije pronađen.');
    redirect('/budgets/');
}

// Samo kategorije rashoda
$categories = $categoryRepo->getUserCategories($user['id'], 'rashod');

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF verifikacija
    if (!Session::verifyCsrf($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Nevaljan zahtev.';
    }
    
    $limit_amount = (float) ($_POST['limit_amount'] ?? 0);
    
    // Validacija
    if ($limit_amount <= 0) {
        $errors[] = 'Limit mora biti veći od 0.';
    }
    
    // Ako nema grešaka, ažuriraj
    if (empty($errors)) {
        $budgetRepo->update($id, [
            'limit_amount' => $limit_amount
        ]);
        
        Session::flash('success', 'Budžet je ažuriran.');
        redirect('/budgets/');
    }
}

$title = 'Izmena budžeta';
include '../../views/partials/header.php';
?>

<h1 class="mb-4">Izmena budžeta</h1>

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
                        <label class="form-label">Kategorija</label>
                        <input type="text" class="form-control" disabled 
                               value="<?php 
                                   foreach ($categories as $cat) {
                                       if ($cat['id'] == $budget['category_id']) {
                                           echo e($cat['name']);
                                           break;
                                       }
                                   }
                               ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Period</label>
                        <input type="text" class="form-control" disabled 
                               value="<?= ucfirst($budget['period']) ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Početak perioda</label>
                        <input type="date" class="form-control" disabled 
                               value="<?= $budget['period_start'] ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label for="limit_amount" class="form-label">Limit (RSD)</label>
                        <input type="number" class="form-control" id="limit_amount" name="limit_amount" 
                               step="0.01" min="0.01" value="<?= $budget['limit_amount'] ?>" required>
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i> Sačuvaj izmene
                        </button>
                        <a href="/budgets/" class="btn btn-secondary">Otkaži</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include '../../views/partials/footer.php'; ?>