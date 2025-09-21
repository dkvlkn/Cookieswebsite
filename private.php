<?php
require __DIR__.'/private_check.php';
require_once __DIR__ . '/include/functions.php';

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
<main class="container py-5">
  <div class="alert alert-success">Bienvenue, <strong><?= htmlspecialchars($_SESSION['user'], ENT_QUOTES) ?></strong> !</div>
  <p>Choisis une page dans la barre de navigation ci-dessus.</p>
</main>
<?php include __DIR__ . '/include/footer.php'; ?>
</body>
</html>
