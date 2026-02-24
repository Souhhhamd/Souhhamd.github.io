<?php
    require_once "Donnees.inc.php";
    require_once "favoris.php";
    require_once "navigation.php";
    require_once "utilisateurs.php";
    require_once 'header.php';

    // Vérifier si un ID est fourni
    if (!isset($_GET['id'])) {
        die("Aucune recette demandée.");
    }

    $id = intval($_GET['id']);

    // Vérifier si la recette existe
    if (!isset($Recettes[$id])) {
        die("Recette introuvable.");
    }

    $recette = $Recettes[$id];
    $zone = isset($_GET['zone']) ? $_GET['zone'] : '';
    $requeteRecherche = isset($_GET['recherche']) ? $_GET['recherche'] : '';
    $estConnecte = isset($_SESSION['login']);
    $aliment = isset($_GET['aliment']) ? $_GET['aliment'] : 'Aliment';

    afficherHeader($zone, $aliment, $requeteRecherche);

?>

<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="UTF-8">
        <title><?= htmlspecialchars($recette['titre']) ?></title>
    </head>


    <body>

        <?php
            // Déterminer si la recette est déjà en favoris
            $favoris = chargerFavoris();
            $estFavori = in_array($id, $favoris);
            $coeur = $estFavori ? "❤️" : "🤍";
        ?>

        <div style="position:relative;">

            <!-- Coeur dynamique -->
            <a href="toggleFavori.php?id=<?= $id ?>" 
            style="position:absolute; top:10px; right:10px; 
                    font-size:30px; text-decoration:none;">
                <?= $coeur ?>
            </a>

            <!-- Titre -->
            <h1><?= htmlspecialchars($recette['titre']) ?></h1>

        </div>

        <?php
            // Gestion de la photo
            $photo = ucfirst(strtolower(str_replace(' ', '_', $recette['titre']))) . ".jpg";
            if (!file_exists("Photos/$photo")) {
                $photo = "default.jpg";
            }
        ?>

        <img src="Photos/<?= $photo ?>" width="250" style="border:1px solid #aaa;"><br><br>

        <h2>Ingrédients</h2>
        
        <?php
            // Le champ ingredients est une seule chaîne séparée par |
            $ingredientsBruts = explode('|', $recette['ingredients']);
        ?>

        <ul>
            <?php foreach ($ingredientsBruts as $ing) : ?>
                <li><?= htmlspecialchars(trim($ing)) ?></li>
            <?php endforeach; ?>
        </ul>

        <h2>Préparation</h2>
        
        <p><?= nl2br(htmlspecialchars($recette['preparation'])) ?></p>

        <br><br>

        <a href="index.php">⬅ Retour à l'accueil</a>

    </body>

    

</html>