<?php require __DIR__.'/private_check.php'; ?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <title>Espace privé — Exercices</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Bootstrap + ton style -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="css/style.css" rel="stylesheet">
</head>
<body>
<header class="p-3 border-bottom">
  <div class="container d-flex justify-content-between align-items-center">
    <h1 class="h4 m-0">Espace privé — Exercices</h1>
    <nav class="d-flex align-items-center gap-2">
      <a href="private.php" class="btn btn-link">← Espace privé</a>
      <a href="private2.php" class="btn btn-link">Privé 2</a>
      <a href="private3.php" class="btn btn-link">Privé 3</a>
      <a href="logout.php" class="btn btn-danger">Déconnexion</a>
    </nav>
  </div>
</header>

<main class="container py-4">

  <!-- Exercice 5 — Horloge / DateTime -->
  <section class="mb-4">
    <div class="card p-4 exo-card">
      <h2 class="h5 exo-title mb-3">Exercice — Date & heure en direct</h2>
      <p id="datetime" class="lead mb-0"></p>
    </div>
  </section>

  <!-- Exercice 4 — Accordéon (+ ARIA dans JS) -->
  <section class="mb-4">
    <div class="card p-4 exo-card">
      <h2 class="h5 exo-title mb-3">Exercice — Accordéon</h2>

      <div class="accordion">

        <button class="accordion-toggle mb-2" data-target="#acc-panel-1">
          Section 1 — Introduction
        </button>
        <div class="accordion-content" id="acc-panel-1">
          <p class="mb-0">Contenu de la section 1. Texte de démonstration.</p>
        </div>

        <button class="accordion-toggle mb-2" data-target="#acc-panel-2">
          Section 2 — Détails
        </button>
        <div class="accordion-content" id="acc-panel-2">
          <p class="mb-0">Contenu de la section 2. Autres informations.</p>
        </div>

        <button class="accordion-toggle mb-2" data-target="#acc-panel-3">
          Section 3 — Conclusion
        </button>
        <div class="accordion-content" id="acc-panel-3">
          <p class="mb-0">Contenu de la section 3. Fin de l’accordéon.</p>
        </div>

      </div>
    </div>
  </section>

  <!-- Exercice 6 — Slideshow -->
  <section class="mb-4">
    <div class="card p-4 exo-card">
      <h2 class="h5 exo-title mb-3">Exercice — Slideshow</h2>

      <div class="slideshow-container">

        <div class="slide active">
          <img src="img/slide1.jpg" alt="Slide 1">
        </div>

        <div class="slide">
          <img src="img/slide2.jpg" alt="Slide 2">
        </div>

        <div class="slide">
          <img src="img/slide3.jpg" alt="Slide 3">
        </div>

      </div>

      <div class="slideshow-controls mt-3">
        <button class="btn btn-outline-secondary" onclick="changeSlide(-1)">⟵ Précédent</button>
        <div class="d-flex align-items-center gap-2 mx-3">
          <span class="dot active" onclick="currentSlide(1)"></span>
          <span class="dot" onclick="currentSlide(2)"></span>
          <span class="dot" onclick="currentSlide(3)"></span>
        </div>
        <button class="btn btn-outline-secondary" onclick="changeSlide(1)">Suivant ⟶</button>
      </div>

      <!-- (facultatif) contrôle auto-play -->
      <!--
      <div class="mt-3">
        <button class="btn btn-sm btn-outline-primary" onclick="startSlideshow()">Démarrer</button>
        <button class="btn btn-sm btn-outline-danger" onclick="stopSlideshow()">Arrêter</button>
      </div>
      -->
    </div>
  </section>

  <!-- Exercice 7 — 2^n -->
  <section class="mb-4">
    <div class="card p-4 exo-card">
      <h2 class="h5 exo-title mb-3">Exercice — Calcul 2^n</h2>
      <p class="mb-3">Clique pour saisir <code>n</code> et obtenir <code>2^n</code> (borné à 1024).</p>
      <button class="btn btn-primary" onclick="pow2Prompt()">Calculer 2^n</button>
    </div>
  </section>

  <!-- Exercice 8 — NIR (INSEE) -->
  <section class="mb-4">
    <div class="card p-4 exo-card">
      <h2 class="h5 exo-title mb-3">Exercice — Vérification NIR (INSEE)</h2>
      <form class="row g-3">
        <div class="col-12 col-md-6">
          <label class="form-label" for="ssn">NIR (13 caractères, 2A/2B accepté)</label>
          <input id="ssn" class="form-control" placeholder="ex: 1 84 12 2A 123 456" autocomplete="off">
        </div>
        <div class="col-12 col-md-3">
          <label class="form-label" for="sskey">Clé (2 chiffres)</label>
          <input id="sskey" class="form-control" placeholder="ex: 37" maxlength="2" autocomplete="off">
        </div>
        <div class="col-12 col-md-3 d-flex align-items-end">
          <button id="btnCheckNIR" class="btn btn-primary w-100">Vérifier</button>
        </div>
      </form>
      <div id="ssResult" class="mt-3"></div>
    </div>
  </section>

  <!-- Exercice 9 — Mini-jeu Canvas -->
  <section class="mb-4">
    <div class="card p-4 exo-card">
      <h2 class="h5 exo-title mb-3">Exercice — Mini-jeu Canvas</h2>
      <div class="mb-3">
        <!-- Taille ajustable selon ton besoin -->
        <canvas id="gameCanvas" width="720" height="360" aria-label="Jeu Canvas"></canvas>
      </div>
      <div class="d-flex gap-2">
        <button id="btnGameStart" class="btn btn-success">Démarrer</button>
        <button id="btnGameStop" class="btn btn-warning">Pause</button>
        <button id="btnGameReset" class="btn btn-danger">Reset</button>
      </div>
      <p class="text-muted mt-2 mb-0">Contrôles : flèches du clavier.</p>
    </div>
  </section>

  <!-- Exercice 3 — Back to top -->
  <button id="jsBackToTop" class="btn btn-primary back-to-top" type="button" onclick="scrollToTop(event)">↑ Haut</button>
</main>

<script src="js/exercices.js"></script>
</body>
</html>
