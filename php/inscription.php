<?php
session_start();
require 'db.php';

$error = "";

/* INSCRIPTION */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $pseudo = trim($_POST['pseudo'] ?? '');
    $email  = trim($_POST['email'] ?? '');
    $mdp    = trim($_POST['mdp'] ?? '');

    if (!empty($pseudo) && !empty($email) && !empty($mdp)) {

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

            $error = "Email invalide.";

        } else {

            $check = $bdd->prepare('SELECT id FROM utilisateurs WHERE pseudo = ? OR email = ?');
            $check->execute([$pseudo, $email]);

            if ($check->rowCount() > 0) {

                $error = "Ce nom ou cet email est déjà gravé dans la pierre.";

            } else {

                $hash = password_hash($mdp, PASSWORD_DEFAULT);

                $ins = $bdd->prepare('
                    INSERT INTO utilisateurs (pseudo, email, mot_de_passe)
                    VALUES (?, ?, ?)
                ');

                $ins->execute([$pseudo, $email, $hash]);
                unset($_SESSION['invite']);


                header('Location: connexion.php?reg_success=1');
                exit();
            }
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

    <title>Inscription - Forteresse</title>

    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@700&family=MedievalSharp&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/common.css">

    <link rel="stylesheet" href="../css/inscription.css">
    <script src="../js/audio.js"></script>



</head>

<body>
<?php include 'header.php'; ?>
<main class="page-content">

<div class="box">

    <h2>Nouveau Sujet</h2>

    <?php if (!empty($error)): ?>

        <div class="error">
            <?= htmlspecialchars($error) ?>
        </div>

    <?php endif; ?>

    <form method="POST">

        <input
            type="text"
            name="pseudo"
            placeholder="Choisissez votre Nom"
            required
        >

        <input
            type="email"
            name="email"
            placeholder="Votre Courrier Royal"
            required
        >

        <input
            type="password"
            name="mdp"
            placeholder="Scellez votre Mot de Passe"
            required
        >

        <button type="submit">
            Graver mon Nom
        </button>

    </form>

    <a href="connexion.php" class="link">
        Déjà un compte ? Se connecter
    </a>

</div>
</main>
<?php include 'footer.php'; ?>

</body>
</html>
