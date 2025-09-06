<?php
require_once '../../config/config.php';
require_once '../../src/Helpers/functions.php';

use App\Core\Auth;
use App\Core\Session;
use App\Repositories\GoalRepository;
use App\Services\GoalService;

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
$goalService = new GoalService();

$goals = $goalRepo->getUserGoals($user['id']);

// Brisanje
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {
    if (Session::verifyCsrf($_POST['csrf_token'] ?? '')) {
        $id = (int) $_POST['delete'];
        
        if ($goalRepo->belongsToUser($id, $user['id'])) {
            $goalRepo->delete($id);
            Session::flash('success', 'Cilj je obrisan.');
            redirect('/goals/');
        }
    }
}

$title = 'Ciljevi';
include '../../views/partials/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Ciljevi</h1>
    <a href="/goals/create.php" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> Novi cilj
    </a>
</div>

<div class="row">
    <?php if (empty($goals)): ?>
        <div class="col-12">
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i> Nemate postavljene ciljeve.
            </div>
        </div>
    <?php else: ?>
        <?php foreach ($goals as $goal): 
            $progress = $goalService->calculateProgress($goal['current_amount'], $goal['target_amount']);
            $isExpired = $goalService->isExpired($goal['deadline']);
        ?>
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">
                        <?= e($goal['title']) ?>
                        <?php if ($progress >= 100): ?>
                            <span class="badge bg-success">Ostvareno!</span>
                        <?php elseif ($isExpired): ?>
                            <span class="badge bg-danger">Isteklo</span>
                        <?php endif; ?>
                    </h5>
                    
                    <p class="text-muted mb-2">
                        Napredak: <strong><?= formatRsd($goal['current_amount']) ?></strong> / 
                        <strong><?= formatRsd($goal['target_amount']) ?></strong>
                    </p>
                    
                    <div class="progress mb-2" style="height: 25px;">
                        <div class="progress-bar bg-<?= $progress >= 100 ? 'success' : 'primary' ?>" 
                             style="width: <?= $progress ?>%">
                            <?= round($progress) ?>%
                        </div>
                    </div>
                    
                    <?php if ($goal['deadline']): ?>
                    <p class="text-muted small mb-2">
                        <i class="bi bi-calendar"></i> Rok: <?= formatDate($goal['deadline']) ?>
                    </p>
                    <?php endif; ?>
                    
                    <div class="d-flex gap-2">
                        <a href="/goals/edit.php?id=<?= $goal['id'] ?>" 
                           class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-pencil"></i> Izmeni
                        </a>
                        <form method="POST" class="d-inline" 
                              onsubmit="return confirm('Da li ste sigurni?')">
                            <input type="hidden" name="csrf_token" value="<?= Session::generateCsrf() ?>">
                            <button type="submit" name="delete" value="<?= $goal['id'] ?>" 
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