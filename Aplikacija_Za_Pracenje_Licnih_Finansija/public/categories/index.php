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
$categories = $categoryRepo->getUserCategories($user['id']);

// Grupiši po tipu
$income_categories = array_filter($categories, fn($c) => $c['type'] === 'prihod');
$expense_categories = array_filter($categories, fn($c) => $c['type'] === 'rashod');

// Brisanje
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {
    if (Session::verifyCsrf($_POST['csrf_token'] ?? '')) {
        $id = (int) $_POST['delete'];
        
        if ($categoryRepo->belongsToUser($id, $user['id'])) {
            $categoryRepo->delete($id);
            Session::flash('success', 'Kategorija je obrisana.');
            redirect('/categories/');
        }
    }
}

$title = 'Kategorije';
include '../../views/partials/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Kategorije</h1>
    <a href="/categories/create.php" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> Nova kategorija
    </a>
</div>

<div class="row">
    <!-- Kategorije prihoda -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="bi bi-arrow-up-circle"></i> Prihodi</h5>
            </div>
            <div class="card-body">
                <?php if (empty($income_categories)): ?>
                    <p class="text-muted">Nema kategorija prihoda.</p>
                <?php else: ?>
                    <div class="list-group">
                        <?php foreach ($income_categories as $cat): ?>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <?php if ($cat['color']): ?>
                                    <span class="badge" style="background-color: <?= e($cat['color']) ?>">
                                        &nbsp;&nbsp;&nbsp;
                                    </span>
                                <?php endif; ?>
                                <?= e($cat['name']) ?>
                            </div>
                            <div>
                                <a href="/categories/edit.php?id=<?= $cat['id'] ?>" 
                                   class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form method="POST" class="d-inline" 
                                      onsubmit="return confirm('Da li ste sigurni?')">
                                    <input type="hidden" name="csrf_token" value="<?= Session::generateCsrf() ?>">
                                    <button type="submit" name="delete" value="<?= $cat['id'] ?>" 
                                            class="btn btn-sm btn-outline-danger">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Kategorije rashoda -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header bg-danger text-white">
                <h5 class="mb-0"><i class="bi bi-arrow-down-circle"></i> Rashodi</h5>
            </div>
            <div class="card-body">
                <?php if (empty($expense_categories)): ?>
                    <p class="text-muted">Nema kategorija rashoda.</p>
                <?php else: ?>
                    <div class="list-group">
                        <?php foreach ($expense_categories as $cat): ?>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <?php if ($cat['color']): ?>
                                    <span class="badge" style="background-color: <?= e($cat['color']) ?>">
                                        &nbsp;&nbsp;&nbsp;
                                    </span>
                                <?php endif; ?>
                                <?= e($cat['name']) ?>
                            </div>
                            <div>
                                <a href="/categories/edit.php?id=<?= $cat['id'] ?>" 
                                   class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form method="POST" class="d-inline" 
                                      onsubmit="return confirm('Da li ste sigurni?')">
                                    <input type="hidden" name="csrf_token" value="<?= Session::generateCsrf() ?>">
                                    <button type="submit" name="delete" value="<?= $cat['id'] ?>" 
                                            class="btn btn-sm btn-outline-danger">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include '../../views/partials/footer.php'; ?>