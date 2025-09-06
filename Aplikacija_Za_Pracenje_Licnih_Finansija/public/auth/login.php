<?php
require_once '../../config/config.php';
require_once '../../src/Helpers/functions.php';

use App\Core\Auth;
use App\Core\Session;

// Autoload
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = __DIR__ . '/../../src/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) return;
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    if (file_exists($file)) require $file;
});

$auth = new Auth();

// Ako je već ulogovan
if ($auth->isLoggedIn()) {
    redirect('/');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // CSRF verifikacija
    if (!Session::verifyCsrf($_POST['csrf_token'] ?? '')) {
        $error = 'Nevaljan zahtev. Pokušajte ponovo.';
    } elseif ($auth->login($email, $password)) {
        Session::flash('success', 'Uspešno ste se prijavili!');
        redirect('/');
    } else {
        $error = 'Pogrešan email ili lozinka.';
    }
}

$title = 'Prijava';
include '../../views/partials/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-6 col-lg-4">
        <div class="card shadow">
            <div class="card-body">
                <h3 class="card-title text-center mb-4">Prijava</h3>

                <?php if ($error): ?>
                <div class="alert alert-danger"><?= e($error) ?></div>
                <?php endif; ?>

                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?= App\Core\Session::generateCsrf() ?>">

                    <div class="mb-3">
                        <label for="email" class="form-label">Email adresa</label>
                        <input type="email" class="form-control" id="email" name="email"
                               value="<?= e($_POST['email'] ?? '') ?>" required autofocus>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Lozinka</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Prijavi se</button>
                </form>

                <hr>

                <p class="text-center mb-0">
                    Nemate nalog? <a href="/auth/register.php">Registrujte se</a>
                </p>

                <div class="alert alert-info mt-3">
                    <small>
                        <strong>Demo nalog:</strong><br>
                        marko@example.com / <code>test123</code>
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../../views/partials/footer.php'; ?>
