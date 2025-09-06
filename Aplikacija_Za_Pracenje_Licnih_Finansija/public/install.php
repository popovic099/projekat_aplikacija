<?php
// public/install.php
require_once __DIR__ . '/../config/config.php';

// Malo helpera (bez zavisnosti)
function h($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

$log = [];
$error = null;

// Reset?
$doReset = isset($_GET['reset']) && $_GET['reset'] == '1';

try {
    // 1) Poveži se na MySQL bez baze
    $dsnServer = "mysql:host=" . DB_HOST . ";charset=" . DB_CHARSET;
    $pdoServer = new PDO($dsnServer, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);

    if ($doReset) {
        $pdoServer->exec("DROP DATABASE IF EXISTS `" . DB_NAME . "`");
        $log[] = "Baza '" . DB_NAME . "' obrisana (reset=1).";
    }

    // 2) Kreiraj bazu (ako ne postoji)
    $pdoServer->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $log[] = "Proverena/kreirana baza: " . DB_NAME;

    // 3) Konektuj se na konkretnu bazu
    $dsnDb = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $pdo = new PDO($dsnDb, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);

    // 4) Tabele
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            email VARCHAR(150) UNIQUE NOT NULL,
            password_hash VARCHAR(255) NOT NULL,
            role ENUM('user','admin') DEFAULT 'user',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_email (email)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");
    $log[] = "Tabela 'users' OK.";

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS accounts (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            name VARCHAR(100) NOT NULL,
            starting_balance DECIMAL(12,2) DEFAULT 0.00,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            INDEX idx_user (user_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");
    $log[] = "Tabela 'accounts' OK.";

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS categories (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            name VARCHAR(100) NOT NULL,
            type ENUM('prihod','rashod') NOT NULL,
            color VARCHAR(7) NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY unique_user_category (user_id, name, type),
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            INDEX idx_user_type (user_id, type)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");
    $log[] = "Tabela 'categories' OK.";

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS transactions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            account_id INT NOT NULL,
            category_id INT NOT NULL,
            amount DECIMAL(12,2) NOT NULL,
            type ENUM('prihod','rashod') NOT NULL,
            occurred_on DATE NOT NULL,
            note TEXT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (account_id) REFERENCES accounts(id) ON DELETE CASCADE,
            FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE RESTRICT,
            INDEX idx_occurred (occurred_on),
            INDEX idx_user_date (user_id, occurred_on)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");
    $log[] = "Tabela 'transactions' OK.";

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS budgets (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            category_id INT NOT NULL,
            period ENUM('mesec','kvartal','godina') NOT NULL,
            period_start DATE NOT NULL,
            limit_amount DECIMAL(12,2) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY unique_budget (user_id, category_id, period, period_start),
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE,
            INDEX idx_user_period (user_id, period_start)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");
    $log[] = "Tabela 'budgets' OK.";

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS goals (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            title VARCHAR(200) NOT NULL,
            target_amount DECIMAL(12,2) NOT NULL,
            current_amount DECIMAL(12,2) DEFAULT 0.00,
            deadline DATE NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            INDEX idx_user (user_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");
    $log[] = "Tabela 'goals' OK.";

    // 5) Seed (uvek od nule radi konzistentnosti instalacije)
    $pdo->exec("DELETE FROM accounts");
    $pdo->exec("DELETE FROM categories");
    $pdo->exec("DELETE FROM transactions");
    $pdo->exec("DELETE FROM budgets");
    $pdo->exec("DELETE FROM goals");
    $pdo->exec("DELETE FROM users");

    // svi koriste lozinku 'test123'
    $hash = password_hash('test123', PASSWORD_DEFAULT);

    $insUser = $pdo->prepare("INSERT INTO users (name, email, password_hash, role) VALUES (?,?,?,?)");
    $insUser->execute(['Admin Korisnik','admin@example.com',$hash,'admin']);
    $insUser->execute(['Marko Marković','marko@example.com',$hash,'user']);

    // Za Marka napravi i par demo stavki (opciono)
    $markoId = (int)$pdo->lastInsertId();

    $insAcc = $pdo->prepare("INSERT INTO accounts (user_id,name,starting_balance) VALUES (?,?,?)");
    $insAcc->execute([$markoId,'Glavni račun', 50000.00]);
    $mainAccountId = (int)$pdo->lastInsertId();
    $insAcc->execute([$markoId,'Štednja', 20000.00]);

    $insCat = $pdo->prepare("INSERT INTO categories (user_id,name,type,color) VALUES (?,?,?,?)");
    $insCat->execute([$markoId,'Plata','prihod','#28a745']);     $catPlata = (int)$pdo->lastInsertId();
    $insCat->execute([$markoId,'Freelance','prihod','#17a2b8']); $catFreel = (int)$pdo->lastInsertId();
    $insCat->execute([$markoId,'Hrana','rashod','#dc3545']);     $catHrana = (int)$pdo->lastInsertId();
    $insCat->execute([$markoId,'Transport','rashod','#ffc107']); $catTrans = (int)$pdo->lastInsertId();
    $insCat->execute([$markoId,'Računi','rashod','#6c757d']);    $catRac   = (int)$pdo->lastInsertId();
    $insCat->execute([$markoId,'Zabava','rashod','#e83e8c']);    $catZab   = (int)$pdo->lastInsertId();

    $insTr = $pdo->prepare("INSERT INTO transactions (user_id,account_id,category_id,amount,type,occurred_on,note) VALUES (?,?,?,?,?,?,?)");
    $insTr->execute([$markoId, $mainAccountId, $catPlata, 75000.00, 'prihod', date('Y-m-01'), 'Mesečna plata']);
    $insTr->execute([$markoId, $mainAccountId, $catHrana, 8500.00,  'rashod', date('Y-m-02'), 'Nedeljni šoping']);
    $insTr->execute([$markoId, $mainAccountId, $catTrans, 2000.00,  'rashod', date('Y-m-03'), 'Gorivo']);
    $insTr->execute([$markoId, $mainAccountId, $catRac,   12000.00, 'rashod', date('Y-m-05'), 'Struja, voda, internet']);
    $insTr->execute([$markoId, $mainAccountId, $catFreel, 15000.00, 'prihod', date('Y-m-06'), 'Web projekat']);

    $insBudget = $pdo->prepare("INSERT INTO budgets (user_id,category_id,period,period_start,limit_amount) VALUES (?,?,?,?,?)");
    $insBudget->execute([$markoId, $catHrana, 'mesec', date('Y-m-01'), 15000.00]);
    $insBudget->execute([$markoId, $catZab,   'mesec', date('Y-m-01'), 10000.00]);

    $insGoal = $pdo->prepare("INSERT INTO goals (user_id,title,target_amount,current_amount,deadline) VALUES (?,?,?,?,?)");
    $insGoal->execute([$markoId, 'Letovanje u Grčkoj', 100000.00, 35000.00, date('Y-06-01', strtotime('+1 year'))]);
    $insGoal->execute([$markoId, 'Nov laptop',        150000.00, 20000.00, date('Y-12-31')]);

    $log[] = "Seed podaci ubačeni (demo nalog: marko@example.com / test123).";

} catch (Throwable $e) {
    $error = $e->getMessage();
}

?>
<!doctype html>
<html lang="sr">
<head>
    <meta charset="utf-8">
    <title>Instalacija</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h1 class="h3 mb-3">Instalacija – Lične Finansije</h1>
                    <p class="text-muted mb-4">Ovaj korak priprema bazu i demo podatke</p>

                    <?php if ($error): ?>
                        <div class="alert alert-danger">
                            <strong>Greška:</strong><br><?= h($error) ?>
                        </div>
                        <p class="mb-0">Installer nije uspeo. Proveri poruku iznad (DB_HOST/PORT, MySQL servis, privilegije).</p>
                    <?php else: ?>
                        <ul class="list-group mb-3">
                            <?php foreach ($log as $line): ?>
                                <li class="list-group-item"><?= h($line) ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <div class="alert alert-success mb-4">
                            Instalacija završena uspešno.
                        </div>
                    <?php endif; ?>

                    <h5 class="mt-4 mb-3">Opcije</h5>
                    <div class="d-flex flex-wrap gap-2">
                        <a class="btn btn-primary" href="/auth/login.php">Idi na Login</a>
                        <a class="btn btn-outline-danger" href="/install.php?reset=1"
                           onclick="return confirm('Ovo će obrisati postojeće podatke i ponovo ubaciti demo. Nastaviti?')">
                            Reset &amp; Seed
                        </a>
                    </div>

                </div>
            </div>

            <div class="mt-3 text-center text-muted small">
                <!-- bez footer copyright-a, na zahtev -->
            </div>
        </div>
    </div>
</div>
</body>
</html>
