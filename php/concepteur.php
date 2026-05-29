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
    <title>Concepteur — TileBurner</title>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/common.css">
    <link rel="stylesheet" href="../css/concepteur.css">
    <script src="../js/audio.js" defer></script>
    <script src="../js/skin-secret.js" defer></script>
    <script src="../js/tile-burner-campagne.js" defer></script>
</head>
<body>
<?php include 'header.php'; ?>

<main class="page-content">
<div class="concepteur-wrapper">

    <!-- ══════════════════════════════════════
         PANNEAU GAUCHE : outils + config
         ══════════════════════════════════════ -->
    <aside class="concepteur-sidebar" id="sidebar-edit">

        <h2 class="sidebar-titre">⚒ Concepteur</h2>

        <!-- Dimensions -->
        <section class="sidebar-section">
            <h3 class="sidebar-section-titre">Grille</h3>
            <div class="dim-row">
                <label>Largeur
                    <input type="number" id="inp-largeur" min="3" max="12" value="8">
                </label>
                <label>Hauteur
                    <input type="number" id="inp-hauteur" min="3" max="12" value="8">
                </label>
                <button class="tool-btn-action" id="btn-appliquer-dim">Appliquer</button>
            </div>
        </section>

        <!-- Palette d'outils -->
        <section class="sidebar-section">
            <h3 class="sidebar-section-titre">Palette</h3>
            <div class="palette-grille">
                <button class="palette-btn actif" data-outil="effacer"    title="Effacer">⬜ Case</button>
                <button class="palette-btn"        data-outil="mur"       title="Mur infranchissable">🧱 Mur</button>
                <button class="palette-btn"        data-outil="glace"     title="Glissade">🧊 Glace</button>
                <button class="palette-btn"        data-outil="fragile"   title="Sol qui s'effondre">💔 Fragile</button>
                <button class="palette-btn"        data-outil="piege"     title="Piège rythmique">⚠️ Piège</button>
                <button class="palette-btn"        data-outil="teleport_a" title="Portail A">🔵 Portail A</button>
                <button class="palette-btn"        data-outil="teleport_b" title="Portail B">🟣 Portail B</button>
                <button class="palette-btn"        data-outil="maudite"   title="Case maudite">💀 Maudite</button>
                <button class="palette-btn palette-depart" data-outil="depart" title="Point de départ du joueur">🧍 Départ</button>
                <button class="palette-btn palette-sortie" data-outil="sortie" title="Sortie du niveau">🚪 Sortie</button>
            </div>
        </section>

        <!-- Légende rapide -->
        <section class="sidebar-section sidebar-legende">
            <h3 class="sidebar-section-titre">Légende</h3>
            <ul class="legende-liste">
                <li><span class="leg-puce leg-case"></span> Case normale (allumable)</li>
                <li><span class="leg-puce leg-mur"></span> Mur infranchissable</li>
                <li><span class="leg-puce leg-glace"></span> Glace (glissade)</li>
                <li><span class="leg-puce leg-fragile"></span> Fragile (devient trou)</li>
                <li><span class="leg-puce leg-piege"></span> Piège (tous les 5 pas)</li>
                <li><span class="leg-puce leg-teleport-a"></span> Portail A</li>
                <li><span class="leg-puce leg-teleport-b"></span> Portail B</li>
                <li><span class="leg-puce leg-maudite"></span> Maudite (s'éteint seule)</li>
            </ul>
        </section>

        <!-- Actions éditeur -->
        <section class="sidebar-section">
            <h3 class="sidebar-section-titre">Actions</h3>
            <div class="actions-col">
                <button class="tool-btn-action btn-tester" id="btn-tester">▶ Tester le niveau</button>
                <button class="tool-btn-action btn-reset"  id="btn-reset">🗑 Réinitialiser</button>
            </div>
        </section>

    </aside>

    <!-- ══════════════════════════════════════
         CENTRE : canvas (éditeur ou jeu)
         ══════════════════════════════════════ -->
    <div class="concepteur-centre">

        <!-- Mode éditeur -->
        <div id="zone-editeur">
            <div class="canvas-label" id="label-editeur">MODE ÉDITION — Cliquez / Glissez pour placer</div>
            <canvas id="editeur-canvas"></canvas>
        </div>

        <!-- Mode test (jeu intégré) -->
        <div id="zone-jeu" style="display:none">
            <div class="canvas-label" id="label-jeu">MODE TEST — Résolvez votre niveau !</div>

            <div class="hud-test">
                <span id="hud-depl">Déplacements : <strong>0</strong></span>
                <span id="hud-cases">Cases : <strong>0</strong> / <strong>0</strong></span>
                <button class="tool-btn-small" id="btn-restart-test">↺ Recommencer</button>
                <button class="tool-btn-small" id="btn-retour-edit">✏ Retour édition</button>
            </div>

            <canvas id="jeu-canvas"></canvas>

            <div id="test-message"></div>
        </div>

    </div>

    <!-- ══════════════════════════════════════
         PANNEAU DROIT : résultat + seed
         ══════════════════════════════════════ -->
    <aside class="concepteur-sidebar sidebar-droite" id="sidebar-result">

        <h2 class="sidebar-titre">📜 Résultat</h2>

        <!-- Statut -->
        <section class="sidebar-section">
            <div id="statut-container">
                <div class="statut-idle" id="statut-idle">
                    <div class="statut-icone">⚒</div>
                    <p>Construisez votre niveau,<br>puis testez-le.</p>
                    <p class="statut-sub">La seed ne sera générée<br>qu'après victoire.</p>
                </div>
                <div class="statut-test" id="statut-test" style="display:none">
                    <div class="statut-icone anim-pulse">⚔</div>
                    <p>En cours de test…</p>
                    <p class="statut-sub">Résolvez le niveau<br>pour obtenir votre seed !</p>
                </div>
                <div class="statut-victoire" id="statut-victoire" style="display:none">
                    <div class="statut-icone">🏆</div>
                    <p class="victoire-titre">Niveau résolu !</p>
                    <p class="statut-sub" id="victoire-depl"></p>
                </div>
            </div>
        </section>

        <!-- Seed (visible après victoire) -->
        <section class="sidebar-section seed-section" id="seed-section" style="display:none">
            <h3 class="sidebar-section-titre">Votre Seed</h3>
            <div class="seed-box">
                <code id="seed-valeur">—</code>
                <button class="btn-copier" id="btn-copier" title="Copier la seed">📋</button>
            </div>
            <p class="seed-aide">Partagez cette seed avec vos amis !</p>

            <?php if ($user_id > 0): ?>
            <div class="seed-actions">
                <input type="text" id="inp-nom-niveau" placeholder="Nom du niveau…" maxlength="60">
                <button class="tool-btn-action" id="btn-publier">🌐 Publier</button>
            </div>
            <div id="msg-publication"></div>
            <?php else: ?>
            <p class="seed-aide" style="color:var(--gold)">
                <a href="connexion.php" style="color:var(--gold)">Connectez-vous</a> pour publier votre niveau.
            </p>
            <?php endif; ?>

            <button class="tool-btn-action btn-rejouer" id="btn-rejouer">↺ Recréer un niveau</button>
        </section>

    </aside>

</div><!-- .concepteur-wrapper -->
</main>

<?php include 'footer.php'; ?>

<!-- ════════════════════════════════════════════════════
     SCRIPT PRINCIPAL
     ════════════════════════════════════════════════════ -->
<script>
(function () {
'use strict';

// ── Constantes ──────────────────────────────────────────────────────────────
const COULEURS_CASES = {
    eteint:     '#2a1a0d',
    mur:        '#1a1a2e',
    glace:      '#a8d8ea',
    fragile:    '#d4a84b',
    piege:      '#8b0000',
    teleport_a: '#1e40af',
    teleport_b: '#7c3aed',
    maudite:    '#4a0080',
    sortie:     '#1a5c2a',
    depart:     '#0f766e',
};

const LABELS_CASES = {
    eteint:     '',
    mur:        '🧱',
    glace:      '🧊',
    fragile:    '💔',
    piege:      '⚠',
    teleport_a: '🔵',
    teleport_b: '🟣',
    maudite:    '💀',
    sortie:     '🚪',
    depart:     '🧍',
};

// ── État éditeur ─────────────────────────────────────────────────────────────
let largeur  = 8, hauteur = 8;
let grille   = [];          // grille[y][x] = string type
let departX  = 0, departY  = 0;
let sortieX  = largeur - 1, sortieY = hauteur - 1;
let outilActif = 'effacer';
let enTrainDeDessiner = false;
// ── Images (mêmes assets que le jeu) ────────────────────────────────────────
const ASSETS_SRC = {
    eteint:     "../img/asset/case_eteinte.png",
    mur:        "../img/asset/mur1.png",
    sortie:     "../img/asset/sortie.png",
    glace:      "../img/asset/glace.png",
    fragile:    "../img/asset/fragile.png",
    piege:      "../img/asset/piege_off.png",
    teleport_a: "../img/asset/teleport_a.png",
    teleport_b: "../img/asset/teleport_b.png",
    maudite:    "../img/asset/maudite_off.png",
    depart:     "../img/asset/joueur_bas.png",
};
const assets = {};
for (const [cle, src] of Object.entries(ASSETS_SRC)) {
    const img = new Image();
    img.onload  = () => dessinerEditeur();
    img.onerror = () => console.warn('Image manquante :', src);
    img.src = src;
    assets[cle] = img;
}
function imgOK(img) { return img && img.complete && img.naturalWidth > 0; }

// ── Éléments DOM ─────────────────────────────────────────────────────────────
const canvasEdit   = document.getElementById('editeur-canvas');
const ctxEdit      = canvasEdit.getContext('2d');
const canvasJeu    = document.getElementById('jeu-canvas');
const zoneEditeur  = document.getElementById('zone-editeur');
const zoneJeu      = document.getElementById('zone-jeu');
const hudDepl      = document.querySelector('#hud-depl strong');
const hudCases     = document.querySelector('#hud-cases');
const msgTest      = document.getElementById('test-message');
const seedSection  = document.getElementById('seed-section');
const seedValeur   = document.getElementById('seed-valeur');
const statutIdle   = document.getElementById('statut-idle');
const statutTest   = document.getElementById('statut-test');
const statutVictoire = document.getElementById('statut-victoire');

let partie = null;
let seedGeneree = null;

// ════════════════════════════════════════
// ÉDITEUR
// ════════════════════════════════════════

function tailleCaseEdit() {
    const maxPx = Math.min(window.innerWidth < 900 ? 340 : 560, 560);
    return Math.floor(maxPx / Math.max(largeur, hauteur));
}

function resetGrille() {
    largeur  = parseInt(document.getElementById('inp-largeur').value) || 8;
    hauteur  = parseInt(document.getElementById('inp-hauteur').value) || 8;
    largeur  = Math.max(3, Math.min(12, largeur));
    hauteur  = Math.max(3, Math.min(12, hauteur));
    departX  = 0; departY  = 0;
    sortieX  = largeur - 1; sortieY = hauteur - 1;
    grille   = [];
    for (let y = 0; y < hauteur; y++) {
        grille[y] = [];
        for (let x = 0; x < largeur; x++) grille[y][x] = 'eteint';
    }
    dessinerEditeur();
}

function dessinerEditeur() {
    const tc = tailleCaseEdit();
    canvasEdit.width  = tc * largeur;
    canvasEdit.height = tc * hauteur;

    ctxEdit.clearRect(0, 0, canvasEdit.width, canvasEdit.height);

    for (let y = 0; y < hauteur; y++) {
        for (let x = 0; x < largeur; x++) {
            const px = x * tc, py = y * tc;
            const type = grille[y][x];

            // Fond couleur (toujours, garantit qu'on voit qqch)
            ctxEdit.fillStyle = COULEURS_CASES[type] || COULEURS_CASES.eteint;
            ctxEdit.fillRect(px, py, tc, tc);

            // Image par-dessus si dispo
            // Pour les cases vides "eteint" on dessine la texture case_eteinte
            const cleImg = (type === 'eteint') ? 'eteint' : type;
            if (imgOK(assets[cleImg])) {
                ctxEdit.drawImage(assets[cleImg], px, py, tc, tc);
            } else if (LABELS_CASES[type]) {
                // Fallback emoji si image absente
                ctxEdit.font = `${Math.floor(tc * 0.5)}px serif`;
                ctxEdit.textAlign = 'center';
                ctxEdit.textBaseline = 'middle';
                ctxEdit.fillText(LABELS_CASES[type], px + tc / 2, py + tc / 2);
            }

            // Grille dorée
            ctxEdit.strokeStyle = 'rgba(212,175,55,0.18)';
            ctxEdit.lineWidth = 1;
            ctxEdit.strokeRect(px + 0.5, py + 0.5, tc - 1, tc - 1);
        }
    }

    // Sortie (si pas dans la grille)
    if (grille[sortieY][sortieX] !== 'sortie') {
        const sx = sortieX * tc, sy = sortieY * tc;
        if (imgOK(assets.sortie)) {
            ctxEdit.drawImage(assets.sortie, sx, sy, tc, tc);
        } else {
            ctxEdit.font = `${Math.floor(tc * 0.5)}px serif`;
            ctxEdit.textAlign = 'center';
            ctxEdit.textBaseline = 'middle';
            ctxEdit.fillText('🚪', sx + tc / 2, sy + tc / 2);
        }
    }

    // Départ (par-dessus tout)
    const dpx = departX * tc, dpy = departY * tc;
    if (imgOK(assets.depart)) {
        ctxEdit.drawImage(assets.depart, dpx, dpy, tc, tc);
    } else {
        const cx = dpx + tc / 2, cy = dpy + tc / 2;
        ctxEdit.fillStyle = '#34d399';
        ctxEdit.beginPath();
        ctxEdit.arc(cx, cy, tc * 0.3, 0, Math.PI * 2);
        ctxEdit.fill();
        ctxEdit.strokeStyle = '#fff';
        ctxEdit.lineWidth = 2;
        ctxEdit.stroke();
        ctxEdit.fillStyle = '#fff';
        ctxEdit.font = `bold ${Math.floor(tc * 0.32)}px Cinzel, serif`;
        ctxEdit.textAlign = 'center';
        ctxEdit.textBaseline = 'middle';
        ctxEdit.fillText('D', cx, cy);
    }
}

function celluleDepuisEvenement(e) {
    const rect   = canvasEdit.getBoundingClientRect();
    const scaleX = canvasEdit.width  / rect.width;
    const scaleY = canvasEdit.height / rect.height;
    const clientX = e.touches ? e.touches[0].clientX : e.clientX;
    const clientY = e.touches ? e.touches[0].clientY : e.clientY;
    const mx = (clientX - rect.left)  * scaleX;
    const my = (clientY - rect.top)   * scaleY;
    const tc = tailleCaseEdit();
    return {
        x: Math.floor(mx / tc),
        y: Math.floor(my / tc)
    };
}

function appliquerOutil(x, y) {
    if (x < 0 || x >= largeur || y < 0 || y >= hauteur) return;

    switch (outilActif) {
        case 'depart':
            departX = x; departY = y;
            grille[y][x] = 'eteint';
            break;
        case 'sortie':
            // Effacer ancienne sortie
            if (grille[sortieY][sortieX] === 'sortie') grille[sortieY][sortieX] = 'eteint';
            sortieX = x; sortieY = y;
            grille[y][x] = 'sortie';
            break;
        case 'effacer':
            grille[y][x] = 'eteint';
            if (departX === x && departY === y) { /* garder */ }
            break;
        default:
            // Empêcher de placer sur départ
            if (x === departX && y === departY) return;
            // Portails : une seule instance
            if (outilActif === 'teleport_a') {
                for (let r = 0; r < hauteur; r++)
                    for (let c = 0; c < largeur; c++)
                        if (grille[r][c] === 'teleport_a') grille[r][c] = 'eteint';
            }
            if (outilActif === 'teleport_b') {
                for (let r = 0; r < hauteur; r++)
                    for (let c = 0; c < largeur; c++)
                        if (grille[r][c] === 'teleport_b') grille[r][c] = 'eteint';
            }
            grille[y][x] = outilActif;
            break;
    }
    dessinerEditeur();
}

// Événements souris / tactile canvas éditeur
canvasEdit.addEventListener('mousedown',  (e) => { enTrainDeDessiner = true; const c = celluleDepuisEvenement(e); appliquerOutil(c.x, c.y); });
canvasEdit.addEventListener('mousemove',  (e) => { if (!enTrainDeDessiner) return; const c = celluleDepuisEvenement(e); appliquerOutil(c.x, c.y); });
canvasEdit.addEventListener('mouseup',    ()  => { enTrainDeDessiner = false; });
canvasEdit.addEventListener('mouseleave', ()  => { enTrainDeDessiner = false; });
canvasEdit.addEventListener('touchstart', (e) => { e.preventDefault(); enTrainDeDessiner = true; const c = celluleDepuisEvenement(e); appliquerOutil(c.x, c.y); }, { passive: false });
canvasEdit.addEventListener('touchmove',  (e) => { e.preventDefault(); if (!enTrainDeDessiner) return; const c = celluleDepuisEvenement(e); appliquerOutil(c.x, c.y); }, { passive: false });
canvasEdit.addEventListener('touchend',   ()  => { enTrainDeDessiner = false; });

// Palette
document.querySelectorAll('.palette-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('.palette-btn').forEach(b => b.classList.remove('actif'));
        btn.classList.add('actif');
        outilActif = btn.dataset.outil;
    });
});

