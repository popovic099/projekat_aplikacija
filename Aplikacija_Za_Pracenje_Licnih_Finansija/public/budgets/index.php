<?php
require_once '../../config/config.php';
require_once '../../src/Helpers/functions.php';

use App\Core\Auth;
use App\Core\Session;
use App\Repositories\BudgetRepository;
use App\Services\BudgetService;

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
$budgetService = new BudgetService();

$budgets = $budgetRepo->getUserBudgetsWithSpent($user['id']);

// Brisanje
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {
    if (Session::verifyCsrf($_POST['csrf_token'] ?? '')) {
        $id = (int) $_POST['delete'];
        
        if ($budgetRepo->belongsToUser($id, $user['id'])) {
            $budgetRepo->delete($id);
            Session::flash('success', 'Budžet je obrisan.');
            redirect('/budgets/');
        }
    }
}

$title = 'Budžeti';
include '../../views/partials/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Budžeti</h1>
    <a href="/budgets/create.php" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> Novi budžet
    </a>
</div>

<div class="row">
    <?php if (empty($budgets)): ?>
        <div class="col-12">
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i> Nemate postavljene budžete za ovaj mesec.
            </div>
        </div>
    <?php else: ?>
        <?php foreach ($budgets as $budget): 
            $percentage = $budgetService->calculateUsagePercentage($budget['spent'], $budget['limit_amount']);
            $status = $budgetService->getBudgetStatus($percentage);
        ?>
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title"><?= e($budget['category_name']) ?></h5>
                    <p class="text-muted mb-2">
                        Potrošeno: <strong><?= formatRsd($budget['spent']) ?></strong> / 
                        Limit: <strong><?= formatRsd($budget['limit_amount']) ?></strong>
                    </p>
                    
                    <div class="progress mb-2" style="height: 25px;">
                        <div class="progress-bar bg-<?= $status['class'] ?>" 
                             style="width: <?= $percentage ?>%">
                            <?= round($percentage) ?>%
                        </div>
                    </div>
                    
                    <?php if ($percentage >= 80): ?>
                    <div class="alert alert-<?= $status['class'] ?> py-1 px-2 mb-2">
                        <small><i class="bi bi-exclamation-triangle"></i> <?= $status['text'] ?></small>
                    </div>
                    <?php endif; ?>
                    
                    <div class="d-flex gap-2">
                        <a href="/budgets/edit.php?id=<?= $budget['id'] ?>" 
                           class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-pencil"></i> Izmeni
                        </a>
                        <form method="POST" class="d-inline" 
                              onsubmit="return confirm('Da li ste sigurni?')">
                            <input type="hidden" name="csrf_token" value="<?= Session::generateCsrf() ?>">
                            <button type="submit" name="delete" value="<?= $budget['id'] ?>" 
                                    class="btn btn-sm btn-outline-danger">
                                <i class="bi bi-trash"></i> Obriši
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php include '../../views/partials/footer.php'; ?>