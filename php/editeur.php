<?php
session_start();
require 'db.php';
if (!isset($_SESSION['id']) && !isset($_SESSION['invite'])) {
    header('Location: connexion.php');
    exit();
}
$user_id = $_SESSION['id'] ?? 0;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Éditeur - TileBurner</title>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/common.css">
    <link rel="stylesheet" href="../css/editeur.css">
    <script src="../js/audio.js"></script>
</head>

<body>
<?php include 'header.php'; ?>
<main class="page-content">
    <div class="editeur-container">
        <h1>🛠️ Éditeur de niveau</h1>
        <div class="editeur-controls">
            <label>Outil :
                <select id="outil">
                    <option value="mur">Mur (#)</option>
                    <option value="depart">Départ (joueur)</option>
                    <option value="sortie">Sortie (🚪)</option>
                    <option value="effacer">Effacer</option>
                </select>
            </label>
            <button id="btn-tester">▶ Tester ce niveau</button>
            <button id="btn-reset-editeur">🗑 Réinitialiser</button>
            <?php if ($user_id > 0): ?>
            <button id="btn-sauvegarder">💾 Sauvegarder</button>
            <?php endif; ?>
        </div>
        <canvas id="editeur-canvas" width="600" height="600"></canvas>
        <p class="editeur-aide">Cliquez sur la grille pour placer des éléments selon l'outil sélectionné.</p>
        <div id="editeur-message"></div>
    </div>
</main>
<?php include 'footer.php'; ?>
<script>
// ===== ÉDITEUR DE NIVEAU =====
const TAILLE_GRILLE = 10;
const TAILLE_CASE   = 60;
const canvas  = document.getElementById('editeur-canvas');
const ctx     = canvas.getContext('2d');

// État de la grille éditeur
let grilleEdit = [];
let departX = 0, departY = 0;
let sortieX = 9, sortieY = 9;

function resetEditeur() {
    grilleEdit = [];
    for (let i = 0; i < TAILLE_GRILLE; i++) {
        grilleEdit[i] = [];
        for (let j = 0; j < TAILLE_GRILLE; j++) {
            grilleEdit[i][j] = 'eteint';
        }
    }
    departX = 0; departY = 0;
    sortieX = 9; sortieY = 9;
    dessinerEditeur();
}

function dessinerEditeur() {
    ctx.clearRect(0, 0, 600, 600);
    for (let i = 0; i < TAILLE_GRILLE; i++) {
        for (let j = 0; j < TAILLE_GRILLE; j++) {
            const px = j * TAILLE_CASE;
            const py = i * TAILLE_CASE;
            const cell = grilleEdit[i][j];

            if (cell === 'mur') {
                ctx.fillStyle = '#1a1a2e';
            } else {
                ctx.fillStyle = '#16213e';
            }
            ctx.fillRect(px, py, TAILLE_CASE, TAILLE_CASE);

            ctx.strokeStyle = '#0f0f1e';
            ctx.lineWidth = 1;
            ctx.strokeRect(px, py, TAILLE_CASE, TAILLE_CASE);
        }
    }

    // Départ
    const dpx = departY * TAILLE_CASE + TAILLE_CASE / 2;
    const dpy = departX * TAILLE_CASE + TAILLE_CASE / 2;
    ctx.fillStyle = '#4adefc';
    ctx.beginPath();
    ctx.arc(dpx, dpy, TAILLE_CASE * 0.32, 0, Math.PI * 2);
    ctx.fill();
    ctx.strokeStyle = '#fff';
    ctx.lineWidth = 2;
    ctx.stroke();

    // Sortie
    ctx.font = `${TAILLE_CASE * 0.5}px serif`;
    ctx.textAlign = 'center';
    ctx.textBaseline = 'middle';
    ctx.fillText('🚪',
        sortieY * TAILLE_CASE + TAILLE_CASE / 2,
        sortieX * TAILLE_CASE + TAILLE_CASE / 2
    );
}

canvas.addEventListener('click', (e) => {
    const rect  = canvas.getBoundingClientRect();
    const scaleX = canvas.width / rect.width;
    const scaleY = canvas.height / rect.height;
    const mx = (e.clientX - rect.left) * scaleX;
    const my = (e.clientY - rect.top)  * scaleY;
    const j  = Math.floor(mx / TAILLE_CASE);
    const i  = Math.floor(my / TAILLE_CASE);

    if (i < 0 || i >= TAILLE_GRILLE || j < 0 || j >= TAILLE_GRILLE) return;

    const outil = document.getElementById('outil').value;
    switch (outil) {
        case 'mur':
            grilleEdit[i][j] = 'mur';
            break;
        case 'depart':
            departX = i; departY = j;
            if (grilleEdit[i][j] === 'mur') grilleEdit[i][j] = 'eteint';
            break;
        case 'sortie':
            sortieX = i; sortieY = j;
            if (grilleEdit[i][j] === 'mur') grilleEdit[i][j] = 'eteint';
            break;
        case 'effacer':
            grilleEdit[i][j] = 'eteint';
            break;
    }
    dessinerEditeur();
});

document.getElementById('btn-tester').addEventListener('click', () => {
    const murs = [];
    for (let i = 0; i < TAILLE_GRILLE; i++) {
        for (let j = 0; j < TAILLE_GRILLE; j++) {
            if (grilleEdit[i][j] === 'mur') murs.push([i, j]);
        }
    }
    const config = {
        mode: 'custom', largeur: 10, hauteur: 10,
        depart_x: departX, depart_y: departY,
        sortie_x: sortieX, sortie_y: sortieY,
        murs
    };
    const encoded = btoa(JSON.stringify(config));
    window.location.href = 'jeu.php?mode=custom&config=' + encoded;
});

document.getElementById('btn-reset-editeur').addEventListener('click', resetEditeur);

<?php if ($user_id > 0): ?>
document.getElementById('btn-sauvegarder').addEventListener('click', async () => {
    const murs = [];
    for (let i = 0; i < TAILLE_GRILLE; i++)
        for (let j = 0; j < TAILLE_GRILLE; j++)
            if (grilleEdit[i][j] === 'mur') murs.push([i, j]);

    const nom = prompt('Nom de votre niveau :', 'Mon niveau');
    if (!nom) return;

    const res = await fetch('../php/sauvegarder_niveau.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            nom,
            depart_x: departX, depart_y: departY,
            sortie_x: sortieX, sortie_y: sortieY,
            murs
        })
    });
    const data = await res.json();
    document.getElementById('editeur-message').textContent =
        data.success ? '✅ Niveau sauvegardé !' : '❌ Erreur : ' + data.error;
});
<?php endif; ?>

// Initialisation
resetEditeur();
</script>
</body>
</html>
