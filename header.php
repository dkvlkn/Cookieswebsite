<?php
?>
<header class="d-flex align-items-center justify-content-between p-3 border-bottom">
    <div class="d-flex align-items-center gap-3">
        <a href="index.php" class="d-inline-block" title="Accueil">
            <img src="img/logo.png" alt="Logo du site" style="height:60px;">
        </a>
        <nav class="d-none d-sm-flex gap-3">
            <a class="link-brand text-decoration-none" href="index.php">Accueil</a>
            <a class="link-brand text-decoration-none" href="login.php">Espace privé</a>
        </nav>
    </div>
    <div class="d-flex align-items-center gap-2">
        <button class="btn btn-link text-decoration-none p-0" onclick="location.href='?toggle_theme=1'" title="Changer le thème">
            <?php if ($theme === 'dark'): ?>
                <img src="img/sun.svg" alt="Passer en mode clair" style="height:28px;">
            <?php else: ?>
                <img src="img/moon.svg" alt="Passer en mode sombre" style="height:28px;">
            <?php endif; ?>
        </button>
    </div>
</header>
<?php
