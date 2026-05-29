<?php
session_start();
require 'db.php';

if (!isset($_SESSION['id']) && !isset($_SESSION['invite'])) {
    header('Location: connexion.php');
    exit();
}


$estInvite = isset($_SESSION['invite']) && !isset($_SESSION['id']);
$user = null;
// Ajout de 'score_total' à 0 par défaut dans le tableau des stats
$stats = ['niveaux_termines' => 0, 'parties_jouees' => 0, 'score_total' => 0];

if (!$estInvite) {
    $req = $bdd->prepare("SELECT * FROM utilisateurs WHERE id = ?");
    $req->execute([$_SESSION['id']]);
    $user = $req->fetch(PDO::FETCH_ASSOC);

    $req = $bdd->prepare("
        SELECT 
            SUM(CASE WHEN termine = 1 THEN 1 ELSE 0 END) AS niveaux_termines,
            COUNT(*) AS parties_jouees,
            IFNULL(SUM(meilleur_score), 0) AS score_total
        FROM progression WHERE utilisateur_id = ?
    ");
    $req->execute([$_SESSION['id']]);
    $row = $req->fetch(PDO::FETCH_ASSOC);
    if ($row) $stats = $row;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Profil - Tile Burner</title>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@700&family=MedievalSharp&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/common.css">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/profil.css">
    <script src="../js/audio.js"></script>
</head>
<body>
<?php include 'header.php'; ?>
<main class="page-content">
<div class="box profil-box">

    <?php if ($estInvite): ?>
        <h2>Profil d'Invité</h2>
        <div class="profil-avatar">
            <img src="../images/avatarinvit.png" alt="avatar.png">
        </div>
        <p class="invite-msg">
            Vous parcourez le royaume en tant que <strong>voyageur anonyme</strong>.<br><br>
            Vos exploits ne sont <em>pas enregistrés</em> dans les chroniques.
        </p>
        <ul class="profil-info">
            <li><strong>Statut :</strong> Invité</li>
            <li><strong>Sauvegarde :</strong> Désactivée</li>
            <li><strong>Score :</strong> Non comptabilisé</li>
        </ul>
        <a href="inscription.php" class="btn-medieval">Créer un compte</a>
        <a href="connexion.php" class="btn-medieval">Se connecter</a>

    <?php else: ?>
        <h2>Profil de <?= htmlspecialchars($user['pseudo']) ?></h2>
        <div class="profil-avatar">
            <img src="../images/avatar.png" alt="avatar.png">
        </div>        
        <ul class="profil-info">
            <li><strong>Pseudo :</strong> <?= htmlspecialchars($user['pseudo']) ?></li>
            <li><strong>Email :</strong> <?= htmlspecialchars($user['email']) ?></li>
            <li><strong>Inscrit le :</strong> <?= date('d/m/Y', strtotime($user['date_inscription'])) ?></li>
            <li><strong>Dernière connexion :</strong> 
                <?= $user['derniere_connexion'] ? date('d/m/Y H:i', strtotime($user['derniere_connexion'])) : 'Première visite' ?>
            </li>
            <li><strong>Fortune actuelle :</strong> <?= (int)$user['points'] ?> Points</li>
            <li><strong>Niveaux terminés :</strong> <?= (int)$stats['niveaux_termines'] ?></li>
            <li><strong>Parties jouées :</strong> <?= (int)$stats['parties_jouees'] ?></li>
        </ul>
        <a href="jeu.php?mode=campagne&niveau_id=1" id="btn-aventure" class="btn-medieval">Reprendre l'aventure</a>
        <script>
        (function () {
            try {
                var uid = <?= json_encode((string)($_SESSION['id'] ?? 'invite')) ?>;
                var data = localStorage.getItem('tileburner_progression_' + uid);
                if (data) {
                    var p = JSON.parse(data);
                    var termines = p.niveauxTermines || [];
                    if (termines.length > 0) {
                        var dernierTermine = Math.max.apply(null, termines);
                        var prochain = Math.min(dernierTermine + 1, 20);
                        document.getElementById('btn-aventure').href =
                            'jeu.php?mode=campagne&niveau_id=' + prochain;
                    }
                }
            } catch(e) {}
        })();
        </script>
        <a href="logout.php" class="btn-medieval">Déconnexion</a>
    <?php endif; ?>

</div>
</main>
<?php include 'footer.php'; ?>
</body>
</html>
