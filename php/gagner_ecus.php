<?php
header('Content-Type: application/json; charset=UTF-8');
session_start();
require 'db.php';

// Les invités ne gagnent pas d'écus
if (!isset($_SESSION['id'])) {
    echo json_encode(['ok' => true, 'invite' => true, 'ecus' => 0]);
    exit();
}

$utilisateur_id = (int)$_SESSION['id'];
$mode           = $_POST['mode']          ?? '';
$score_joueur   = (int)($_POST['score']   ?? 0);
$score_optimal  = (int)($_POST['optimal'] ?? 0);
$difficulte     = $_POST['difficulte']    ?? '';
$niveau_id      = (int)($_POST['niveau_id'] ?? 0);
$solveur_utilise = !empty($_POST['solveur_utilise']);

// ─── Calcul des écus ───────────────────────────────────────────

$ecus_gagnes = 0;

if ($mode === 'campagne') {

    // Vérifier que ce niveau n'a pas déjà été récompensé
    if ($niveau_id > 0) {
        $stmt = $bdd->prepare("
            SELECT termine FROM progression
            WHERE utilisateur_id = ? AND niveau_id = (
                SELECT id FROM niveaux WHERE type_niveau = 'campagne' AND ordre = ? LIMIT 1
            )
        ");
        $stmt->execute([$utilisateur_id, $niveau_id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        // Si déjà terminé avant cette victoire → pas d'écus
        if ($row && (int)$row['termine'] === 1) {
            echo json_encode(['ok' => true, 'ecus' => 0, 'deja_termine' => true]);
            exit();
        }
    }

    $grille_difficulte = [
        'Facile'    => 5,
        'Moyen'     => 15,
        'Difficile' => 30,
        'Expert'    => 50,
    ];
    $ecus_gagnes = $grille_difficulte[$difficulte] ?? 5;

} elseif ($mode === 'wasm') {

    // Si le solveur a été utilisé → 0 écus
    if ($solveur_utilise) {
        echo json_encode(['ok' => true, 'ecus' => 0, 'solveur' => true]);
        exit();
    }

    $max_ecus = 50;
    // Chaque coup au-delà du score optimal retire 1 écu
    $ecus_gagnes = max(0, $max_ecus - max(0, $score_joueur - $score_optimal));

}

// ─── Créditer le joueur ────────────────────────────────────────

if ($ecus_gagnes > 0) {
    try {
        $stmt = $bdd->prepare("UPDATE utilisateurs SET points = points + ? WHERE id = ?");
        $stmt->execute([$ecus_gagnes, $utilisateur_id]);
    } catch (Exception $e) {
        echo json_encode(['ok' => false, 'erreur' => $e->getMessage()]);
        exit();
    }
}

echo json_encode([
    'ok'   => true,
    'ecus' => $ecus_gagnes,
]);
