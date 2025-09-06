<?php
use App\Core\Session;
?>
<!DOCTYPE html>
<html lang="sr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'Lične Finansije') ?> - <?= APP_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="/assets/css/custom.css">
</head>
<body>
    <?php if (Session::has('user_id')): ?>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="/"><i class="bi bi-wallet2"></i> <?= APP_NAME ?></a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link" href="/"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="/transactions/"><i class="bi bi-arrow-left-right"></i> Transakcije</a></li>
                    <li class="nav-item"><a class="nav-link" href="/categories/"><i class="bi bi-tags"></i> Kategorije</a></li>
                    <li class="nav-item"><a class="nav-link" href="/budgets/"><i class="bi bi-piggy-bank"></i> Budžeti</a></li>
                    <li class="nav-item"><a class="nav-link" href="/goals/"><i class="bi bi-trophy"></i> Ciljevi</a></li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle"></i> <?= e(Session::get('user_name')) ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="/profile/"><i class="bi bi-person"></i> Profil</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="/auth/logout.php"><i class="bi bi-box-arrow-right"></i> Odjava</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <?php endif; ?>

    <main class="<?= Session::has('user_id') ? 'container my-4' : '' ?>">
        <?php include __DIR__ . '/flash.php'; ?>
