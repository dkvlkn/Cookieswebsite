<?php
// logout.php — Déconnexion propre : supprimer session_id en CSV + détruire session + cookie
declare(strict_types=1);

require_once __DIR__ . '/include/functions.php';

function session_cookie_params_hardened(): void {
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'domain'   => $_SERVER['HTTP_HOST'] ?? '',
        'secure'   => is_https(),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
}

function csv_path(): string {
    return __DIR__ . '/../secret/password.csv';
}

$sessionCookieName = session_name();
if (!empty($_COOKIE[$sessionCookieName])) {
    // Ouvrir la session existante
    session_cookie_params_hardened();
    session_start();

    $username = $_SESSION['user'] ?? null;

    // Effacer le session_id du CSV
    if ($username) {
        $file = csv_path();
        if (is_file($file) && ($fp = fopen($file, 'r+')) !== false) {
            if (flock($fp, LOCK_EX)) {
                $rows = [];
                while (($row = fgetcsv($fp)) !== false) {
                    if (($row[0] ?? null) === $username) { $row[3] = ''; }
                    $rows[] = $row;
                }
                ftruncate($fp, 0);
                rewind($fp);
                foreach ($rows as $r) { fputcsv($fp, $r); }
                fflush($fp);
                flock($fp, LOCK_UN);
            }
            fclose($fp);
        }
    }

    // Détruire la session + cookie
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie($sessionCookieName, '', [
            'expires'  => time() - 42000,
            'path'     => $params['path'],
            'domain'   => $params['domain'],
            'secure'   => $params['secure'],
            'httponly' => $params['httponly'],
            'samesite' => $params['samesite'] ?? 'Lax',
        ]);
    }
    session_destroy();
}
header('Location: index.php');
exit;
