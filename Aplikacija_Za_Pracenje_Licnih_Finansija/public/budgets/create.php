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

// Samo kategorije rashoda
$categories = $categoryRepo->getUserCategories($user['id'], 'rashod');

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF verifikacija
    if (!Session::verifyCsrf($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Nevaljan zahtev.';
    }
    
    $category_id = (int) ($_POST['category_id'] ?? 0);
    $period = $_POST['period'] ?? '';
    $period_start = $_POST['period_start'] ?? '';
    $limit_amount = (float) ($_POST['limit_amount'] ?? 0);
    
    // Validacija
    if ($category_id <= 0) {
        $errors[] = 'Kategorija je obavezna.';
    }
    
    if (!in_array($period, ['mesec', 'kvartal', 'godina'])) {
        $errors[] = 'Period mora biti mesec, kvartal ili godina.';
    }
    
    if (empty($period_start)) {
        $errors[] = 'Početak perioda je obavezan.';
    }
    
    if ($limit_amount <= 0) {
        $errors[] = 'Limit mora biti veći od 0.';
    }
    
    // Ako nema grešaka, kreiraj
    if (empty($errors)) {
        try {
            $budgetRepo->create([
                'user_id' => $user['id'],
                'category_id' => $category_id,
                'period' => $period,
                'period_start' => $period_start,
                'limit_amount' => $limit_amount
            ]);
            
            Session::flash('success', 'Budžet je kreiran.');
            redirect('/budgets/');
        } catch (Exception $e) {
            $errors[] = 'Budžet za ovu kategoriju i period već postoji.';
        }
    }
}

$title = 'Novi budžet';
include '../../views/partials/header.php';
?>

<h1 class="mb-4">Novi budžet</h1>

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
                        <label for="category_id" class="form-label">Kategorija rashoda</label>
                        <select class="form-select" id="category_id" name="category_id" required>
                            <option value="">Izaberite kategoriju</option>
                            <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>"><?= e($cat['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="period" class="form-label">Period</label>
                        <select class="form-select" id="period" name="period" required>
                            <option value="">Izaberite period</option>
                            <option value="mesec">Mesec</option>
                            <option value="kvartal">Kvartal</option>
                            <option value="godina">Godina</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="period_start" class="form-label">Početak perioda</label>
                        <input type="date" class="form-control" id="period_start" name="period_start" 
                               value="<?= date('Y-m-01') ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="limit_amount" class="form-label">Limit (RSD)</label>
                        <input type="number" class="form-control" id="limit_amount" name="limit_amount" 
                               step="0.01" min="0.01" required>
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i> Sačuvaj
                        </button>
                        <a href="/budgets/" class="btn btn-secondary">Otkaži</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include '../../views/partials/footer.php'; ?>