// Dimensions
document.getElementById('btn-appliquer-dim').addEventListener('click', () => {
    if (confirm('Réinitialiser la grille avec les nouvelles dimensions ?')) resetGrille();
});

// Reset
document.getElementById('btn-reset').addEventListener('click', () => {
    if (confirm('Réinitialiser entièrement la grille ?')) resetGrille();
});

// ════════════════════════════════════════
// CONSTRUCTION CONFIG → MOTEUR
// ════════════════════════════════════════

function construireConfig() {
    const cases_speciales = [];
    for (let y = 0; y < hauteur; y++) {
        for (let x = 0; x < largeur; x++) {
            const t = grille[y][x];
            if (t && t !== 'eteint' && t !== 'sortie') {
                cases_speciales.push({ x, y, type: t });
            }
        }
    }
    return {
        largeur,
        hauteur,
        depart_x: departX,
        depart_y: departY,
        sortie_x: sortieX,
        sortie_y: sortieY,
        cases_speciales
    };
}

// ════════════════════════════════════════
// MODE TEST
// ════════════════════════════════════════

function validerNiveau() {
    // Vérifier départ ≠ sortie
    if (departX === sortieX && departY === sortieY) {
        afficherErreur('Le départ et la sortie doivent être sur des cases différentes.');
        return false;
    }
    // Vérifier que la case de départ n'est pas un mur
    if (grille[departY][departX] === 'mur') {
        afficherErreur('Le départ ne peut pas être sur un mur.');
        return false;
    }
    return true;
}

