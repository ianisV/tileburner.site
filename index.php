<?php
session_start();
if (!isset($_SESSION['pseudo']) && !isset($_SESSION['invite'])) {
    header('Location: connexion.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Accueil - Tile Burner</title>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@700&family=MedievalSharp&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/common.css">
    <link rel="stylesheet" href="css/style.css">
    <script src="js/audio.js"></script>
</head>
<body>
<?php include 'header.php'; ?>
<main class="page-content">
    <div class="box">
        <?php if(isset($_SESSION['pseudo'])): ?>
            <h1>Bienvenue <?= htmlspecialchars($_SESSION['pseudo']) ?></h1>
        <?php elseif(isset($_SESSION['invite'])): ?>
            <h1>Bienvenue Invité</h1>
            <p style="font-size:14px;opacity:0.8;">Créez un compte pour sauvegarder vos progrès !</p>
        <?php endif; ?>

        <a href="menu.php" class="btn-medieval">Jouer</a>
        <a href="profil.php" class="btn-medieval">Profil</a>
        <a href="settings.php" class="btn-medieval">Paramètres</a>
        <?php if(isset($_SESSION['pseudo'])): ?>
            <a href="logout.php" class="btn-medieval">Déconnexion</a>
        <?php else: ?>
            <a href="logout.php" class="btn-medieval">Se connecter</a>
        <?php endif; ?>
    </div>
</main>
<?php include 'footer.php'; ?>
</body>
</html>
