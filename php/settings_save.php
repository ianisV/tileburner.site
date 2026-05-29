<?php
session_start();
require_once 'db.php';

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    die("Accès interdit.");
}

// Récupération + validation
$volume_master = max(0, min(100, (int)($_POST['volume_master'] ?? 50)));
$volume_music  = max(0, min(100, (int)($_POST['volume_music']  ?? 50)));
$volume_sfx    = max(0, min(100, (int)($_POST['volume_sfx']    ?? 50)));
$muted         = isset($_POST['muted']) ? 1 : 0;
$language      = 'fr';

// Sauvegarde en BDD si connecté
$sauvegarde_bdd = false;
if (isset($_SESSION['id'])) {
    try {
        $user = ['id' => (int)$_SESSION['id']];

        if ($user) {
            $sql = "INSERT INTO settings 
                        (utilisateur_id, volume_master, volume_music, volume_sfx, muted, language)
                    VALUES 
                        (:uid, :vm, :vmu, :vs, :mu, :lg)
                    ON DUPLICATE KEY UPDATE
                        volume_master = :vm2,
                        volume_music  = :vmu2,
                        volume_sfx    = :vs2,
                        muted         = :mu2,
                        language      = :lg2";
            $stmt = $bdd->prepare($sql);
            $stmt->execute([
                ':uid'  => $user['id'],
                ':vm'   => $volume_master,
                ':vmu'  => $volume_music,
                ':vs'   => $volume_sfx,
                ':mu'   => $muted,
                ':lg'   => $language,
                ':vm2'  => $volume_master,
                ':vmu2' => $volume_music,
                ':vs2'  => $volume_sfx,
                ':mu2'  => $muted,
                ':lg2'  => $language,
            ]);
            $sauvegarde_bdd = true;
        }
    } catch (PDOException $e) {
        // erreur silencieuse
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Paramètres enregistrés</title>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@700&family=MedievalSharp&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/common.css">
    <link rel="stylesheet" href="../css/settings_save.css">
    <script src="../js/audio.js"></script>
</head>
<body>
<?php include 'header.php'; ?>

<main class="page-content">
    <div class="box">
        <h1>Paramètres enregistrés ✅</h1>

        <?php if ($sauvegarde_bdd): ?>
            <p style="color:#90ee90;">💾 Sauvegardé dans votre compte</p>
        <?php elseif (isset($_SESSION['invite'])): ?>
            <p style="opacity:0.7;">ℹ️ Mode invité : paramètres sauvegardés uniquement sur ce navigateur</p>
        <?php endif; ?>

        <p><strong>Volume général :</strong> <?= $volume_master ?>%</p>
        <p><strong>Musique :</strong> <?= $volume_music ?>%</p>
        <p><strong>Effets sonores :</strong> <?= $volume_sfx ?>%</p>

        <a class="btn" href="settings.php">⬅ Retour aux paramètres</a>
    </div>
</main>

<?php include 'footer.php'; ?>
</body>
</html>
