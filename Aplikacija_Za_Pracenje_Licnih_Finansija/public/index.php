<?php
require_once '../config/config.php';
require_once '../src/Helpers/functions.php';

use App\Core\Auth;
use App\Services\ReportService;
use App\Repositories\TransactionRepository;

// Autoload
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = __DIR__ . '/../src/';
    
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

// Proveri autentifikaciju
requireAuth();

$auth = new Auth();
$user = $auth->currentUser();

$reportService = new ReportService();
$transactionRepo = new TransactionRepository();

// Dashboard statistike
$stats = $reportService->getDashboardStats($user['id']);
$latestTransactions = $transactionRepo->getLatest($user['id'], 10);

$title = 'Dashboard';
include '../views/partials/header.php';
?>

<h1 class="mb-4">Dashboard</h1>

<!-- Statistike kartice -->
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="card text-white bg-success">
            <div class="card-body">
                <h5 class="card-title"><i class="bi bi-arrow-up-circle"></i> Prihodi (mesec)</h5>
                <h3 class="card-text"><?= formatRsd($stats['income']) ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card text-white bg-danger">
            <div class="card-body">
                <h5 class="card-title"><i class="bi bi-arrow-down-circle"></i> Rashodi (mesec)</h5>
                <h3 class="card-text"><?= formatRsd($stats['expense']) ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card text-white bg-primary">
            <div class="card-body">
                <h5 class="card-title"><i class="bi bi-wallet"></i> Saldo</h5>
                <h3 class="card-text"><?= formatRsd($stats['balance']) ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card text-white bg-info">
            <div class="card-body">
                <h5 class="card-title"><i class="bi bi-trophy"></i> Napredak ciljeva</h5>
                <h3 class="card-text"><?= $stats['goalsProgress'] ?>%</h3>
            </div>
        </div>
    </div>
</div>

<!-- Poslednje transakcije -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-clock-history"></i> Poslednje transakcije</h5>
    </div>
    <div class="card-body">
        <?php if (empty($latestTransactions)): ?>
            <p class="text-muted">Nema transakcija.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Datum</th>
                            <th>Kategorija</th>
                            <th>Tip</th>
                            <th>Iznos</th>
                            <th>Napomena</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($latestTransactions as $t): ?>
                        <tr>
                            <td><?= formatDate($t['occurred_on']) ?></td>
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
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="text-center">
                <a href="/transactions/" class="btn btn-primary">Sve transakcije</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include '../views/partials/footer.php'; ?>