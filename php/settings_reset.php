<?php
session_start();
require_once 'db.php';

header('Content-Type: application/json');

// Valeurs par défaut
$defaults = [
    'volume_master' => 50,
    'volume_music'  => 50,
    'volume_sfx'    => 50,
    'muted'         => 0,
    'language'      => 'fr',
];

// Si utilisateur connecté → réinitialiser en BDD
if (isset($_SESSION['id'])) {
    try {
        $user_id = (int)$_SESSION['id'];

            // UPSERT : insère ou met à jour les paramètres par défaut
            $sql = "INSERT INTO settings 
                        (utilisateur_id, volume_master, volume_music, volume_sfx, muted, language)
                    VALUES 
                        (:uid, :vm, :vmu, :vs, :mu, :lg)
                    ON DUPLICATE KEY UPDATE
                        volume_master = :vm2,
                        volume_music  = :vmu2,
                        volume_sfx    = :vs2,
                        muted         = :mu2,
                        language      = :lg2";

            $stmt = $bdd->prepare($sql);
            $stmt->execute([
                ':uid'  => $user_id,
                ':vm'   => $defaults['volume_master'],
                ':vmu'  => $defaults['volume_music'],
                ':vs'   => $defaults['volume_sfx'],
                ':mu'   => $defaults['muted'],
                ':lg'   => $defaults['language'],
                ':vm2'  => $defaults['volume_master'],
                ':vmu2' => $defaults['volume_music'],
                ':vs2'  => $defaults['volume_sfx'],
                ':mu2'  => $defaults['muted'],
                ':lg2'  => $defaults['language'],
            ]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        exit;
    }
}

// Renvoyer les valeurs par défaut au JS pour qu'il mette à jour le localStorage
echo json_encode([
    'success'  => true,
    'defaults' => $defaults
]);
