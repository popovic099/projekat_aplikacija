<?php
require_once '../../config/config.php';
require_once '../../src/Helpers/functions.php';

use App\Core\Auth;
use App\Core\Session;
use App\Repositories\TransactionRepository;
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
$categoryRepo = new CategoryRepository();

// Filteri
$filters = [
    'date_from' => $_GET['date_from'] ?? '',
    'date_to' => $_GET['date_to'] ?? '',
    'type' => $_GET['type'] ?? '',
    'category_id' => $_GET['category_id'] ?? ''
];

// Dobij transakcije
$transactions = $transactionRepo->getUserTransactions($user['id'], $filters);
$categories = $categoryRepo->getUserCategories($user['id']);

// Brisanje
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {
    if (Session::verifyCsrf($_POST['csrf_token'] ?? '')) {
        $id = (int) $_POST['delete'];
        $transaction = $transactionRepo->findById($id);
        
        if ($transaction && $transaction['user_id'] == $user['id']) {
            $transactionRepo->delete($id);
            Session::flash('success', 'Transakcija je obrisana.');
            redirect('/transactions/');
        }
    }
}

$title = 'Transakcije';
include '../../views/partials/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Transakcije</h1>
    <a href="/transactions/create.php" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> Nova transakcija
    </a>
</div>

<!-- Filteri -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Datum od</label>
                <input type="date" class="form-control" name="date_from" 
                       value="<?= e($filters['date_from']) ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">Datum do</label>
                <input type="date" class="form-control" name="date_to" 
                       value="<?= e($filters['date_to']) ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label">Tip</label>
                <select class="form-select" name="type">
                    <option value="">Svi</option>
                    <option value="prihod" <?= $filters['type'] === 'prihod' ? 'selected' : '' ?>>Prihod</option>
                    <option value="rashod" <?= $filters['type'] === 'rashod' ? 'selected' : '' ?>>Rashod</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Kategorija</label>
                <select class="form-select" name="category_id">
                    <option value="">Sve</option>
                    <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>" <?= $filters['category_id'] == $cat['id'] ? 'selected' : '' ?>>
                        <?= e($cat['name']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">&nbsp;</label>
                <button type="submit" class="btn btn-secondary w-100">
                    <i class="bi bi-funnel"></i> Filtriraj
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Tabela transakcija -->
<div class="card">
    <div class="card-body">
        <?php if (empty($transactions)): ?>
            <p class="text-muted">Nema transakcija za prikaz.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Datum</th>
                            <th>Račun</th>
                            <th>Kategorija</th>
                            <th>Tip</th>
                            <th>Iznos</th>
                            <th>Napomena</th>
                            <th>Akcije</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($transactions as $t): ?>
                        <tr>
                            <td><?= formatDate($t['occurred_on']) ?></td>
                            <td><?= e($t['account_name']) ?></td>
                            <td>
                                <?php if ($t['category_color']): ?>
                                    <span class="badge" style="background-color: <?= e($t['category_color']) ?>">
                                        <?= e($t['category_name']) ?>
                                    </span>
                                <?php else: ?>
                                    <?= e($t['category_name']) ?>
                                <?php endif; ?>
                            </td>
                            <td><?= typeBadge($t['type']) ?></td>
                            <td class="fw-bold text-<?= $t['type'] === 'prihod' ? 'success' : 'danger' ?>">
                                <?= $t['type'] === 'prihod' ? '+' : '-' ?><?= formatRsd($t['amount']) ?>
                            </td>
                            <td><?= e($t['note']) ?></td>
                            <td>
                                <a href="/transactions/edit.php?id=<?= $t['id'] ?>" 
                                   class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form method="POST" class="d-inline" 
                                      onsubmit="return confirm('Da li ste sigurni?')">
                                    <input type="hidden" name="csrf_token" value="<?= Session::generateCsrf() ?>">
                                    <button type="submit" name="delete" value="<?= $t['id'] ?>" 
                                            class="btn btn-sm btn-outline-danger">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include '../../views/partials/footer.php'; ?>