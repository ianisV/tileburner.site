<?php
session_start();
require_once 'db.php';

// Valeurs par défaut
$settings = [
    'volume_master' => 50,
    'volume_music'  => 50,
    'volume_sfx'    => 50,
    'muted'         => false,
    'language'      => 'fr',
];

// Charger depuis la BDD si utilisateur connecté
if (isset($_SESSION['pseudo'])) {
    try {
        $req = $bdd->prepare("
            SELECT s.* FROM settings s
            JOIN utilisateurs u ON u.id = s.utilisateur_id
            WHERE u.pseudo = ?
        ");
        $req->execute([$_SESSION['pseudo']]);
        $row = $req->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $settings['volume_master'] = (int)$row['volume_master'];
            $settings['volume_music']  = (int)$row['volume_music'];
            $settings['volume_sfx']    = (int)$row['volume_sfx'];
            $settings['muted']         = (bool)$row['muted'];
            $settings['language']      = $row['language'];
        }
    } catch (PDOException $e) {
        // En cas d'erreur, on garde les valeurs par défaut
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Paramètres</title>
    <link rel="stylesheet" href="../css/common.css">
    <link rel="stylesheet" href="../css/settings.css">
    <script src="../js/audio.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@700&family=MedievalSharp&display=swap" rel="stylesheet">
</head>
<body>
<?php include 'header.php'; ?>
<main class="page-content">

<div class="container">
    <h1>⚙️ Paramètres</h1>

    <form action="settings_save.php" method="POST" id="formSettings">

        <!-- AUDIO -->
        <h2>🔊 Audio</h2>

        <div class="setting">
            <label>Volume général <span class="value-display" id="val_master"><?= $settings['volume_master'] ?>%</span></label>
            <input type="range" name="volume_master" min="0" max="100"
                   value="<?= $settings['volume_master'] ?>"
                   oninput="updateUI()">
        </div>

        <div class="setting">
            <label>Musique <span class="value-display" id="val_music"><?= $settings['volume_music'] ?>%</span></label>
            <input type="range" name="volume_music" min="0" max="100"
                   value="<?= $settings['volume_music'] ?>"
                   oninput="updateUI()">
        </div>

        <div class="setting">
            <label>Effets sonores <span class="value-display" id="val_sfx"><?= $settings['volume_sfx'] ?>%</span></label>
            <input type="range" name="volume_sfx" min="0" max="100"
                   value="<?= $settings['volume_sfx'] ?>"
                   oninput="updateUI()">
        </div>

        <!-- BOUTONS -->
        <div class="buttons">
            <button type="button" class="btn-back" onclick="window.location.href='index.php'">← Retour</button>
            <button type="button" class="btn-reset" id="btnReset">Réinitialiser</button>
            <button type="submit" class="btn-save">💾 Enregistrer</button>
        </div>

    </form>
</div>

<script>
let lastSfxTest = 0;

function updateUI() {
    const m   = document.querySelector('[name="volume_master"]').value;
    const mus = document.querySelector('[name="volume_music"]').value;
    const sfx = document.querySelector('[name="volume_sfx"]').value;

    document.getElementById('val_master').textContent = m + "%";
    document.getElementById('val_music').textContent  = mus + "%";
    document.getElementById('val_sfx').textContent    = sfx + "%";

    localStorage.setItem('vol_master', m);
    localStorage.setItem('vol_music',  mus);
    localStorage.setItem('vol_sfx',    sfx);

    if (window.appliquerVolume) window.appliquerVolume();
}

// ===== BOUTON RÉINITIALISER =====
document.getElementById('btnReset').addEventListener('click', function() {
    if (!confirm('Voulez-vous vraiment réinitialiser tous les paramètres ?')) return;

    fetch('settings_reset.php', { method: 'POST' })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                const d = data.defaults;

                // 1. Mettre à jour le formulaire visuellement
                document.querySelector('[name="volume_master"]').value = d.volume_master;
                document.querySelector('[name="volume_music"]').value  = d.volume_music;
                document.querySelector('[name="volume_sfx"]').value    = d.volume_sfx;
                document.getElementById('muted') && (document.getElementById('muted').checked = (d.muted == 1 || d.muted === true));

                // 2. Mettre à jour les % affichés
                document.getElementById('val_master').textContent = d.volume_master + "%";
                document.getElementById('val_music').textContent  = d.volume_music + "%";
                document.getElementById('val_sfx').textContent    = d.volume_sfx + "%";

                // 3. Mettre à jour le localStorage
                localStorage.setItem('vol_master', d.volume_master);
                localStorage.setItem('vol_music',  d.volume_music);
                localStorage.setItem('vol_sfx',    d.volume_sfx);

                // 4. Appliquer le volume immédiatement
                if (window.appliquerVolume) window.appliquerVolume();

                alert('✅ Paramètres réinitialisés !');
            } else {
                alert('❌ Erreur lors de la réinitialisation.');
            }
        })
        .catch(err => {
            console.error(err);
            alert('❌ Erreur réseau.');
        });
});

// ===== SAUVEGARDE DU localStorage AU SUBMIT =====
document.getElementById('formSettings').addEventListener('submit', function(e) {
    const volMaster = document.querySelector('[name="volume_master"]').value;
    const volMusic  = document.querySelector('[name="volume_music"]').value;
    const volSfx    = document.querySelector('[name="volume_sfx"]').value;

    localStorage.setItem('vol_master', volMaster);
    localStorage.setItem('vol_music',  volMusic);
    localStorage.setItem('vol_sfx',    volSfx);
});
</script>

</main>
<?php include 'footer.php'; ?>
</body>
</html>