function afficherErreur(msg) {
    msgTest.textContent = msg;
    msgTest.className   = 'erreur';
    // Flash sur l'éditeur
    canvasEdit.style.boxShadow = '0 0 30px #800000, 0 0 60px #800000';
    setTimeout(() => { canvasEdit.style.boxShadow = ''; }, 800);
}

document.getElementById('btn-tester').addEventListener('click', () => {
    if (!validerNiveau()) return;

    // Passer en mode jeu
    zoneEditeur.style.display = 'none';
    zoneJeu.style.display     = 'block';
    statutIdle.style.display  = 'none';
    statutTest.style.display  = 'block';
    statutVictoire.style.display = 'none';
    seedSection.style.display = 'none';
    msgTest.textContent = '';
    msgTest.className   = '';

    const config = construireConfig();

    // Dimensionner le canvas de jeu
    const maxPx = Math.min(window.innerWidth < 900 ? 340 : 560, 560);
    canvasJeu.width  = maxPx;
    canvasJeu.height = maxPx;

    if (partie) { partie = null; }

    partie = new TileBurnerCampagne(canvasJeu, config, {
        onDeplacement: (n) => { hudDepl.textContent = n; },
        onMaj: (info)   => {
            hudCases.innerHTML = `Cases : <strong>${info.allumees}</strong> / <strong>${info.total}</strong>`;
        },
        onMessage: (txt) => {
            if (txt) { msgTest.textContent = txt; msgTest.className = ''; }
        },
        onVictoire: (nbDepl) => { surVictoire(nbDepl); }
    });
});

