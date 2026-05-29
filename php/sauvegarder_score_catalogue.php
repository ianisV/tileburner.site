<?php
header('Content-Type: application/json; charset=UTF-8');
session_start();
require 'db.php';

// Les invités ne sauvegardent pas
if (!isset($_SESSION['id'])) {
    echo json_encode(['ok' => true, 'invite' => true]);
    exit();
}

$utilisateur_id = (int)$_SESSION['id'];
$niveau_id      = (int)($_POST['niveau_id'] ?? 0);
$score          = (int)($_POST['score']     ?? 0);

if ($niveau_id < 1 || $score < 1) {
    echo json_encode(['ok' => false, 'erreur' => 'donnees_invalides']);
    exit();
}

try {
    // Vérifier que le niveau existe et n'est pas campagne
    $stmt = $bdd->prepare("SELECT id FROM niveaux WHERE id = ? AND type_niveau != 'campagne' LIMIT 1");
    $stmt->execute([$niveau_id]);
    if (!$stmt->fetch()) {
        echo json_encode(['ok' => false, 'erreur' => 'niveau_introuvable']);
        exit();
    }

    $sql = "INSERT INTO progression (utilisateur_id, niveau_id, meilleur_score, termine)
            VALUES (:uid, :nid, :score, 1)
            ON DUPLICATE KEY UPDATE
                meilleur_score = IF(meilleur_score = 0 OR :score2 < meilleur_score, :score3, meilleur_score),
                termine        = 1";

    $stmt = $bdd->prepare($sql);
    $stmt->execute([
        ':uid'    => $utilisateur_id,
        ':nid'    => $niveau_id,
        ':score'  => $score,
        ':score2' => $score,
        ':score3' => $score,
    ]);

    echo json_encode(['ok' => true]);

} catch (Exception $e) {
    echo json_encode(['ok' => false, 'erreur' => $e->getMessage()]);
}
