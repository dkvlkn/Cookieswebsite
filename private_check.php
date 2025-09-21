<?php
// private_check.php — Garde : ne crée pas de cookie si l'utilisateur n'est pas connecté
declare(strict_types=1);

require_once __DIR__ . '/include/functions.php';

$sessionCookieName = session_name();

// Si aucun cookie de session → redirection SANS créer de session
if (empty($_COOKIE[$sessionCookieName])) {
    header('Location: login.php');
    exit;
}

// Sinon on ouvre la session existante
session_cookie_params_hardened();
session_start();

if (empty($_SESSION['user'])) {
    session_write_close();
    header('Location: login.php');
    exit;
}

// Valider session_id contre CSV
$file = csv_path();
if (!is_file($file)) {
    // Fichier absent : invalider et renvoyer au login
    session_unset(); session_destroy();
    header('Location: login.php');
    exit;
}

$valid = false;
$fp = fopen($file, 'r');
if ($fp !== false) {
    if (flock($fp, LOCK_SH)) {
        while (($row = fgetcsv($fp)) !== false) {
            $u = $row[0] ?? null;
            $sid = $row[3] ?? null;
            if ($u === $_SESSION['user'] && $sid === session_id()) { $valid = true; break; }
        }
        flock($fp, LOCK_UN);
        fclose($fp);
    } else {
        fclose($fp);
    }
}
if (!$valid) {
    session_unset(); session_destroy();
    header('Location: login.php');
    exit;
}

function is_https(): bool {
    if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') return true;
    if (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') return true;
    return false;
}
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
function csv_path(): string { return __DIR__ . '/../secret/password.csv'; }