function surVictoire(nbDepl) {
    // Générer la seed depuis la config
    const config  = construireConfig();
    const encoded = btoa(unescape(encodeURIComponent(JSON.stringify(config))));
    seedGeneree   = encoded;

    // Afficher résultats
    statutTest.style.display     = 'none';
    statutVictoire.style.display = 'block';
    document.getElementById('victoire-depl').textContent = `Résolu en ${nbDepl} déplacement${nbDepl > 1 ? 's' : ''} !`;
    seedSection.style.display = 'block';
    seedValeur.textContent    = encoded.substring(0, 32) + '…';
    seedValeur.title          = encoded;

    msgTest.textContent = '🏆 Victoire ! Votre seed est prête.';
    msgTest.className   = 'victoire';
}

// Recommencer le test
document.getElementById('btn-restart-test').addEventListener('click', () => {
    if (partie) partie.reinitialiser();
    msgTest.textContent = '';
    msgTest.className   = '';
    statutTest.style.display = 'block';
    statutVictoire.style.display = 'none';
    seedSection.style.display    = 'none';
    hudDepl.textContent = '0';
});

// Retour éditeur
document.getElementById('btn-retour-edit').addEventListener('click', () => {
    zoneJeu.style.display     = 'none';
    zoneEditeur.style.display = 'block';
    statutTest.style.display  = 'none';
    statutIdle.style.display  = 'block';
    partie = null;
    dessinerEditeur();
});

