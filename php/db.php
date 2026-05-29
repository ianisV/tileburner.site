<?php

$host   = 'localhost'; 
$user   = 'root';                     
$pass   = 'root'; 
$dbname = 'tileburner';


function niveauxCampagneParDefaut(): array
{
    return [
        [
            'nom'   => 'Niveau 1 - Initiation',
            'ordre' => 1,
            'depart_x' => 0, 'depart_y' => 0,
            'sortie_x' => 9, 'sortie_y' => 9,
            'murs'  => json_encode([[2,3],[3,2],[5,5],[7,1],[1,8]]),
        ],
        [
            'nom'   => 'Niveau 2 - Couloirs',
            'ordre' => 2,
            'depart_x' => 0, 'depart_y' => 0,
            'sortie_x' => 0, 'sortie_y' => 9,
            'murs'  => json_encode([
                [1,0],[1,2],[1,4],[1,6],[1,8],
                [3,1],[3,3],[3,5],[3,7],[3,9],
                [5,0],[5,2],[5,4],[5,6],[5,8],
                [7,1],[7,3],[7,5],[7,7],[7,9],
            ]),
        ],
        [
            'nom'   => 'Niveau 3 - Labyrinthe',
            'ordre' => 3,
            'depart_x' => 0, 'depart_y' => 0,
            'sortie_x' => 9, 'sortie_y' => 0,
            'murs'  => json_encode([
                [0,2],[0,3],[0,4],[0,5],[0,6],[0,7],
                [2,1],[2,2],[2,3],[2,4],[2,5],
                [4,4],[4,5],[4,6],[4,7],[4,8],
                [6,2],[6,3],[6,4],[6,5],
                [8,5],[8,6],[8,7],[8,8],[8,9],
            ]),
        ],
        [
            'nom'   => 'Niveau 4 - Îlots',
            'ordre' => 4,
            'depart_x' => 5, 'depart_y' => 5,
            'sortie_x' => 0, 'sortie_y' => 0,
            'murs'  => json_encode([
                [1,1],[1,2],[2,1],
                [1,7],[1,8],[2,8],
                [7,1],[8,1],[8,2],
                [7,7],[7,8],[8,7],[8,8],
                [4,4],[4,5],[5,4],[5,6],[6,5],
                [0,4],[0,5],[4,0],[5,0],
            ]),
        ],
        [
            'nom'   => 'Niveau 5 - Expert',
            'ordre' => 5,
            'depart_x' => 9, 'depart_y' => 9,
            'sortie_x' => 0, 'sortie_y' => 0,
            'murs'  => json_encode([
                [0,3],[1,3],[2,3],
                [0,6],[1,6],[2,6],
                [3,0],[3,1],[3,2],
                [3,7],[3,8],[3,9],
                [6,0],[6,1],[6,2],
                [6,7],[6,8],[6,9],
                [7,3],[8,3],[9,3],
                [7,6],[8,6],[9,6],
                [4,4],[4,5],[5,4],[5,5],
            ]),
        ],
    ];
}

