<?php
// generate.php — Création d'utilisateurs (admin)
// - Mode installation : accessible SANS login tant que le fichier CSV est absent ou vide
// - Sinon, page protégée (require private_check.php)
// - Verrouillage fichier (flock), réécriture atomique, validations

declare(strict_types=1);
date_default_timezone_set('Europe/Paris');

require_once __DIR__ . '/include/functions.php';

$csvFile = __DIR__ . '/secret/password.csv';
$secretDir = __DIR__ . '/secret';

// --- Déterminer le mode installation (pas d'utilisateur encore) ---
$setupMode = !is_file($csvFile) || (filesize($csvFile) === 0);

// --- Si nous ne sommes PAS en mode installation, protéger la page ---
if (!$setupMode) {
    require __DIR__ . '/private_check.php';
}

$err = null;
$ok  = null;

// --- Création si POST ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim($_POST['login'] ?? '');
    $pass  = (string)($_POST['password'] ?? '');

    if (!is_valid_login($login)) {
        $err = "Identifiant invalide (3–32 alphanum + _ . -)";
    } elseif (!is_valid_password($pass)) {
        $err = "Mot de passe trop court (min 6).";
    } else {
        // Préparer le dossier secret
        if (!is_dir($secretDir)) {
            if (!mkdir($secretDir, 0750, true) && !is_dir($secretDir)) {
                $err = "Impossible de créer le dossier secret/.";
            }
        }
        if (!$err) {
            // Ajoute l'utilisateur via la fonction utilitaire
            if (!function_exists('add_user_to_csv')) {
                /**
                 * Ajoute un utilisateur au fichier CSV.
                 * Retourne true en cas de succès, sinon une chaîne d'erreur.
                 */
                function add_user_to_csv(string $csvFile, string $login, string $pass) {
                    // Vérifier si l'utilisateur existe déjà
                    if (is_file($csvFile) && filesize($csvFile) > 0) {
                        if (($handle = fopen($csvFile, 'r')) !== false) {
                            while (($data = fgetcsv($handle)) !== false) {
                                if (($data[0] ?? '') === $login) {
                                    fclose($handle);
                                    return "Cet identifiant existe déjà.";
                                }
                            }
                            fclose($handle);
                        }
                    }
                    // Hacher le mot de passe
                    $hash = password_hash($pass, PASSWORD_DEFAULT);
                    $date = date('Y-m-d H:i:s');
                    // Écriture atomique avec verrouillage
                    $fp = fopen($csvFile, 'a');
                    if (!$fp) {
                        return "Impossible d'ouvrir le fichier.";
                    }
                    if (!flock($fp, LOCK_EX)) {
                        fclose($fp);
                        return "Impossible de verrouiller le fichier.";
                    }
                    $ok = fputcsv($fp, [$login, $hash, $date]);
                    flock($fp, LOCK_UN);
                    fclose($fp);
                    if (!$ok) {
                        return "Erreur lors de l'écriture.";
                    }
                    return true;
                }
            }
            $result = add_user_to_csv($csvFile, $login, $pass);
            if ($result === true) {
                $ok = "Utilisateur créé.";
                $setupMode = false;
            } else {
                $err = $result;
            }
        }
    }
}

// --- Liste utilisateurs (affichage) ---
// Définir la fonction get_users_from_csv si elle n'existe pas déjà
if (!function_exists('get_users_from_csv')) {
    function get_users_from_csv(string $csvFile): array {
        $users = [];
        if (is_file($csvFile) && filesize($csvFile) > 0) {
            if (($handle = fopen($csvFile, 'r')) !== false) {
                while (($data = fgetcsv($handle)) !== false) {
                    // Supposons que le CSV a au moins login et date de création
                    $login = $data[0] ?? '';
                    $date  = $data[2] ?? '';
                    if ($login !== '') {
                        $users[] = [$login, $date];
                    }
                }
                fclose($handle);
            }
        }
        return $users;
    }
}

$list = get_users_from_csv($csvFile);
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <title>Admin — Créer un utilisateur</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="css/style.css" rel="stylesheet">
</head>
<body>
<?php
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

<main class="container py-4" style="max-width:680px;">
  <?php if ($setupMode): ?>
    <div class="alert alert-warning">
      <strong>Mode installation :</strong>
      aucun utilisateur n’est encore enregistré.
      Cette page est accessible sans authentification pour créer le premier compte.
      (Dès qu’un utilisateur existe, la page redeviendra protégée.)
    </div>
  <?php endif; ?>

  <?php if ($ok): ?><div class="alert alert-success"><?= htmlspecialchars($ok, ENT_QUOTES) ?></div><?php endif; ?>
  <?php if ($err): ?><div class="alert alert-danger"><?= htmlspecialchars($err, ENT_QUOTES) ?></div><?php endif; ?>

  <form method="post" class="card p-4 shadow-sm mb-4" autocomplete="off">
    <div class="mb-3">
      <label class="form-label">Identifiant</label>
      <input name="login" class="form-control" required pattern="[A-Za-z0-9_.-]{3,32}"
             value="<?= isset($_POST['login']) ? htmlspecialchars($_POST['login'], ENT_QUOTES) : '' ?>">
    </div>
    <div class="mb-3">
      <label class="form-label">Mot de passe</label>
      <input type="password" name="password" class="form-control" required minlength="6" autocomplete="new-password">
    </div>
    <button class="btn btn-primary">Créer</button>
  </form>

  <div class="card p-3 shadow-sm">
    <h2 class="h6">Utilisateurs existants</h2>
    <div class="table-responsive">
      <table class="table table-sm align-middle mb-0">
        <thead><tr><th>Identifiant</th><th>Créé le</th></tr></thead>
        <tbody>
        <?php foreach ($list as [$u,$d]): ?>
          <tr>
            <td><?= htmlspecialchars($u, ENT_QUOTES) ?></td>
            <td><?= htmlspecialchars($d, ENT_QUOTES) ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</main>

<?php include __DIR__ . '/include/footer.php'; ?>
</body>
</html>
