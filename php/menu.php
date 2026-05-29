<?php
session_start();
require 'db.php';

$user_id = $_SESSION['id'] ?? 0;
$progressions = [];

if ($user_id > 0) {
    try {
        $req = $bdd->prepare("
            SELECT n.ordre, p.termine, p.meilleur_score
            FROM progression p
            JOIN niveaux n ON n.id = p.niveau_id
            WHERE p.utilisateur_id = ?
              AND n.type_niveau = 'campagne'
        ");
        $req->execute([$user_id]);
        foreach ($req->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $progressions[(int)$row['ordre']] = $row;
        }
    } catch (PDOException $e) {}
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Menu — TileBurner</title>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;700;900&family=Cinzel+Decorative:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/common.css">
    <link rel="stylesheet" href="../css/menu.css">
    <script src="../js/audio.js" defer></script>
    <script>window.PROGRESSION_USER_ID = <?= json_encode((string)($user_id ?: 'invite')) ?>;</script>
    <script src="../js/niveaux-campagne.js" defer></script>
    <script src="../js/progression.js" defer></script>
</head>

<body>
<?php include 'header.php'; ?>

<main class="page-content">
    <div class="menu-container">

        <h1 class="menu-titre">TileBurner</h1>
        <p class="menu-soustitre">Embrasez chaque tuile et conquérez le donjon</p>

        <div class="actions-rapides">
            <a href="style_catalogue.php" class="btn-action">
                <span class="btn-icon"></span>
                <span class="btn-label">Catalogue des Niveaux</span>
            </a>
            <a href="jeu.php?mode=wasm" class="btn-action">
                <span class="btn-icon">⚙</span>
                <span class="btn-label">Niveau Aléatoire (WASM)</span>
            </a>
            <a href="concepteur.php" class="btn-action">
                <span class="btn-icon">🛠</span>
                <span class="btn-label">Concepteur de niveaux</span>
            </a>
        </div>

        <div class="ornement">
            <span class="ornement-symbol">✦</span>
        </div>

        <!-- CAMPAGNE -->
        <section class="menu-section">
            <h2>Campagne</h2>
            <p class="menu-soustitre">Choisissez un niveau et embrasez la grille</p>
            <div class="grille-niveaux" id="grille-niveaux">
                <!-- Rempli dynamiquement en JS -->
            </div>
        </section>

        <div class="ornement">
            <span class="ornement-symbol">✦</span>
        </div>

        <!-- AUTRES MODES -->
        <div class="menu-modes-grid">

            <section class="menu-section">
                <h2>Skins & Apparences</h2>
                <p>Échangez vos scores durement gagnés contre des cosmétiques légendaires.</p>
                <a href="boutique.php" class="btn-menu">Visiter la Boutique</a>
            </section>

        </div>

    </div>
</main>

<script>
    const progressionsPHP = <?php echo json_encode($progressions, JSON_UNESCAPED_UNICODE); ?>;

    function escapeHtml(s) {
        return String(s ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    document.addEventListener('DOMContentLoaded', () => {
        const conteneur = document.getElementById('grille-niveaux');
        if (!conteneur || !window.NIVEAUX_CAMPAGNE) return;

        const niveaux = window.NIVEAUX_CAMPAGNE;

        function estTermine(niveauId) {
            if (progressionsPHP[niveauId]?.termine) return true;
            if (window.Progression?.estTermine?.(niveauId)) return true;
            return false;
        }

        function meilleurScore(niveauId) {
            const scoreBdd = progressionsPHP[niveauId]
                ? parseInt(progressionsPHP[niveauId].meilleur_score) : null;
            const scoreLs = window.Progression?.meilleurScore?.(niveauId) ?? null;

            if (scoreBdd && scoreLs) return Math.min(scoreBdd, scoreLs);
            return scoreBdd || scoreLs || null;
        }

        const couleursDiff = {
            'Facile':    '#4caf50',
            'Moyen':     '#ff9800',
            'Difficile': '#f44336',
            'Expert':    '#9c27b0',
        };

        niveaux.forEach((n, index) => {
            const debloque = index === 0
                || estTermine(niveaux[index - 1].id)
                || estTermine(n.id); // déjà terminé = forcément accessible
            const termine  = estTermine(n.id);
            const score    = meilleurScore(n.id);
            const couleur  = couleursDiff[n.difficulte] || '#888';

            const carte = document.createElement('div');
            carte.className = 'carte-niveau'
                + (debloque ? '' : ' verrouille')
                + (termine  ? ' termine' : '');

            carte.innerHTML = `
                <div class="numero-niveau">N° ${n.id}</div>
                <h3 class="nom-niveau">${escapeHtml(n.nom)}</h3>
                <p class="desc-niveau">${escapeHtml(n.description || '')}</p>
                <div class="bas-niveau">
                    <span class="difficulte" style="background:${couleur}">
                        ${escapeHtml(n.difficulte || '')}
                    </span>
                    ${termine
                        ? `<span class="badge-termine">✓ Réussi${score ? ' — ' + score + ' coups' : ''}</span>`
                        : ''}
                </div>
                ${debloque
                    ? `<a href="jeu.php?mode=campagne&niveau_id=${n.id}" class="btn-jouer">▶ Jouer</a>`
                    : `<div class="btn-verrouille">🔒 Verrouillé</div>`}
            `;

            conteneur.appendChild(carte);
        });
    });
</script>
<?php include 'footer.php'; ?>

</body>
</html>