function insererNiveauxCampagneManquants(PDO $bdd): void{
    $existe = $bdd->prepare(
        "SELECT id FROM niveaux WHERE type_niveau = 'campagne' AND ordre = ? LIMIT 1"
    );
    $inserer = $bdd->prepare("
        INSERT INTO niveaux
            (nom, ordre, largeur, hauteur, position_depart_x, position_depart_y,
             sortie_x, sortie_y, murs, type_niveau)
        VALUES
            (:nom, :ordre, 10, 10, :dx, :dy, :sx, :sy, :murs, 'campagne')
    ");

    foreach (niveauxCampagneParDefaut() as $niv) {
        $existe->execute([$niv['ordre']]);
        if ($existe->fetch()) {
            continue;
        }
        $inserer->execute([
            ':nom'   => $niv['nom'],
            ':ordre' => $niv['ordre'],
            ':dx'    => $niv['depart_x'],
            ':dy'    => $niv['depart_y'],
            ':sx'    => $niv['sortie_x'],
            ':sy'    => $niv['sortie_y'],
            ':murs'  => $niv['murs'],
        ]);
    }
}

function connecterBDD(string $host, string $user, string $pass, string $dbname): PDO{
    try {
        $bdd = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
        $bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $bdd;
    }
    catch (PDOException $e) {
        throw new PDOException('Connexion MySQL locale impossible : ' . $e->getMessage());
    }
}

try {
    $bdd = connecterBDD($host, $user, $pass, $dbname);

    $bdd->exec("
        CREATE TABLE IF NOT EXISTS utilisateurs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            pseudo VARCHAR(50) NOT NULL UNIQUE,
            email VARCHAR(150) NOT NULL UNIQUE,
            mot_de_passe VARCHAR(255) NOT NULL,
            score_total INT DEFAULT 0,
            date_inscription DATETIME DEFAULT CURRENT_TIMESTAMP,
            derniere_connexion DATETIME NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8
    ");

    $bdd->exec("
        CREATE TABLE IF NOT EXISTS niveaux (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nom VARCHAR(100) NOT NULL,
            ordre INT NOT NULL,
            largeur INT NOT NULL DEFAULT 10,
            hauteur INT NOT NULL DEFAULT 10,
            position_depart_x INT DEFAULT 0,
            position_depart_y INT DEFAULT 0,
            murs TEXT,
            sortie_x INT DEFAULT 9,
            sortie_y INT DEFAULT 9,
            type_niveau ENUM('campagne','aleatoire','custom') DEFAULT 'campagne'
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8
    ");

    $bdd->exec("
        CREATE TABLE IF NOT EXISTS progression (
            id INT AUTO_INCREMENT PRIMARY KEY,
            utilisateur_id INT NOT NULL,
            niveau_id INT NOT NULL,
            position_actuelle_x INT DEFAULT 0,
            position_actuelle_y INT DEFAULT 0,
            meilleur_score INT DEFAULT 0,
            termine TINYINT(1) DEFAULT 0,
            date_maj DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY uniq_user_niveau (utilisateur_id, niveau_id),
            FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
            FOREIGN KEY (niveau_id) REFERENCES niveaux(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8
    ");

    $bdd->exec("
        CREATE TABLE IF NOT EXISTS settings (
            utilisateur_id INT PRIMARY KEY,
            volume_master INT DEFAULT 50,
            volume_music INT DEFAULT 50,
            volume_sfx INT DEFAULT 50,
            muted TINYINT(1) DEFAULT 0,
            language VARCHAR(5) DEFAULT 'fr',
            FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8
    ");

    $bdd->exec("
        CREATE TABLE IF NOT EXISTS boutique_skins (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nom VARCHAR(100) NOT NULL,
            image_url VARCHAR(255) NOT NULL,
            prix INT NOT NULL DEFAULT 0,
            type_skin VARCHAR(50) DEFAULT 'avatar'
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8
    ");

    $checkSkins = $bdd->query("SELECT COUNT(*) FROM boutique_skins");
    if ($checkSkins->fetchColumn() == 0) {
        $reqInsert = $bdd->prepare("
            INSERT INTO boutique_skins (id, nom, image_url, prix, type_skin) 
            VALUES (?, ?, ?, ?, ?)
        ");
        
        $reqInsert->execute([1, 'Aventurier de Feu', '../images/skins/avatar_feu.png', 500, 'avatar']);
        $reqInsert->execute([2, "Chevalier d'Ombre", '../images/skins/avatar_ombre.png', 1200, 'avatar']);
        $reqInsert->execute([3, 'Seigneur de Givre', '../images/skins/avatar_givre.png', 2500, 'avatar']);
    }

    $ajouterColonneSiManquante = function ($table, $colonne, $definition) use ($bdd, $dbname) {
        $req = $bdd->prepare("
            SELECT COUNT(*) FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ?
        ");
        $req->execute([$dbname, $table, $colonne]);
        if ($req->fetchColumn() == 0) {
            $bdd->exec("ALTER TABLE `$table` ADD COLUMN `$colonne` $definition");
        }
    };

    $ajouterColonneSiManquante('utilisateurs', 'score_total',        'INT DEFAULT 0');
    $ajouterColonneSiManquante('utilisateurs', 'date_inscription',   'DATETIME DEFAULT CURRENT_TIMESTAMP');
    $ajouterColonneSiManquante('utilisateurs', 'derniere_connexion', 'DATETIME NULL');
    $ajouterColonneSiManquante('utilisateurs', 'points',             'INT DEFAULT 0');
    $ajouterColonneSiManquante('niveaux', 'sortie_x',                'INT DEFAULT 9');
    $ajouterColonneSiManquante('niveaux', 'sortie_y',                'INT DEFAULT 9');
    $ajouterColonneSiManquante('niveaux', 'type_niveau',             "ENUM('campagne','aleatoire','custom') DEFAULT 'campagne'");
    $ajouterColonneSiManquante('niveaux', 'seed',                    'VARCHAR(50) NULL');

    insererNiveauxCampagneManquants($bdd);

    $bdd->exec("
        CREATE TABLE IF NOT EXISTS achats_utilisateurs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            utilisateur_id INT NOT NULL,
            skin_id INT NOT NULL,
            date_achat DATETIME DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY uniq_user_skin (utilisateur_id, skin_id),
            FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
            FOREIGN KEY (skin_id) REFERENCES boutique_skins(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8
    ");

    $db = $bdd;

} catch (Exception $e) {
    $message = htmlspecialchars($e->getMessage());
    $aide = 'Vérifiez que votre serveur local MAMP ou XAMPP est bien démarré.';

    die("<!DOCTYPE html><html lang='fr'><head><meta charset='UTF-8'><title>Base de données</title>
        <style>body{font-family:sans-serif;max-width:520px;margin:3rem auto;padding:1rem}
        code{background:#f0f0f0;padding:2px 6px}</style></head><body>
        <h1>Connexion à la base locale impossible</h1>
        <p>$message</p>
        <p>$aide</p>
        </body></html>");
}
?>
