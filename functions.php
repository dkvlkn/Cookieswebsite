<?php
if (!function_exists('is_https')) {
    function is_https(): bool {
        if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') return true;
        if (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') return true;
        return false;
    }
}

if (!function_exists('set_cookie')) {
    function set_cookie(string $name, string $value, int $days = 365, bool $httpOnly = false): void {
        setcookie($name, $value, [
            'expires'  => time() + 60 * 60 * 24 * $days,
            'path'     => '/',
            'secure'   => is_https(),
            'httponly' => $httpOnly,
            'samesite' => 'Lax',
        ]);
    }
}

if (!function_exists('del_cookie')) {
    function del_cookie(string $name): void {
        setcookie($name, '', [
            'expires'  => time() - 3600,
            'path'     => '/',
            'secure'   => is_https(),
            'httponly' => false,
            'samesite' => 'Lax',
        ]);
    }
}

function is_valid_login(string $login): bool {
    return (bool)preg_match('/^[A-Za-z0-9_.-]{3,32}$/', $login);
}

function is_valid_password(string $pass): bool {
    return strlen($pass) >= 6;
}

function add_user_to_csv(string $csvFile, string $login, string $pass) {
    // Ouvrir le fichier
    $fp = is_file($csvFile) ? fopen($csvFile, 'r+') : fopen($csvFile, 'w+');
    if ($fp === false) {
        return "Erreur d'accès au fichier.";
    }
    $err = null;
    if (flock($fp, LOCK_EX)) {
        // Lire les lignes existantes
        $rows = [];
        while (($r = fgetcsv($fp)) !== false) {
            $rows[] = $r;
        }
        // Vérifier doublon
        foreach ($rows as $r) {
            if (($r[0] ?? null) === $login) {
                $err = "Utilisateur déjà existant.";
                break;
            }
        }
        // Ajouter et réécrire si OK
        if (!$err) {
            $rows[] = [$login, password_hash($pass, PASSWORD_DEFAULT), date('c'), ''];
            ftruncate($fp, 0);
            rewind($fp);
            foreach ($rows as $row) {
                fputcsv($fp, $row);
            }
            fflush($fp);
            @chmod($csvFile, 0640);
        }
        flock($fp, LOCK_UN);
    } else {
        $err = "Verrouillage indisponible (flock).";
    }
    fclose($fp);
    return $err ?: true;
}

function get_users_from_csv(string $csvFile): array {
    $list = [];
    if (is_file($csvFile)) {
        $fp = fopen($csvFile, 'r');
        if ($fp !== false) {
            if (flock($fp, LOCK_SH)) {
                while (($r = fgetcsv($fp)) !== false) {
                    if (!empty($r[0])) {
                        $list[] = [$r[0], $r[2] ?? ''];
                    }
                }
                flock($fp, LOCK_UN);
            }
            fclose($fp);
        }
    }
    return $list;
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

function csv_path(): string {
    return __DIR__ . '/../secret/password.csv';
}