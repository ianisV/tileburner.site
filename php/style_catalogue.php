<?php
session_start();
require 'db.php';

if (!isset($_SESSION['id']) && !isset($_SESSION['guest']) && !isset($_SESSION['invite'])) {
    header('Location: connexion.php');
    exit();
}

$user_id = $_SESSION['id'] ?? 0;

try {
    $stmt = $bdd->query("
        SELECT n.*,
               MIN(NULLIF(p.meilleur_score, 0)) AS meilleur_score_global
        FROM niveaux n
        LEFT JOIN progression p ON p.niveau_id = n.id
        WHERE n.type_niveau != 'campagne'
        GROUP BY n.id
        ORDER BY n.id ASC
    ");
    $niveaux = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erreur de récupération des niveaux : " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Liste des Niveaux - Tile Burner</title>
    <link rel="stylesheet" href="../css/common.css">
    <link rel="stylesheet" href="../css/style_catalogue.css">
</head>
<body>
    <?php include 'header.php'; ?>

    <h1> Catalogue des Labyrinthes</h1>
    <p style="text-align: center; color: #aaa;">Retrouvez ici tous les niveaux officiels et les générations de la communauté.</p>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nom du Niveau</th>
                <th>Taille</th>
                <th>Type</th>
                <th>Graine (Seed)</th>
                <th>Meilleur Score</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($niveaux)): ?>
                <tr>
                    <td colspan="7" style="text-align: center; color: #aaa;">Aucun niveau disponible pour le moment.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($niveaux as $n): ?>
                    <tr>
                        <td><strong>#<?php echo $n['id']; ?></strong></td>
                        <td><?php echo htmlspecialchars($n['nom']); ?></td>
                        <td><?php echo $n['largeur'] . 'x' . $n['hauteur']; ?></td>
                        <td>
                            <span class="badge badge-<?php echo $n['type_niveau']; ?>">
                                <?php echo ucfirst($n['type_niveau']); ?>
                            </span>
                        </td>
                        <td class="td-seed" style="font-family: monospace; color: #bbb; font-size:.8rem;">
                            <?php
                                $seed = $n['seed'] ?? '';
                                if (!$seed) {
                                    echo '—';
                                } elseif (is_numeric($seed) && strlen($seed) >= 10) {
                                    // Ancien timestamp : afficher —
                                    echo '—';
                                } else {
                                    $apercu = strlen($seed) > 12 ? substr($seed, 0, 12) . '…' : $seed;
                                    $seedEsc = htmlspecialchars($seed, ENT_QUOTES);
                                    echo htmlspecialchars($apercu);
                                    echo ' <button onclick="copierSeed(\'' . $seedEsc . '\')" '
                                         . 'title="Copier la seed complète" '
                                         . 'style="background:none;border:none;cursor:pointer;'
                                         . 'color:#d4af37;font-size:.9rem;padding:0 2px;vertical-align:middle;">'
                                         . '📋</button>';
                                }
                            ?>
                        </td>
                        <td style="text-align:center; color:#d4af37; font-family:'Cinzel',serif; font-size:.85rem;">
                            <?php echo $n['meilleur_score_global'] ? $n['meilleur_score_global'] . ' 🚶' : '—'; ?>
                        </td>
                        <td>
                            <a href="jeu.php?mode=<?php echo $n['type_niveau']; ?>&niveau_id=<?php echo $n['id']; ?>" class="btn-jouer">Jouer</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
<?php include 'footer.php'; ?>

<div id="toast-seed" style="
    display:none; position:fixed; bottom:2rem; left:50%; transform:translateX(-50%);
    background:#1a1208; border:1px solid rgba(212,175,55,0.6); color:#d4af37;
    font-family:'Cinzel',serif; font-size:.78rem; letter-spacing:1px;
    padding:.6rem 1.4rem; border-radius:6px; z-index:9999;
    box-shadow:0 4px 20px rgba(0,0,0,0.7);">Seed copiée !</div>

<script>
function copierSeed(seed) {
    navigator.clipboard.writeText(seed).then(function() {
        var t = document.getElementById('toast-seed');
        t.style.display = 'block';
        setTimeout(function() { t.style.display = 'none'; }, 2000);
    }).catch(function() {
        // Fallback si clipboard API indisponible
        prompt('Copiez la seed :', seed);
    });
}
</script>
</body>
</html>