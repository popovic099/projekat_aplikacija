<?php
require_once '../../config/config.php';
require_once '../../src/Helpers/functions.php';

use App\Core\Auth;
use App\Core\Session;
use App\Repositories\AccountRepository;

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

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF verifikacija (DODATO)
    if (!Session::verifyCsrf($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Nevaljan zahtev.';
    }

    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';

    // Validacija
    if (empty($name)) $errors[] = 'Ime je obavezno.';
    if (empty($email)) $errors[] = 'Email je obavezan.';
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email nije valjan.';
    if (empty($password)) $errors[] = 'Lozinka je obavezna.';
    elseif (strlen($password) < 6) $errors[] = 'Lozinka mora imati minimum 6 karaktera.';
    if ($password !== $password_confirm) $errors[] = 'Lozinke se ne poklapaju.';

    // Ako nema grešaka, registruj
    if (empty($errors)) {
        $registered = $auth->register([
            'name' => $name,
            'email' => $email,
            'password' => $password
        ]);

        if ($registered) {
            // Auto login + kreiraj osnovni račun
            $auth->login($email, $password);
            $accountRepo = new AccountRepository();
            $accountRepo->createDefaultAccount(Session::get('user_id'));

            Session::flash('success', 'Uspešno ste se registrovali!');
            redirect('/');
        } else {
            $errors[] = 'Email adresa već postoji.';
        }
    }
}

$title = 'Registracija';
include '../../views/partials/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-6 col-lg-4">
        <div class="card shadow">
            <div class="card-body">
                <h3 class="card-title text-center mb-4">Registracija</h3>

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
                        <label for="name" class="form-label">Ime i prezime</label>
                        <input type="text" class="form-control" id="name" name="name"
                               value="<?= e($_POST['name'] ?? '') ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email adresa</label>
                        <input type="email" class="form-control" id="email" name="email"
                               value="<?= e($_POST['email'] ?? '') ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Lozinka</label>
                        <input type="password" class="form-control" id="password" name="password"
                               minlength="6" required>
                    </div>

                    <div class="mb-3">
                        <label for="password_confirm" class="form-label">Potvrdi lozinku</label>
                        <input type="password" class="form-control" id="password_confirm"
                               name="password_confirm" required>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Registruj se</button>
                </form>

                <hr>

                <p class="text-center mb-0">
                    Već imate nalog? <a href="/auth/login.php">Prijavite se</a>
                </p>
            </div>
        </div>
    </div>
</div>

<?php include '../../views/partials/footer.php'; ?>
