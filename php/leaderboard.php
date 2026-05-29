<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Tile Burner - Classement</title>
    <link rel="stylesheet" href="../css/common.css">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/leaderboard.css">
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;700;900&family=Cinzel+Decorative:wght@700&display=swap" rel="stylesheet">
    <script src="../js/audio.js" defer></script>
    <script src="../js/skin-secret.js" defer></script>
</head>
<body>

<?php include 'header.php'; ?>

<?php
// =========================================================
// GÉNÉRATION ALÉATOIRE DES CLASSEMENTS POUR LES 20 NIVEAUX
// =========================================================

// Seed pour avoir des résultats stables (change à chaque rechargement de page si tu veux)
// Si tu veux des scores fixes par niveau, décommente la ligne suivante :
// mt_srand(42);

$prenoms = [
    "Panenka", "Pignouf59", "Poulet", "Simba", "ForSure", "Mathis",
    "Clio2", "Redoublant", "Tintin", "Halouf", "Drakon", "Eryndor",
    "Lyssandra", "Korgath", "Mystik", "Valdor", "Shadowfax", "BraiseNoire",
    "FeuFollet", "Cendrelune", "Pyrrhus", "Ignis", "Ashen", "Vulkan",
    "Brasier", "Étincelle", "Phénix", "Salamandre", "Brûlot", "Charbon"
];

$suffixes = [
    " le Brûlant", " l'Ancien", " de la Flamme", " du Chaos", " l'Agile",
    " le Pyromane", " des Cendres", " l'Indomptable", " le Vaillant",
    " du Bûcher", " l'Embrasé", " le Tisonnier", " l'Ardent", " le Fougueux"
];

$titres_niveaux = [
    1 => "Premiers pas", 2 => "Un mur en travers", 3 => "Couloir étroit",
    4 => "Dédale de murs", 5 => "Glissade", 6 => "Brûlure rapide",
    7 => "Le labyrinthe", 8 => "Échiquier", 9 => "Téléporteurs",
    10 => "Sol qui s'effondre", 11 => "Piège glacé", 12 => "Double portail",
    13 => "Mémoire courte", 14 => "Tempête de glace", 15 => "Croisement maudit",
    16 => "Fragments d'âme", 17 => "Le grand vide", 18 => "Pièges mortels",
    19 => "Téléport infernal", 20 => "Maître du feu"
];

// Fonction pour générer un classement pour un niveau donné
function genererClassement($niveau, $prenoms, $suffixes) {
    mt_srand($niveau * 1337);

    $joueurs = [];
    $noms_utilises = [];

    for ($i = 0; $i < 10; $i++) {
        do {
            $nom = $prenoms[array_rand($prenoms)] . $suffixes[array_rand($suffixes)];
        } while (in_array($nom, $noms_utilises));
        $noms_utilises[] = $nom;

        // Score : plus le niveau est dur, plus le score minimum monte
        // (car il faut plus de coups pour finir)
        $score_min = 10 + ($niveau * 2);
        $score = rand($score_min, $score_min + 30);

        $joueurs[] = [
            'nom' => $nom,
            'score' => $score
        ];
    }

    // 🔥 TRI CROISSANT : le plus petit score gagne
    usort($joueurs, fn($a, $b) => $a['score'] <=> $b['score']);

    return $joueurs;
}


$current_lvl = isset($_GET['lvl']) ? max(1, min(20, (int)$_GET['lvl'])) : 1;
$joueurs = genererClassement($current_lvl, $prenoms, $suffixes);
$titre_niveau = $titres_niveaux[$current_lvl] ?? "Niveau inconnu";
?>

<div class="main-content">
    <h1 class="menu-titre">Classement Mondial</h1>
    <p class="sous-titre">Niveau <?= $current_lvl ?> — <?= htmlspecialchars($titre_niveau) ?></p>

    <!-- ONGLETS DE NIVEAUX (1 à 20) -->
    <div class="tabs-levels">
        <?php for ($i = 1; $i <= 20; $i++): ?>
            <a href="?lvl=<?= $i ?>" class="tab-btn <?= $current_lvl == $i ? 'active' : '' ?>">
                <?= $i ?>
            </a>
        <?php endfor; ?>
    </div>

    <!-- TABLEAU DU CLASSEMENT -->
    <div class="menu-section">
        <h2 class="section-titre"><span></span> Top 10 des Brûleurs</h2>

        <div class="leaderboard-container">
            <table class="leaderboard-table">
                <thead>
                    <tr>
                        <th class="rank-col">RANG</th>
                        <th class="avatar-col">AVATAR</th>
                        <th class="name-col">GUERRIER</th>
                        <th class="score-col">SCORE</th>
                    </tr>
                </thead>
                <tbody>
<?php foreach ($joueurs as $index => $joueur):
    $rang = $index + 1;
    $class_top = ($rang <= 3) ? "top-$rang" : "";
    $medaille = match($rang) {
        1 => "🥇",
        2 => "🥈",
        3 => "🥉",
        default => "#$rang"
    };
?>
    <tr class="<?= $class_top ?>">
        <td class="rank-col"><?= $medaille ?></td>
        <td class="avatar-col">
            <div class="mini-avatar avatar-<?= $rang ?>"></div>
        </td>
        <td class="name-col"><?= htmlspecialchars($joueur['nom']) ?></td>
        <td class="score-col"><?= $joueur['score'] ?></td>
    </tr>
<?php endforeach; ?>

                </tbody>
            </table>
        </div>

        <div class="footer-actions">
            <a href="menu.php" class="btn-back">⬅ Retour au menu</a>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
</body>
</html>
