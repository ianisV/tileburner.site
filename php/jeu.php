<?php
header('Content-Type: text/html; charset=UTF-8');
session_start();
require 'db.php';

if (!isset($_SESSION['id']) && !isset($_SESSION['guest']) && !isset($_SESSION['invite'])) {
    header('Location: connexion.php');
    exit();
}

$user_id   = $_SESSION['id'] ?? 0;
$estInvite = empty($_SESSION['id']); // true si invité ou non connecté
$mode      = $_GET['mode'] ?? 'campagne';
$niveau_id = (int)($_GET['niveau_id'] ?? 1);

$titreNiveau = 'Tile Burner';
$modeAffiche = ucfirst($mode);

$configDefaut = ['lignes' => 5, 'colonnes' => 5, 'depart' => [0,0], 'murs' => []];
$configJson   = json_encode($configDefaut);
$seed_url     = isset($_GET['seed']) ? $_GET['seed'] : null;

/* ── Chargement BDD si niveau_id fourni (custom / aleatoire depuis catalogue) ── */
$niveauBdd = null;
if ($niveau_id > 0 && in_array($mode, ['aleatoire', 'custom'])) {
    try {
        $stmt = $bdd->prepare("SELECT * FROM niveaux WHERE id = ? LIMIT 1");
        $stmt->execute([$niveau_id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $cases = json_decode($row['murs'] ?? '[]', true) ?: [];
            $niveauBdd = [
                'largeur'        => (int)$row['largeur'],
                'hauteur'        => (int)$row['hauteur'],
                'depart_x'       => (int)$row['position_depart_x'],
                'depart_y'       => (int)$row['position_depart_y'],
                'sortie_x'       => (int)$row['sortie_x'],
                'sortie_y'       => (int)$row['sortie_y'],
                'cases_speciales'=> $cases,
            ];
            $titreNiveau = htmlspecialchars($row['nom']);
        }
    } catch (PDOException $e) { /* silencieux */ }
}

/* ── Mode WASM ─────────────────────────────────────────────── */
$estModeWasm = ($mode === 'wasm');
if ($estModeWasm) {
    $titreNiveau = 'Mode C natif';
    $modeAffiche = 'WASM (C natif)';
}
?>
<?php if (!$estModeWasm): ?>
<script>
const config = {
    mode:   "<?php echo $mode; ?>",
    seed:   <?php echo $seed_url ? $seed_url : "Date.now()"; ?>,
    largeur: 8,
    hauteur: 8
};
const NIVEAU_BDD = <?php echo $niveauBdd ? json_encode($niveauBdd, JSON_UNESCAPED_UNICODE) : 'null'; ?>;
</script>
<?php endif; ?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <script src="https://cdn.jsdelivr.net/npm/php-wasm/php-tags.jsdelivr.mjs" type="module"></script>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($titreNiveau) ?> — TileBurner</title>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/jeu.css">
    <link rel="stylesheet" href="../css/common.css">
    <?php if ($estModeWasm): ?>
    <style>
        /* ══ WASM : layout principal côte-à-côte ══ */
        .wasm-layout {
            display: flex;
            gap: 20px;
            flex-wrap: nowrap;
            justify-content: center;
            align-items: flex-start;
        }
        .wasm-left {
            display: flex; flex-direction: column; align-items: center; gap: 10px;
            flex-shrink: 0;
        }

        /* Canvas texturé (même rendu que campagne) */
        #wgame {
            display: block;
            border: 2px solid rgba(212,175,55,0.5);
            border-radius: 4px;
            box-shadow: 0 0 30px rgba(0,0,0,0.8), 0 0 15px rgba(212,175,55,0.15);
            max-width: 100%;
        }

        /* Config murs */
        .wasm-config {
            display: flex; align-items: flex-end; gap: 12px; flex-wrap: wrap;
            background: rgba(0,0,0,0.4);
            border: 1px solid rgba(212,175,55,0.25);
            border-radius: 8px; padding: 10px 14px;
            justify-content: center;
        }
        .wasm-config label {
            font-family: 'Cinzel', serif; font-size: .7rem;
            color: rgba(244,228,188,0.6); text-transform: uppercase;
            letter-spacing: 1px; display: flex; flex-direction: column; gap: 3px;
        }
        .wasm-config input[type=number] {
            width: 60px; padding: 4px 6px; text-align: center;
            background: rgba(0,0,0,0.5); border: 1px solid rgba(212,175,55,0.4);
            border-radius: 4px; color: #d4af37;
            font-family: 'Cinzel', serif; font-size: .85rem;
        }

        /* D-pad médiéval */
        .wasm-dpad {
            display: grid;
            grid-template-columns: repeat(3, 52px);
            grid-template-rows: repeat(3, 52px);
            gap: 2px;
        }
        .wdp {
            width: 52px; height: 52px;
            font-family: 'Cinzel', serif; font-size: 1.2rem;
            background: linear-gradient(180deg, #4a2c12 0%, #2a1808 100%);
            border: 1px solid rgba(212,175,55,0.5);
            border-radius: 8px; color: #d4af37; cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            transition: all .15s; box-shadow: 0 3px 0 #1a0f06; user-select: none;
        }
        .wdp:hover  { background: linear-gradient(180deg, #6b3f1c 0%, #3d2410 100%);
                      box-shadow: 0 3px 0 #1a0f06, 0 0 10px rgba(212,175,55,0.3); }
        .wdp:active { transform: translateY(2px); box-shadow: 0 1px 0 #1a0f06; }

        /* Panneau solveur — colonne de droite */
        .wasm-solver {
            background: linear-gradient(180deg, rgba(42,26,13,0.97) 0%, rgba(15,8,3,0.99) 100%);
            border: 1px solid rgba(212,175,55,0.4);
            border-radius: 8px; padding: 16px;
            width: 260px; min-width: 220px;
            display: flex; flex-direction: column; gap: 10px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.7);
            align-self: stretch;
        }
        .wasm-solver h3 {
            font-family: 'Cinzel', serif; color: #d4af37;
            font-size: .9rem; letter-spacing: 2px; text-transform: uppercase;
            border-bottom: 1px solid rgba(212,175,55,0.3);
            padding-bottom: 8px; margin: 0;
            position: relative; align-self: center;
        }
        #wsolver-status {
            font-size: .78rem; color: rgba(244,228,188,0.6);
            min-height: 14px; font-family: 'Cinzel', serif;
            align-self: center;
        }
        .wsolver-row { display: flex; gap: 6px; flex-wrap: wrap; }
        #wsol-list {
            list-style: none; flex: 1; overflow-y: auto;
            display: flex; flex-direction: column; gap: 2px;
            max-height: 350px;
        }
        #wsol-list li {
            padding: 3px 8px; border-radius: 4px;
            font-size: .75rem; color: rgba(244,228,188,0.5);
            display: flex; gap: 6px; font-family: 'Cinzel', serif; letter-spacing: 1px;
        }
        #wsol-list li.current { background: rgba(212,175,55,0.2); color: #d4af37; }
        #wsol-list li.done    { background: rgba(0,0,0,0.3); opacity:.5;
                                text-decoration: line-through; }
        .wasm-speed {
            display: flex; align-items: center; gap: 6px;
            font-size: .72rem; color: rgba(244,228,188,0.5); font-family: 'Cinzel', serif;
        }
        .wasm-speed input { flex:1; accent-color: #d4af37; }

        /* Boutons médiévaux compacts */
        .btn-wasm {
            font-family: 'Cinzel', serif; font-size: .68rem; font-weight: bold;
            text-transform: uppercase; letter-spacing: 1px;
            padding: .45rem .9rem; cursor: pointer; transition: all .2s;
            background: linear-gradient(180deg, #700000 0%, #3a0000 100%);
            color: #d4af37; border: 1px solid rgba(212,175,55,0.5);
            border-radius: 4px; box-shadow: 0 2px 0 #1a0000;
            text-decoration: none; display: inline-block;
        }
        .btn-wasm:hover {
            background: linear-gradient(180deg, #a00000 0%, #600000 100%);
            border-color: #d4af37; color: #fff;
            box-shadow: 0 2px 0 #1a0000, 0 0 10px rgba(212,175,55,0.4);
        }
        .btn-wasm:active  { transform: translateY(1px); }
        .btn-wasm:disabled { opacity:.4; cursor:not-allowed; }
        .btn-wasm-green {
            background: linear-gradient(180deg, #1a5c2a 0%, #0d3317 100%);
            border-color: rgba(34,197,94,0.5); color: #4ade80;
        }
        .btn-wasm-green:hover {
            background: linear-gradient(180deg, #25803b 0%, #154522 100%);
            color: #fff; border-color: #4ade80;
            box-shadow: 0 2px 0 #1a0000, 0 0 10px rgba(74,222,128,0.4);
        }

        #wmsg {
            font-family: 'Cinzel', serif; font-size: .88rem; letter-spacing: 2px;
            text-transform: uppercase; min-height: 18px; text-align: center;
            color: #4ade80; text-shadow: 0 0 10px rgba(74,222,128,0.5);
        }

        /* Loading */
        #wasm-loading {
            display: flex; align-items: center; justify-content: center;
            width: 480px; height: 480px;
            font-family: 'Cinzel', serif; color: rgba(212,175,55,0.7);
            font-size: .88rem; letter-spacing: 2px; text-transform: uppercase;
        }

        /* Responsive : passer en colonne sur petit écran */
        @media (max-width: 780px) {
            .wasm-layout { flex-wrap: wrap; }
            .wasm-solver { width: 100%; min-width: unset; align-self: auto; }
            #wsol-list { max-height: 180px; }
        }
    </style>
    <?php endif; ?>
    <script src="../js/audio.js" defer></script>
    <script src="../js/skin-secret.js" defer></script>
    <?php if (!$estModeWasm): ?>
    <script>window.PROGRESSION_USER_ID = <?= json_encode((string)($user_id ?: 'invite')) ?>;</script>
    <script src="../js/niveaux-campagne.js"></script>
    <script src="../js/progression.js"></script>
    <?php if ($mode === 'auto' || $mode === 'automatique'): ?>
        <script src="../js/tile-burner-game.js" defer></script>
    <?php else: ?>
        <script src="../js/tile-burner-campagne.js" defer></script>
    <?php endif; ?>
    <?php endif; ?>
</head>

<body>
<?php include 'header.php'; ?>

<main class="jeu-wrapper">
<div class="game-container">

    <h2 class="niveau-titre" id="titre-niveau"><?= htmlspecialchars($titreNiveau) ?></h2>
    <p class="niveau-mode">Mode : <?= htmlspecialchars($modeAffiche) ?></p>

    <?php if ($estModeWasm): ?>
    <!-- ════════════════════════════════════════════════════
         MODE WASM — Grille C via WebAssembly
         ════════════════════════════════════════════════════ -->

    <!-- Config -->
    <div class="wasm-config">
        <label>
            Murs
            <input type="number" id="wcfg-walls" value="5" min="0" max="40">
        </label>
        <button class="btn-wasm" onclick="wNouvellePartie()">🎲 Nouvelle partie</button>
        <button class="btn-wasm" onclick="wReset()">↺ Recommencer</button>
        <?php if (!$estInvite): ?>
        <label>
            Nom
            <input type="text" id="wcfg-nom" placeholder="Niveau C natif"
                   maxlength="60"
                   style="width:160px;padding:4px 6px;background:rgba(0,0,0,0.5);
                          border:1px solid rgba(212,175,55,0.4);border-radius:4px;
                          color:#d4af37;font-family:'Cinzel',serif;font-size:.78rem;">
        </label>
        <button class="btn-wasm btn-wasm-green" id="wbtn-publier" onclick="wPublierNiveau()">📤 Publier</button>
        <?php endif; ?>
        <a href="menu.php" class="btn-wasm">← Menu</a>
    </div>
    <div id="wmsg-pub" style="font-family:'Cinzel',serif;font-size:.78rem;letter-spacing:1px;text-align:center;min-height:1.2rem;margin-top:4px;"></div>

    <!-- HUD -->
    <div class="hud" style="justify-content:center">
        <span id="whud-dep">Déplacements : <strong>0</strong></span>
    </div>
    <div id="wmsg"></div>

    <!-- Contenu principal : canvas texturé + solveur côte à côte -->
    <div class="wasm-layout">
        <div class="wasm-left">
            <div id="wasm-loading">⚙ Chargement du module C…</div>
            <!-- Canvas avec les vraies textures du moteur campagne -->
            <canvas id="wgame" width="480" height="480" style="display:none"></canvas>
            <!-- D-pad -->
            <div class="wasm-dpad" id="wasm-dpad" style="display:none">
                <div></div>
                <div class="wdp" onclick="wJouer(0)" title="Nord (Z/↑)">↑</div>
                <div></div>
                <div class="wdp" onclick="wJouer(3)" title="Ouest (Q/←)">←</div>
                <div></div>
                <div class="wdp" onclick="wJouer(1)" title="Est (D/→)">→</div>
                <div></div>
                <div class="wdp" onclick="wJouer(2)" title="Sud (S/↓)">↓</div>
                <div></div>
            </div>
        </div>

        <!-- Solveur — colonne de droite -->
        <div class="wasm-solver" id="wasm-solver" style="display:none">
            <h3>⚔ Solveur IDA*</h3>
            <div id="wsolver-status">En attente…</div>
            <div class="wsolver-row">
                <button class="btn-wasm btn-wasm-green" id="wbtn-solve"
                        onclick="wLancerSolveur()" disabled>Résoudre</button>
                <button class="btn-wasm" id="wbtn-play"
                        onclick="wJouerSolution()" disabled>▶ Animer</button>
                <button class="btn-wasm" id="wbtn-stop"
                        onclick="wStopAnim()" disabled>⏹</button>
            </div>
            <div class="wasm-speed">
                Vitesse :
                <input type="range" id="wspeed" min="100" max="1500" value="500"
                       oninput="wAnimSpeed=+this.value;
                                document.getElementById('wspeed-lbl').textContent=this.value+'ms'">
                <span id="wspeed-lbl">500ms</span>
            </div>
            <ul id="wsol-list"></ul>
        </div>
    </div>

    <!-- ── Emscripten : Module déclaré AVANT jeu.js ── -->
    <script>
    var Module = {
        onRuntimeInitialized: function() {
            // Bind des fonctions C
            w_init            = Module.cwrap('init',            'number', ['number','number','number','number']);
            w_jouer           = Module.cwrap('jouer',           'number', ['number']);
            w_resoudre        = Module.cwrap('resoudre',        'number', []);
            w_getSolutionCoup = Module.cwrap('getSolutionCoup', 'number', ['number']);
            w_reset           = Module.cwrap('reset',           null,     []);
            w_getNbLignes     = Module.cwrap('getNbLignes',     'number', []);
            w_getNbColonnes   = Module.cwrap('getNbColonnes',   'number', []);
            w_getJoueurX      = Module.cwrap('getJoueurX',      'number', []);
            w_getJoueurY      = Module.cwrap('getJoueurY',      'number', []);
            w_getNbDep        = Module.cwrap('getNbDep',        'number', []);
            w_getCase         = Module.cwrap('getCase',         'number', ['number','number']);
            w_estSortie       = Module.cwrap('estSortie',       'number', ['number','number']);
            w_estGagne        = Module.cwrap('estGagne',        'number', []);

            wasmPret = true;
            document.getElementById('wasm-loading').style.display = 'none';
            document.getElementById('wgame').style.display        = '';
            document.getElementById('wasm-dpad').style.display    = '';
            document.getElementById('wasm-solver').style.display  = '';
            document.getElementById('wbtn-solve').disabled        = false;
            document.getElementById('wsolver-status').textContent = 'Prêt.';
            wInitImages(wNouvellePartie);
        },
        print:    function(t) { console.log('[C]', t); },
        printErr: function(t) { console.warn('[C]', t); }
    };
    </script>
    <!-- Chemin relatif depuis php/ vers wasm/ -->
    <script src="../wasm/jeu.js"></script>

    <script>
    /* ── État ── */
    var wasmPret = false;
    var w_init, w_jouer, w_resoudre, w_getSolutionCoup, w_reset;
    var w_getNbLignes, w_getNbColonnes, w_getJoueurX, w_getJoueurY;
    var w_getNbDep, w_getCase, w_estSortie, w_estGagne;

    var wAnimSolution = [];
    var wAnimStep     = 0;
    var wAnimTimer    = null;
    var wAnimSpeed    = 500;

    /* ── Images (mêmes assets que tile-burner-campagne.js) ── */
    var wImages   = {};
    var wImgPrets = false;
    var wDerniereDir = 'bas';   // direction joueur pour choisir le bon sprite

    function wInitImages(callback) {
        var skinActif = window.SkinSecret && window.SkinSecret.estActif();
        var sfx = skinActif ? '_secret' : '';
        var liste = {
            eteinte:      '../img/asset/case_eteinte.png',
            allumee:      '../img/asset/case_allumee.png',
            mur1:         '../img/asset/mur1.png',
            mur2:         '../img/asset/mur2.png',
            mur3:         '../img/asset/mur3.png',
            mur4:         '../img/asset/mur4.png',
            mur5:         '../img/asset/mur5.png',
            sortie:       '../img/asset/sortie.png',
            joueur_haut:  '../img/asset/joueur' + sfx + '_haut.png',
            joueur_bas:   '../img/asset/joueur' + sfx + '_bas.png',
            joueur_gauche:'../img/asset/joueur' + sfx + '_gauche.png',
            joueur_droite:'../img/asset/joueur' + sfx + '_droite.png',
        };
        // variantes de murs mémorisées par position
        wMurVariantes = {};

        var total  = Object.keys(liste).length;
        var charge = 0;
        for (var nom in liste) {
            (function(n, src) {
                var img = new Image();
                img.onload = img.onerror = function() {
                    charge++;
                    if (charge === total) { wImgPrets = true; if (callback) callback(); }
                };
                img.src = src;
                wImages[n] = img;
            })(nom, liste[nom]);
        }
    }

    var wMurVariantes = {};

    /* ── Rendu canvas avec textures campagne ── */
    function wRendreGrille() {
        var rows   = w_getNbLignes();
        var cols   = w_getNbColonnes();
        var jx     = w_getJoueurX();
        var jy     = w_getJoueurY();
        var canvas = document.getElementById('wgame');
        var taille = Math.floor(Math.min(480 / rows, 480 / cols));
        canvas.width  = taille * cols;
        canvas.height = taille * rows;
        var ctx = canvas.getContext('2d');
        ctx.clearRect(0, 0, canvas.width, canvas.height);

        for (var i = 0; i < rows; i++) {
            for (var j = 0; j < cols; j++) {
                var px     = j * taille;
                var py     = i * taille;
                var t      = w_getCase(i, j);
                var sortie = w_estSortie(i, j);

                // Fond : toujours case_eteinte
                wDrawImg(ctx, 'eteinte', px, py, taille, '#1a1208');

                if (sortie) {
                    wDrawImg(ctx, 'sortie', px, py, taille, '#00aa44');
                } else if (t === 2) { // MUR
                    var key = i + ',' + j;
                    if (!wMurVariantes[key]) wMurVariantes[key] = 1 + Math.floor(Math.random() * 5);
                    wDrawImg(ctx, 'mur' + wMurVariantes[key], px, py, taille, '#3a3a48');
                } else if (t === 1) { // ALLUMEE
                    wDrawImg(ctx, 'allumee', px, py, taille, 'rgba(212,175,55,0.4)');
                }
            }
        }

        // Joueur
        var nomSprite = 'joueur_' + wDerniereDir;
        wDrawImg(ctx, nomSprite, jy * taille, jx * taille, taille, '#d4af37');

        document.querySelector('#whud-dep strong').textContent = w_getNbDep();
    }

    function wDrawImg(ctx, nom, px, py, taille, fallback) {
        var img = wImages[nom];
        if (img && img.complete && img.naturalWidth > 0) {
            ctx.drawImage(img, px, py, taille, taille);
        } else if (fallback) {
            ctx.fillStyle = fallback;
            ctx.fillRect(px, py, taille, taille);
        }
    }

    /* ── Actions ── */
    function wNouvellePartie() {
        if (!wasmPret) return;
        wStopAnim();
        var murs = parseInt(document.getElementById('wcfg-walls').value) || 0;
        w_init(murs, 0, 0, 0);
        wAnimSolution = [];
        document.getElementById('wsol-list').innerHTML = '';
        document.getElementById('wsolver-status').textContent = 'Prêt.';
        document.getElementById('wbtn-play').disabled = true;
        document.getElementById('wmsg').textContent = '';
        wRendreGrille();
    }

    function wReset() {
        if (!wasmPret) return;
        wStopAnim();
        w_reset();
        wAnimSolution = [];
        document.getElementById('wsol-list').innerHTML = '';
        document.getElementById('wsolver-status').textContent = 'Prêt.';
        document.getElementById('wbtn-play').disabled = true;
        document.getElementById('wmsg').textContent = '';
        wRendreGrille();
    }

    function wJouer(dir) {
        if (!wasmPret) return;
        if (w_estGagne()) return; // bloquer tout mouvement après victoire
        wStopAnim();
        // Mettre à jour la direction pour le sprite joueur
        var DIRS_NOM = ['haut', 'droite', 'bas', 'gauche'];
        wDerniereDir = DIRS_NOM[dir] || 'bas';
        w_jouer(dir);
        wRendreGrille();
        if (w_estGagne()) {
            document.getElementById('wmsg').textContent = '⚔ Victoire ! ⚔';
            document.getElementById('wmsg').style.color = '#4ade80';
        }
    }

    /* ── Solveur ── */
    function wLancerSolveur() {
        if (!wasmPret) return;
        wStopAnim();
        document.getElementById('wsolver-status').textContent = 'Recherche en cours…';
        document.getElementById('wbtn-solve').disabled = true;
        setTimeout(function() {
            w_reset(); // Remettre le niveau à zéro côté C avant de résoudre
            var nb = w_resoudre();
            document.getElementById('wbtn-solve').disabled = false;
            if (nb < 0) {
                document.getElementById('wsolver-status').textContent = 'Aucune solution.';
                return;
            }
            document.getElementById('wsolver-status').textContent =
                'Solution : ' + nb + ' coup' + (nb > 1 ? 's' : '') + '.';
            wAnimSolution = [];
            for (var i = 0; i < nb; i++) wAnimSolution.push(w_getSolutionCoup(i));

            var DIRS = ['↑ Nord', '→ Est', '↓ Sud', '← Ouest'];
            var ul = document.getElementById('wsol-list');
            ul.innerHTML = '';
            wAnimSolution.forEach(function(d, i) {
                var li = document.createElement('li');
                li.id = 'wstep-' + i;
                li.innerHTML = '<span style="width:20px;opacity:.5">' + (i+1) + '.</span>' + DIRS[d];
                ul.appendChild(li);
            });
            document.getElementById('wbtn-play').disabled = false;
        }, 30);
    }

    function wJouerSolution() {
        if (!wAnimSolution.length) return;
        wStopAnim();
        w_reset(); // recommencer depuis le début avant d'animer
        wRendreGrille();
        wAnimStep = 0;
        document.getElementById('wbtn-stop').disabled = false;
        document.getElementById('wbtn-play').disabled = true;
        wHilite(-1);
        wAnimTimer = setInterval(function() {
            if (wAnimStep >= wAnimSolution.length) { wStopAnim(); return; }
            wHilite(wAnimStep);
            var DIRS_NOM = ['haut', 'droite', 'bas', 'gauche'];
            wDerniereDir = DIRS_NOM[wAnimSolution[wAnimStep]] || 'bas';
            w_jouer(wAnimSolution[wAnimStep]);
            wRendreGrille();
            wAnimStep++;
            if (w_estGagne()) {
                wStopAnim();
                document.getElementById('wmsg').textContent = '⚔ Victoire ! ⚔';
            }
        }, wAnimSpeed);
    }

    function wStopAnim() {
        if (wAnimTimer) { clearInterval(wAnimTimer); wAnimTimer = null; }
        document.getElementById('wbtn-stop').disabled = true;
        if (wAnimSolution.length) document.getElementById('wbtn-play').disabled = false;
    }

    /* ── Publication ── */
    async function wPublierNiveau() {
        if (!wasmPret) return;

        var msgEl = document.getElementById('wmsg-pub');
        var btn   = document.getElementById('wbtn-publier');
        if (!btn) return;

        // Générer une seed textuelle (base64 de la config) comme le concepteur
        var rows = w_getNbLignes();
        var cols = w_getNbColonnes();
        var casesSpeciales = [];
        for (var i = 0; i < rows; i++) {
            for (var j = 0; j < cols; j++) {
                if (w_getCase(i, j) === 2) casesSpeciales.push({ x: j, y: i, type: 'mur' });
            }
        }
        var configPourSeed = {
            largeur: cols, hauteur: rows,
            depart_x: 0, depart_y: 0,
            sortie_x: cols - 1, sortie_y: rows - 1,
            cases_speciales: casesSpeciales
        };
        var seed = btoa(unescape(encodeURIComponent(JSON.stringify(configPourSeed))));

        btn.disabled   = true;
        btn.textContent = '⏳ Publication…';
        msgEl.style.color = 'rgba(244,228,188,0.6)';
        msgEl.textContent = '';

        // Lire le nom saisi, fallback sur le placeholder
        var nomInput = document.getElementById('wcfg-nom');
        var nom = nomInput ? nomInput.value.trim() : '';

        try {
            var body = new FormData();
            body.append('seed',            seed);
            body.append('largeur',         cols);
            body.append('hauteur',         rows);
            body.append('depart_x',        0);
            body.append('depart_y',        0);
            body.append('sortie_x',        cols - 1);
            body.append('sortie_y',        rows - 1);
            body.append('cases_speciales', JSON.stringify(casesSpeciales));
            body.append('nom',             nom);
            body.append('type',            'wasm');

            var res  = await fetch('save_niveau.php', { method: 'POST', body: body });
            var data = await res.json();

            if (data.status === 'ok') {
                msgEl.style.color = '#4ade80';
                msgEl.textContent = '✅ Niveau publié avec succès !';
            } else {
                msgEl.style.color = '#c0392b';
                msgEl.textContent = '❌ ' + (data.message || 'Erreur inconnue');
            }
        } catch (err) {
            msgEl.style.color = '#c0392b';
            msgEl.textContent = '❌ Erreur réseau.';
        } finally {
            btn.disabled    = false;
            btn.textContent = '📤 Publier';
        }
    }

    function wHilite(idx) {
        document.querySelectorAll('#wsol-list li').forEach(function(li, i) {
            li.className = i < idx ? 'done' : i === idx ? 'current' : '';
        });
        if (idx >= 0) {
            var el = document.getElementById('wstep-' + idx);
            if (el) el.scrollIntoView({ block: 'nearest' });
        }
    }

    /* ── Clavier ── */
    document.addEventListener('keydown', function(e) {
        if (!wasmPret) return;
        // Ne pas intercepter les touches si le focus est dans un champ texte
        var tag = document.activeElement && document.activeElement.tagName;
        if (tag === 'INPUT' || tag === 'TEXTAREA') return;
        var map = { ArrowUp:0, z:0, Z:0, ArrowRight:1, d:1, D:1,
                    ArrowDown:2, s:2, S:2, ArrowLeft:3, q:3, Q:3 };
        if (map[e.key] !== undefined) { e.preventDefault(); wJouer(map[e.key]); }
    });
    </script>

    <?php else: ?>
    <!-- ════════════════════════════════════════════════════
         MODES CLASSIQUES (campagne / aleatoire / custom)
         ════════════════════════════════════════════════════ -->
    <div class="hud">
        <a href="menu.php" class="btn-back">← Menu</a>
        <span id="hud-depl">Déplacements : <strong>0</strong></span>
        <span id="hud-cases">Cases : <strong>0</strong> / <strong>0</strong></span>
        <button type="button" id="btn-undo">Annuler</button>
        <button type="button" id="btn-restart">Recommencer</button>
    </div>

    <canvas id="game" width="600" height="600" aria-label="Grille de jeu"></canvas>

    <div id="message" role="status" aria-live="polite"></div>

    <div class="instructions">
        <p>Allumez <strong>toutes</strong> les cases puis atteignez la <strong>sortie</strong>.</p>
        <p>Repasser sur une case allumée l'<strong>éteint</strong>. Déplacement :
            <kbd>↑</kbd><kbd>↓</kbd><kbd>←</kbd><kbd>→</kbd> ou <kbd>Z</kbd><kbd>Q</kbd><kbd>S</kbd><kbd>D</kbd>
        </p>
    </div>

    <!-- Pop-up victoire -->
    <div id="popup-victoire" class="popup-overlay" style="display:none">
        <div class="popup-box">
            <h2>⚔️ Victoire ! ⚔️</h2>
            <p class="popup-score">Terminé en <strong id="popup-depl">0</strong> déplacements</p>
            <div class="popup-actions">
                <a href="#" id="popup-suivant" class="btn-menu">Niveau suivant →</a>
                <a href="menu.php" class="btn-menu">← Retour au menu</a>
            </div>
        </div>
    </div>

    <script>
    (function () {
        const canvas = document.getElementById('game');
        const msgEl  = document.getElementById('message');
        const popup  = document.getElementById('popup-victoire');
        const popupDepl    = document.getElementById('popup-depl');
        const popupSuivant = document.getElementById('popup-suivant');

        const CONFIG_SERVEUR = <?= $configJson ?>;
        const MODE     = <?= json_encode($mode) ?>;
        const NIVEAU_ID = <?= (int)$niveau_id ?>;
        const NB_NIVEAUX = 20;

        let partie = null;

        const urlParams = new URLSearchParams(window.location.search);
        let seedCourante = urlParams.get('seed');

        if (MODE === 'aleatoire' && !seedCourante) {
            seedCourante = Date.now();
        } else if (seedCourante) {
            seedCourante = parseInt(seedCourante);
        }

        function obtenirConfig() {
            // Niveau chargé depuis la BDD (custom ou aléatoire du catalogue)
            if (typeof NIVEAU_BDD !== 'undefined' && NIVEAU_BDD) return NIVEAU_BDD;
            if (MODE === 'aleatoire') return genererNiveauAleatoire(seedCourante);
            if (MODE === 'campagne' && window.NIVEAUX_CAMPAGNE) {
                const n = window.NIVEAUX_CAMPAGNE.find(n => n.id === NIVEAU_ID);
                if (n) return n;
            }
            return CONFIG_SERVEUR;
        }

        function sauvegarderProgression(score) {
            if (typeof Progression !== 'undefined') Progression.terminerNiveau(NIVEAU_ID, score);
            // Niveau catalogue (aleatoire/custom avec niveau_id connu)
            if (typeof NIVEAU_BDD !== 'undefined' && NIVEAU_BDD && NIVEAU_ID > 0) {
                const data = new FormData();
                data.append('niveau_id', NIVEAU_ID);
                data.append('score', score);
                return fetch('sauvegarder_score_catalogue.php', { method: 'POST', body: data })
                    .then(r => r.json()).catch(() => {});
            }
            if (MODE !== 'campagne') return Promise.resolve();
            const data = new FormData();
            data.append('niveau_id', NIVEAU_ID);
            data.append('score', score);
            data.append('termine', 1);
            return fetch('sauvegarder_progression.php', { method: 'POST', body: data })
                .then(r => r.json()).catch(() => {});
        }

        function afficherPopupVictoire(score) {
            sauvegarderProgression(score);
            popupDepl.textContent = score;
            if (MODE === 'campagne' && NIVEAU_ID < NB_NIVEAUX) {
                popupSuivant.href = `jeu.php?mode=campagne&niveau_id=${NIVEAU_ID + 1}`;
                popupSuivant.style.display = '';
                popupSuivant.textContent = `Niveau ${NIVEAU_ID + 1} →`;
            } else if (MODE === 'campagne') {
                popupSuivant.style.display = 'none';
            } else {
                popupSuivant.href = 'jeu.php?mode=aleatoire';
                popupSuivant.textContent = '↻ Nouvelle partie';
                popupSuivant.style.display = '';
            }
            popup.style.display = 'flex';
        }

        function demarrerPartie() {
            const config = obtenirConfig();
            const callbacks = {
                onDeplacement: (n) => {
                    const el = document.querySelector('#hud-depl strong');
                    if (el) el.textContent = n;
                },
                onMaj: (info) => {
                    const el = document.querySelector('#hud-cases');
                    if (el) el.innerHTML =
                        `Cases : <strong>${info.allumees}</strong> / <strong>${info.total}</strong>`;
                },
                onMessage: (txt) => { if (msgEl) msgEl.textContent = txt; },
                onVictoire: (nb) => afficherPopupVictoire(nb),
            };
            const modeAuto = (config.mode === 'auto' || config.mode === 'automatique');
            if (modeAuto && typeof TileBurnerGame !== 'undefined') {
                partie = new TileBurnerGame(canvas, config, callbacks);
            } else if (typeof TileBurnerCampagne !== 'undefined') {
                partie = new TileBurnerCampagne(canvas, config, callbacks);
            } else {
                console.error('Aucun moteur de jeu chargé');
            }
        }

        window.addEventListener('load', () => {
            demarrerPartie();
            document.getElementById('btn-restart').addEventListener('click', () => {
                if (partie) partie.recommencer();
                msgEl.textContent = ''; msgEl.className = '';
                popup.style.display = 'none';
            });
            document.getElementById('btn-undo').addEventListener('click', () => {
                if (partie) partie.annuler();
            });
            document.addEventListener('keydown', (e) => {
                if (!partie) return;
                switch (e.key) {
                    case 'ArrowUp':  case 'z': case 'w': partie.deplacer(-1, 0); e.preventDefault(); break;
                    case 'ArrowDown': case 's':          partie.deplacer(1, 0);  e.preventDefault(); break;
                    case 'ArrowLeft': case 'q': case 'a': partie.deplacer(0,-1); e.preventDefault(); break;
                    case 'ArrowRight': case 'd':          partie.deplacer(0, 1); e.preventDefault(); break;
                }
            });
        });
    })();
    </script>
    <?php endif; ?>

</div><!-- .game-container -->
</main>

<?php include 'footer.php'; ?>
</body>
</html>