// ════════════════════════════════════════
// CLAVIER (mode test)
// ════════════════════════════════════════
document.addEventListener('keydown', (e) => {
    if (!partie || zoneJeu.style.display === 'none') return;
    const tag = document.activeElement?.tagName;
    if (tag === 'INPUT' || tag === 'TEXTAREA' || document.activeElement?.isContentEditable) return;
    switch (e.key) {
        case 'ArrowUp':    case 'z': case 'Z': partie.deplacer(-1,  0); e.preventDefault(); break;
        case 'ArrowDown':  case 's': case 'S': partie.deplacer( 1,  0); e.preventDefault(); break;
        case 'ArrowLeft':  case 'q': case 'Q': partie.deplacer( 0, -1); e.preventDefault(); break;
        case 'ArrowRight': case 'd': case 'D': partie.deplacer( 0,  1); e.preventDefault(); break;
        case 'r': case 'R': if (partie) partie.reinitialiser(); break;
    }
});

// ════════════════════════════════════════
// COPIER SEED
// ════════════════════════════════════════
document.getElementById('btn-copier').addEventListener('click', () => {
    if (!seedGeneree) return;
    navigator.clipboard.writeText(seedGeneree).then(() => {
        const btn = document.getElementById('btn-copier');
        btn.textContent = '✅';
        setTimeout(() => { btn.textContent = '📋'; }, 1500);
    });
});

