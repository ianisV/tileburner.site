<?php
session_start();
require 'db.php';

/* Mode invité */
if (isset($_GET['invite'])) {
    $_SESSION['invite'] = true;
    header('Location: index.php');
    exit();
}

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pseudo = trim($_POST['pseudo'] ?? '');
    $mdp = trim($_POST['mdp'] ?? '');

    if (!empty($pseudo) && !empty($mdp)) {
        $req = $bdd->prepare("SELECT * FROM utilisateurs WHERE pseudo = ?");
        $req->execute([$pseudo]);
        $user = $req->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($mdp, $user['mot_de_passe'])) {
            $_SESSION['id'] = $user['id'];
            $_SESSION['pseudo'] = $user['pseudo'];
            unset($_SESSION['invite']);

            // Mise à jour de la dernière connexion
            $bdd->prepare("UPDATE utilisateurs SET derniere_connexion = NOW() WHERE id = ?")
                ->execute([$user['id']]);

            header('Location: index.php');
            exit();
        } else {
            $error = "La herse reste fermée... Identifiants incorrects.";
        }
    } else {
        $error = "Veuillez remplir tous les champs.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Forteresse de Connexion</title>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@700&family=MedievalSharp&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/common.css">
    <link rel="stylesheet" href="../css/connexion.css">
    <script src="../js/audio.js"></script>
</head>
<body>
<?php include 'header.php'; ?>
<main class="page-content">
<div class="box">
    <h2>Entrez dans la Place</h2>

    <?php if (isset($_GET['reg_success'])): ?>
        <div class="success">Votre nom est inscrit au registre !</div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST">
        <input type="text" name="pseudo" placeholder="Pseudo" required>
        <input type="password" name="mdp" placeholder="Mot de passe" required>
        <button type="submit">Se connecter</button>
    </form>

    <a class="guest-btn" href="connexion.php?invite=1">Continuer en tant qu'invité</a>
    <a class="register-link" href="inscription.php">Créer un compte</a>
</div>
</main>
<?php include 'footer.php'; ?>
</body>
</html>
