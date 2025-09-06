<?php
namespace App\Core;

class Session {

    // Postavi sesijsku vrednost
    public static function set(string $key, $value): void {
        $_SESSION[$key] = $value;
    }

    // Dobij sesijsku vrednost
    public static function get(string $key, $default = null) {
        return $_SESSION[$key] ?? $default;
    }

    // Proveri da li postoji ključ
    public static function has(string $key): bool {
        return isset($_SESSION[$key]);
    }

    // Ukloni ključ
    public static function remove(string $key): void {
        unset($_SESSION[$key]);
    }

    // Očisti sve sesije (strože)
    public static function destroy(): void {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        session_destroy();
    }

    // Flash poruke
    public static function flash(string $type, string $message): void {
        $_SESSION['flash'] = [
            'type' => $type,
            'message' => $message
        ];
    }

    // Dobij flash poruku
    public static function getFlash(): ?array {
        $flash = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);
        return $flash;
    }

    // CSRF token
    public static function generateCsrf(): string {
        $token = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $token;
        return $token;
    }

    // Verifikuj CSRF token
    public static function verifyCsrf(string $token): bool {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
}
