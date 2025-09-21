<?php
// index.php — Accueil public, consentement cookies + thème auto
// Yanis SAMAH, L3 Informatique, Université de Cergy, UE Développement Web Avancé

declare(strict_types=1);
date_default_timezone_set('Europe/Paris');

require_once __DIR__ . '/include/functions.php';

/* ---------- Helpers ---------- */
function is_https(): bool {
    if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') return true;
    if (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') return true;
    return false;
}

/**
 * $httpOnly:
 *  - true  : pour les cookies sensibles (session)
 *  - false : pour les préférences (theme, last_visit) afin de pouvoir les supprimer côté client
 */
function set_cookie(string $name, string $value, int $days = 365, bool $httpOnly = false): void {
    setcookie($name, $value, [
        'expires'  => time() + 60 * 60 * 24 * $days,
        'path'     => '/',
        'secure'   => is_https(),
        'httponly' => $httpOnly,
        'samesite' => 'Lax',
    ]);
}

function del_cookie(string $name): void {
    setcookie($name, '', [
        'expires'  => time() - 3600,
        'path'     => '/',
        'secure'   => is_https(),
        'httponly' => false,
        'samesite' => 'Lax',
    ]);
}

/* ---------- Consentement ---------- */
$consent = ['preferences' => false];
if (isset($_COOKIE['cookie_consent'])) {
    $raw = json_decode($_COOKIE['cookie_consent'], true);
    if (is_array($raw)) {
        $consent['preferences'] = !empty($raw['preferences']);
    }
}
$hasConsent = isset($_COOKIE['cookie_consent']);
$prefOk     = $consent['preferences'];

/* ---------- Actions serveur liées au consentement ---------- */
if (isset($_GET['clear_prefs'])) {
    del_cookie('theme');
    del_cookie('last_visit');
    header('Location: index.php');
    exit;
}

/* ---------- Thème (auto) ---------- */
$h         = (int)date('G'); // 0..23
$autoTheme = ($h >= 19 || $h < 7) ? 'dark' : 'light';
$theme     = $autoTheme;

if ($prefOk && isset($_COOKIE['theme']) && in_array($_COOKIE['theme'], ['light','dark'], true)) {
    $theme = $_COOKIE['theme'];
}

/* Toggle thème (seulement si Préférences consenties) */
if (isset($_GET['toggle_theme'])) {
    if ($prefOk) {
        $newTheme = ($theme === 'dark') ? 'light' : 'dark';
        set_cookie('theme', $newTheme, 365, false);
        header('Location: index.php');
        exit;
    } else {
        header('Location: index.php#cookies');
        exit;
    }
}

/* ---------- last_visit (préférences uniquement) ---------- */
$last_visit_ts = null;
if ($prefOk) {
    if (isset($_COOKIE['last_visit'])) {
        $ts = filter_var($_COOKIE['last_visit'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 946684800]]);
        if ($ts !== false) $last_visit_ts = $ts;
    }
    set_cookie('last_visit', (string)time(), 365, false);
}

/* ---------- En-têtes sécurité ---------- */
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');

$date_format = 'd/m/Y H:i';
?>
<!doctype html>
<html lang="fr" data-theme="<?= htmlspecialchars($theme, ENT_QUOTES) ?>">
<head>
    <meta charset="utf-8">
    <title>Accueil — Site de Yanis SAMAH</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
</head>
<body>
<?php include __DIR__ . '/include/header.php'; ?>

<main class="container py-5 text-center">
    <h2 class="display-6 mb-3">Bienvenue !</h2>
    <?php if ($prefOk): ?>
        <p class="text-secondary">
            <?= $last_visit_ts ? 'Dernière visite : ' . date($date_format, (int)$last_visit_ts) : 'Première visite 😊' ?>
        </p>
    <?php else: ?>
        <p class="text-secondary">
            Les cookies de préférences sont désactivés. Active-les pour mémoriser ton thème et ta dernière visite.
        </p>
    <?php endif; ?>

    <div class="my-4">
        <a href="login.php" class="btn btn-primary" >Se connecter</a>
        <a href="generate.php" class="btn btn-outline-secondary">Créer un compte (admin)</a>
    </div>

    <div class="row justify-content-center g-4 mt-4">
        <div class="col-10 col-md-5">
            <img src="img/img1.jpg" class="img-fluid rounded-4 shadow-sm" alt="Image 1">
        </div>
        <div class="col-10 col-md-5">
            <img src="img/img2.jpg" class="img-fluid rounded-4 shadow-sm" alt="Image 2">
        </div>
    </div>