// ════════════════════════════════════════
// PUBLIER (utilisateur connecté)
// ════════════════════════════════════════
<?php if ($user_id > 0): ?>
document.getElementById('btn-publier')?.addEventListener('click', async () => {
    if (!seedGeneree) return;
    const nom = document.getElementById('inp-nom-niveau').value.trim();
    if (!nom) {
        document.getElementById('msg-publication').textContent = 'Donnez un nom à votre niveau.';
        return;
    }
    const config = construireConfig();
    const body = new FormData();
    body.append('nom',       nom);
    body.append('seed',      seedGeneree);
    body.append('largeur',   config.largeur);
    body.append('hauteur',   config.hauteur);
    body.append('depart_x',  config.depart_x);
    body.append('depart_y',  config.depart_y);
    body.append('sortie_x',  config.sortie_x);
    body.append('sortie_y',  config.sortie_y);
    body.append('cases_speciales', JSON.stringify(config.cases_speciales));
    body.append('type',      'custom');

    try {
        const res  = await fetch('save_niveau.php', { method: 'POST', body });
        const data = await res.json();
        const msg  = document.getElementById('msg-publication');
        if (data.status === 'ok') {
            msg.textContent = '✅ Niveau publié avec succès !';
            msg.className   = 'msg-ok';
        } else {
            msg.textContent = '❌ ' + (data.message || 'Erreur inconnue');
            msg.className   = 'msg-err';
        }
    } catch (err) {
        document.getElementById('msg-publication').textContent = '❌ Erreur réseau.';
    }
});
<?php endif; ?>

// ════════════════════════════════════════
// REJOUER / RECRÉER
// ════════════════════════════════════════
document.getElementById('btn-rejouer').addEventListener('click', () => {
    seedSection.style.display    = 'none';
    statutVictoire.style.display = 'none';
    statutIdle.style.display     = 'block';
    zoneJeu.style.display        = 'none';
    zoneEditeur.style.display    = 'block';
    seedGeneree = null;
    resetGrille();
});

// ════════════════════════════════════════
// INIT
// ════════════════════════════════════════
resetGrille();

})();
</script>
</body>
</html>