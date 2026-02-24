<?php
    session_start();
    require_once "favoris.php";
    require_once "navigation.php"; // pour afficherRecetteSynth
    require_once "utilisateurs.php";
    

    $zone = isset($_GET['zone']) ? $_GET['zone'] : '';
    $favoris = chargerFavoris();
    $estConnecte = isset($_SESSION['login']);
    $requeteRecherche = isset($_GET['recherche']) ? $_GET['recherche'] : '';
    $aliment = isset($_GET['aliment']) ? $_GET['aliment'] : 'Aliment';

    require_once 'header.php';
    afficherHeader($zone, $aliment, $requeteRecherche);

   
?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Mes favoris</title>
    </head>

    <hr>

    <body>

    <h1>Mes recettes favorites</h1>

    <?php
        if (empty($favoris)) {
            echo "<p>Aucun favori pour le moment.</p>";
        } else {
            echo "<div style='display:flex; flex-wrap:wrap; gap:20px;'>";
            foreach ($favoris as $id) {
                afficherRecetteSynth($id);
            }
            echo "</div>";
        }
    ?>

    <br>

    <a href="index.php">Retour à l’accueil</a>

    </body>
</html>