</main>

<?php include __DIR__ . '/include/footer.php'; ?>

<!-- Panneau consentement -->
<div id="cookieBackdrop" class="cookie-backdrop" role="presentation" aria-hidden="true"></div>
<div id="cookiePanel" class="cookie-panel" role="dialog" aria-modal="true" aria-labelledby="cookieTitle" style="display:none">
    <div class="card cookie-card">
        <div class="card-body">
            <div class="d-flex align-items-start justify-content-between">
                <div class="pe-3">
                    <h5 id="cookieTitle" class="card-title mb-2">Cookies & confidentialité</h5>
                    <p class="card-text mb-2">
                        Nous utilisons uniquement des cookies nécessaires et de préférences (thème, dernière visite).
                        Choisis ce que tu acceptes :
                    </p>
                </div>
                <button class="btn-close" aria-label="Fermer" onclick="hideConsent()"></button>
            </div>
            <div class="form-check my-2">
                <input class="form-check-input" type="checkbox" id="ckPrefs">
                <label class="form-check-label" for="ckPrefs">
                    Cookies de préférences (mémoriser le thème & la dernière visite)
                </label>
            </div>
            <div class="d-flex gap-2 mt-3 cookie-actions">
                <button class="btn btn-outline-secondary" onclick="denyAll()">Tout refuser</button>
                <button class="btn btn-primary" onclick="acceptAll()">Tout accepter</button>
                <button class="btn btn-success ms-auto" onclick="saveSelection()">Enregistrer</button>
            </div>
        </div>
    </div>
</div>

<script>
(function(){
    const hasConsent = <?= $hasConsent ? 'true' : 'false' ?>;
    const prefOk = <?= $prefOk ? 'true' : 'false' ?>;

    const panel = document.getElementById('cookiePanel');
    const backdrop = document.getElementById('cookieBackdrop');
    const ckPrefs = document.getElementById('ckPrefs');

    function showConsent() {
        if (backdrop) backdrop.style.display = 'block';
        if (panel) panel.style.display = 'block';
        if (ckPrefs) ckPrefs.checked = prefOk;
    }
    window.hideConsent = function(){
        if (backdrop) backdrop.style.display = 'none';
        if (panel) panel.style.display = 'none';
    };
    window.showConsent = showConsent; // Expose for footer button

    function setConsent(obj) {
        const v = JSON.stringify(obj);
        document.cookie = "cookie_consent="+encodeURIComponent(v)+"; Max-Age="+(180*24*3600)+
                         "; Path=/; SameSite=Lax"+(location.protocol==='https:'?"; Secure":"");
    }
    function deleteCookie(name) {
        document.cookie = name+"=; Max-Age=0; Path=/; SameSite=Lax"+(location.protocol==='https:'?"; Secure":"");
    }

    window.acceptAll = function() {
        setConsent({preferences:true});
        location.reload();
    };
    window.denyAll = function() {
        setConsent({preferences:false});
        deleteCookie('theme'); deleteCookie('last_visit');
        location.href = 'index.php?clear_prefs=1';
    };
    window.saveSelection = function() {
        const wantPrefs = !!(ckPrefs && ckPrefs.checked);
        setConsent({preferences: wantPrefs});
        if (!wantPrefs) {
            deleteCookie('theme'); deleteCookie('last_visit');
            location.href = 'index.php?clear_prefs=1';
        } else {
            location.reload();
        }
    };

    // Show cookie consent only if no consent is set or explicitly requested via #cookies
    if (!hasConsent || location.hash === '#cookies') {
        showConsent();
    }
})();
</script>
</body>
</html>