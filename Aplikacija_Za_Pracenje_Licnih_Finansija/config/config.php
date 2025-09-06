<?php
// ===== Konfiguracija baze podataka =====
// Ako koristiš XAMPP i prebacio si MySQL na 3307, promeni DB_PORT na '3307'.
define('DB_HOST', '127.0.0.1');     // sigurnije od 'localhost' na Windows-u
define('DB_PORT', '3306');          // promeni na '3307' ako MySQL sluša na 3307
define('DB_NAME', 'finance_tracker');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// ===== Aplikacijske konstante =====
// Ako koristiš ugrađeni PHP server sa -S localhost:8000, ovo je praktično.
define('APP_NAME', 'Lične Finansije');
define('APP_URL', 'http://localhost:8000');
define('ITEMS_PER_PAGE', 10);

// ===== Putanje =====
define('ROOT_PATH', dirname(__DIR__));
define('PUBLIC_PATH', ROOT_PATH . '/public');
define('SRC_PATH', ROOT_PATH . '/src');
define('VIEWS_PATH', ROOT_PATH . '/views');

// ===== Sesija =====
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
session_start();
