<?php
// login.php — Formulaire public, NE crée PAS de session tant que l'auth n'est pas validée
declare(strict_types=1);
date_default_timezone_set('Europe/Paris');

require_once __DIR__ . '/include/functions.php';

// Traitement login POST (sans session tant que non validé)
$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = (string)($_POST['password'] ?? '');

    $file = csv_path();
    if (!is_file($file)) { $error = "Aucun utilisateur enregistré."; }
    else {
        $ok = false;
        $rows = [];
        $foundIndex = null;

        $fp = fopen($file, 'r+');
        if ($fp === false) { $error = "Erreur d'accès au fichier."; }
        else {
            if (flock($fp, LOCK_EX)) {
                // lecture
                while (($data = fgetcsv($fp)) !== false) {
                    $rows[] = $data;
                }
                // vérif credentials
                foreach ($rows as $i => $r) {
                    $u = $r[0] ?? null;
                    $hash = $r[1] ?? null;
                    if ($u !== null && $u === $username && $hash && password_verify($password, $hash)) {
                        $ok = true;
                        $foundIndex = $i;
                        break;
                    }
                }
                if ($ok && $foundIndex !== null) {
                    // -> CRÉER la session MAINTENANT (après succès)
                    session_cookie_params_hardened();
                    session_start();
                    session_regenerate_id(true);
                    $_SESSION['user'] = $username;

                    // enregistrer le session_id en 4e colonne
                    $rows[$foundIndex][3] = session_id();

                    // réécriture atomique
                    ftruncate($fp, 0);
                    rewind($fp);
                    foreach ($rows as $row) {
                        fputcsv($fp, $row);
                    }
                    fflush($fp);
                    flock($fp, LOCK_UN);
                    fclose($fp);

                    header('Location: private.php');
                    exit;
                } else {
                    flock($fp, LOCK_UN);
                    fclose($fp);
                    $error = "Identifiants invalides.";
                }
            } else {
                fclose($fp);
                $error = "Verrouillage indisponible.";
            }
        }
    }
}

// Détection du thème pour le header
$h = (int)date('G');
$autoTheme = ($h >= 19 || $h < 7) ? 'dark' : 'light';
$theme = $autoTheme;
if (isset($_COOKIE['cookie_consent'])) {
    $raw = json_decode($_COOKIE['cookie_consent'], true);
    if (is_array($raw) && !empty($raw['preferences']) && isset($_COOKIE['theme']) && in_array($_COOKIE['theme'], ['light','dark'], true)) {
        $theme = $_COOKIE['theme'];
    }
}
include __DIR__ . '/include/header.php';
?>
<main class="container py-5" style="max-width: 480px;">
  <?php if ($error): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error, ENT_QUOTES) ?></div>
  <?php endif; ?>

  <form method="post" class="card p-4 shadow-sm">
    <div class="mb-3">
      <label class="form-label">Utilisateur</label>
      <input class="form-control" name="username" autocomplete="username" required>
    </div>
    <div class="mb-3">
      <label class="form-label">Mot de passe</label>
      <input type="password" class="form-control" name="password" autocomplete="current-password" required>
    </div>
    <button class="btn btn-primary w-100" type="submit">Se connecter</button>
  </form>
</main>
<?php include __DIR__ . '/include/footer.php'; ?>
</body>
</html>
