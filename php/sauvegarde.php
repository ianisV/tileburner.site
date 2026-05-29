<?php
session_start();
require 'db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['id'])) {
    echo json_encode(['error' => 'Non connecté']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$niveau_id = (int)($data['niveau_id'] ?? 0);
$pos_x     = (int)($data['pos_x'] ?? 0);
$pos_y     = (int)($data['pos_y'] ?? 0);
$score     = (int)($data['score'] ?? 0);
$termine   = !empty($data['termine']) ? 1 : 0;

$req = $bdd->prepare("
    INSERT INTO progression (utilisateur_id, niveau_id, position_actuelle_x, position_actuelle_y, meilleur_score, termine)
    VALUES (?, ?, ?, ?, ?, ?)
    ON DUPLICATE KEY UPDATE 
        position_actuelle_x = VALUES(position_actuelle_x),
        position_actuelle_y = VALUES(position_actuelle_y),
        meilleur_score = CASE
            WHEN VALUES(termine) = 1 AND (meilleur_score = 0 OR VALUES(meilleur_score) < meilleur_score OR termine = 0)
            THEN VALUES(meilleur_score)
            ELSE meilleur_score
        END,
        termine = VALUES(termine) OR termine
");
$req->execute([$_SESSION['id'], $niveau_id, $pos_x, $pos_y, $score, $termine]);

// Mise à jour du score total
if ($score > 0) {
    $bdd->prepare("UPDATE utilisateurs SET score_total = score_total + ? WHERE id = ?")
        ->execute([$score, $_SESSION['id']]);
}

echo json_encode(['success' => true]);
