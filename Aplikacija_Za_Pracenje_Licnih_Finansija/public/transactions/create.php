<?php
require_once '../../config/config.php';
require_once '../../src/Helpers/functions.php';

use App\Core\Auth;
use App\Core\Session;
use App\Repositories\TransactionRepository;
use App\Repositories\AccountRepository;
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

$transactionRepo = new TransactionRepository();
$accountRepo = new AccountRepository();
$categoryRepo = new CategoryRepository();

$accounts = $accountRepo->getUserAccounts($user['id']);
$categories = $categoryRepo->getUserCategories($user['id']);

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF verifikacija
    if (!Session::verifyCsrf($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Nevaljan zahtev.';
    }
    
    $account_id = (int) ($_POST['account_id'] ?? 0);
    $category_id = (int) ($_POST['category_id'] ?? 0);
    $amount = (float) ($_POST['amount'] ?? 0);
    $type = $_POST['type'] ?? '';
    $occurred_on = $_POST['occurred_on'] ?? '';
    $note = trim($_POST['note'] ?? '');
    
    // Validacija
    if ($amount <= 0) {
        $errors[] = 'Iznos mora biti veći od 0.';
    }
    
    if (!in_array($type, ['prihod', 'rashod'])) {
        $errors[] = 'Tip mora biti prihod ili rashod.';
    }
    
    if (empty($occurred_on)) {
        $errors[] = 'Datum je obavezan.';
    }
    
    // Ako nema grešaka, kreiraj
    if (empty($errors)) {
        $transactionRepo->create([
            'user_id' => $user['id'],
            'account_id' => $account_id,
            'category_id' => $category_id,
            'amount' => $amount,
            'type' => $type,
            'occurred_on' => $occurred_on,
            'note' => $note
        ]);
        
        Session::flash('success', 'Transakcija je kreirana.');
        redirect('/transactions/');
    }
}

$title = 'Nova transakcija';
include '../../views/partials/header.php';
?>

<h1 class="mb-4">Nova transakcija</h1>

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
                        <label for="account_id" class="form-label">Račun</label>
                        <select class="form-select" id="account_id" name="account_id" required>
                            <option value="">Izaberite račun</option>
                            <?php foreach ($accounts as $acc): ?>
                            <option value="<?= $acc['id'] ?>"><?= e($acc['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="type" class="form-label">Tip</label>
                        <select class="form-select" id="type" name="type" required>
                            <option value="">Izaberite tip</option>
                            <option value="prihod">Prihod</option>
                            <option value="rashod">Rashod</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="category_id" class="form-label">Kategorija</label>
                        <select class="form-select" id="category_id" name="category_id" required>
                            <option value="">Izaberite kategoriju</option>
                            <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>"><?= e($cat['name']) ?> (<?= $cat['type'] ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="amount" class="form-label">Iznos (RSD)</label>
                        <input type="number" class="form-control" id="amount" name="amount" 
                               step="0.01" min="0.01" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="occurred_on" class="form-label">Datum</label>
                        <input type="date" class="form-control" id="occurred_on" name="occurred_on" 
                               value="<?= date('Y-m-d') ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="note" class="form-label">Napomena (opciono)</label>
                        <textarea class="form-control" id="note" name="note" rows="2"></textarea>
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i> Sačuvaj
                        </button>
                        <a href="/transactions/" class="btn btn-secondary">Otkaži</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include '../../views/partials/footer.php'; ?>