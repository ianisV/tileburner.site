<?php
session_start();
require 'db.php';

if (!isset($_SESSION['id']) && !isset($_SESSION['invite'])) {
    header('Location: connexion.php');
    exit();
}

$estInvite = isset($_SESSION['invite']) && !isset($_SESSION['id']);
$user_id = $_SESSION['id'] ?? 0;
$message = "";
$scoreTotal = 0;
$tousLesSkins = [];
$skinsPossedes = [];

if (!$estInvite) {
   
    $reqPoints = $bdd->prepare("SELECT points FROM utilisateurs WHERE id = ?");
    $reqPoints->execute([$user_id]);
    $pointsJoueur = $reqPoints->fetch(PDO::FETCH_ASSOC)['points'] ?? 0;
    
    if (isset($_POST['acheter_skin'])) {
        $skin_id = (int)$_POST['skin_id'];
        
        $reqSkin = $bdd->prepare("SELECT * FROM boutique_skins WHERE id = ?");
        $reqSkin->execute([$skin_id]);
        $skin = $reqSkin->fetch(PDO::FETCH_ASSOC);
        
        if ($skin) {
          
            $reqCheck = $bdd->prepare("SELECT 1 FROM achats_utilisateurs WHERE utilisateur_id = ? AND skin_id = ?");
            $reqCheck->execute([$user_id, $skin_id]);
            
            if ($reqCheck->fetch()) {
                $message = "❌ Vous possédez déjà ce skin !";
            } elseif ($pointsJoueur < $skin['prix']) {
                $message = "❌ Solde de points insuffisant pour acquérir ce skin.";
            } else {
            
                $bdd->beginTransaction();
                try {
                    
                    $reqAchat = $bdd->prepare("INSERT INTO achats_utilisateurs (utilisateur_id, skin_id) VALUES (?, ?)");
                    $reqAchat->execute([$user_id, $skin_id]);
                    
                    $reqDeduit = $bdd->prepare("UPDATE utilisateurs SET points = points - ? WHERE id = ?");
                    $reqDeduit->execute([$skin['prix'], $user_id]);
                    
                    $bdd->commit();
                    
                    $message = "🎉 Félicitations ! Vous avez débloqué : " . htmlspecialchars($skin['nom']);
                    
                    $pointsJoueur -= $skin['prix'];
                } catch (Exception $e) {
                    $bdd->rollBack();
                    $message = "❌ Une erreur est survenue lors de l'achat.";
                }
            }
        }
    }

 
    $reqSkins = $bdd->query("SELECT * FROM boutique_skins ORDER BY prix ASC");
    $tousLesSkins = $reqSkins->fetchAll(PDO::FETCH_ASSOC);

    $reqPossedes = $bdd->prepare("SELECT skin_id FROM achats_utilisateurs WHERE utilisateur_id = ?");
    $reqPossedes->execute([$user_id]);
    $skinsPossedes = $reqPossedes->fetchAll(PDO::FETCH_COLUMN);
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Boutique Royale - Tile Burner</title>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@700&family=MedievalSharp&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/common.css">
    <link rel="stylesheet" href="../css/boutique.css"> </head>
<body>
    <?php include 'header.php'; ?>

    <h1>Boutique des Équipements</h1>
    <p class="boutique-description">Dépensez la gloire acquise dans vos donjons pour débloquer des apparences exclusives.</p>
    
    <?php if ($estInvite): ?>
        <p class="fortune-msg">✨ Fortune actuelle : <strong>Non disponible (Mode Invité)</strong></p>
        <div class="status-msg">⚠️ Créez un compte pour accumuler des scores et débloquer les skins de la boutique !</div>
    <?php else: ?>
        <p class="fortune-msg">✨ Votre Fortune : <strong><?= (int)$pointsJoueur ?> Points</strong></p>
        
        <?php if (!empty($message)): ?>
            <div class="status-msg"><?= $message ?></div>
        <?php endif; ?>

        <div class="boutique-grid">
            <?php foreach ($tousLesSkins as $skin): ?>
                <?php $possede = in_array($skin['id'], $skinsPossedes); ?>
                <div class="skin-card">
                    <div class="skin-apercu">
<!-- APRÈS -->
<img src="<?= htmlspecialchars($skin['image_url']) ?>" alt="Aperçu Skin">

</div>
                    <h3><?= htmlspecialchars($skin['nom']) ?></h3>
                    <div class="skin-prix">💰 <?= $skin['prix'] ?> Pts</div>
                    
                    <?php if ($possede): ?>
                        <span class="badge-acquis">Acquis ✔</span>
                    <?php else: ?>
                        <form method="POST" action="">
                            <input type="hidden" name="skin_id" value="<?= $skin['id'] ?>">
                            <button type="submit" name="acheter_skin" class="btn-acheter" <?= $pointsJoueur < $skin['prix'] ? 'disabled' : '' ?>>
                                Acheter
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</body>
</html>