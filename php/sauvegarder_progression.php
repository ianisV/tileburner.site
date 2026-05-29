<?php
header('Content-Type: application/json; charset=UTF-8');
session_start();
require 'db.php';

if (!isset($_SESSION['id'])) {
    echo json_encode(['ok' => true, 'invite' => true]);
    exit();
}

$utilisateur_id = (int)$_SESSION['id'];
$niveau_id      = (int)($_POST['niveau_id'] ?? 0);
$score          = (int)($_POST['score']     ?? 0);
$termine        = !empty($_POST['termine']) ? 1 : 0;


if ($niveau_id < 1 || $niveau_id > 20) {
    echo json_encode(['ok' => false, 'erreur' => 'niveau_invalide']);
    exit();
}

try {
   
    $stmt = $bdd->prepare("SELECT id FROM niveaux WHERE type_niveau = 'campagne' AND ordre = ? LIMIT 1");
    $stmt->execute([$niveau_id]);
    $niv = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$niv) {
        echo json_encode(['ok' => false, 'erreur' => 'niveau_introuvable']);
        exit();
    }

    $niv_bdd_id = (int)$niv['id'];

    // 1. Sauvegarde dans la table progression
    $sql = "INSERT INTO progression (utilisateur_id, niveau_id, meilleur_score, termine)
            VALUES (:uid, :nid, :score, :term)
            ON DUPLICATE KEY UPDATE
                meilleur_score = LEAST(meilleur_score, VALUES(meilleur_score)),
                termine        = GREATEST(termine, VALUES(termine))";

    $stmt = $bdd->prepare($sql);
    $stmt->execute([
        ':uid'   => $utilisateur_id,
        ':nid'   => $niv_bdd_id,
        ':score' => $score,
        ':term'  => $termine,
    ]);

    
    $sqlPoints = "UPDATE utilisateurs SET points = points + :score GAGNÉ WHERE id = :uid";
    $stmtPoints = $bdd->prepare("UPDATE utilisateurs SET points = points + ? WHERE id = ?");
    $stmtPoints->execute([$score, $utilisateur_id]);

    echo json_encode(['ok' => true]);

} catch (Exception $e) {
    echo json_encode(['ok' => false, 'erreur' => $e->getMessage()]);
}
