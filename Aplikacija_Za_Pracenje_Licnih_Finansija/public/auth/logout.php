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
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    
    if (file_exists($file)) {
        require $file;
    }
});

$auth = new Auth();
$auth->logout();

Session::flash('success', 'Uspešno ste se odjavili.');
redirect('/auth/login.php');
