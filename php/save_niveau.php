<?php
header("Content-Type: application/json");
session_start();

// Seuls les joueurs connectés peuvent publier (pas les invités)
if (empty($_SESSION['id'])) {
    echo json_encode([
        "status"  => "error",
        "message" => "Vous devez être connecté pour publier un niveau."
    ]);
    exit;
}

try {
    // Connexion configurée d'après le fichier db 
    $pdo = new PDO(
        "mysql:host=localhost;dbname=tileburner;charset=utf8",
        "root",
        "root"
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}
catch (PDOException $e) {
    echo json_encode([
        "status" => "error",
        "message" => "Connexion BDD échouée : " . $e->getMessage()
    ]);
    exit;
}

// Récupération des données JS
$seed     = $_POST["seed"]     ?? null;
$largeur  = $_POST["largeur"]  ?? null;
$hauteur  = $_POST["hauteur"]  ?? null;
// type_niveau doit correspondre à l'ENUM BDD : 'aleatoire', 'custom', 'campagne'
// Le mode wasm est traité comme 'aleatoire'
$typeRaw  = $_POST["type"] ?? "wasm";
$type     = ($typeRaw === "wasm") ? "aleatoire" : $typeRaw;
$nom      = trim($_POST["nom"] ?? "");
$depart_x = (int)($_POST["depart_x"] ?? 0);
$depart_y = (int)($_POST["depart_y"] ?? 0);
$sortie_x = isset($_POST["sortie_x"]) ? (int)$_POST["sortie_x"] : ($largeur ? $largeur - 1 : 9);
$sortie_y = isset($_POST["sortie_y"]) ? (int)$_POST["sortie_y"] : ($hauteur ? $hauteur - 1 : 9);
$ordre    = 0;

// cases_speciales (concepteur) ou murs (wasm) — on unifie sous "murs"
if (!empty($_POST["cases_speciales"])) {
    $murs = $_POST["cases_speciales"];
} else {
    $murs = $_POST["murs"] ?? "[]";
}

// Nom par défaut selon le type
if ($nom === "") {
    $nom = ($typeRaw === "wasm") ? "Niveau C natif" : "Niveau personnalisé";
}

if (!$seed || !$largeur || !$hauteur) {
    echo json_encode([
        "status" => "error",
        "message" => "Données manquantes"
    ]);
    exit;
}

try {
    // Requête ajustée selon les colonnes de bd niveau 
    $stmt = $pdo->prepare("
        INSERT INTO niveaux (
            nom,
            ordre,
            largeur,
            hauteur,
            position_depart_x,
            position_depart_y,
            murs,
            sortie_x,
            sortie_y,
            type_niveau,
            seed
        )
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->execute([
        $nom,
        $ordre,
        $largeur,
        $hauteur,
        $depart_x,
        $depart_y,
        $murs,
        $sortie_x,
        $sortie_y,
        $type,
        $seed
    ]);

    echo json_encode([
        "status" => "ok",
        "id" => $pdo->lastInsertId()
    ]);

}
catch (PDOException $e) {
    echo json_encode([
        "status" => "error",
        "message" => "Erreur d'insertion SQL : " . $e->getMessage()
    ]);
}
