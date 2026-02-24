<?php
require_once 'Donnees.inc.php';

/* ===============================
   FONCTIONS DE HIÉRARCHIE
   =============================== */

function getSousCategories($aliment) {
    global $Hierarchie;
    if (!isset($Hierarchie[$aliment]['sous-categorie'])) return [];
    return $Hierarchie[$aliment]['sous-categorie'];
}

function getSuperCategories($aliment) {
    global $Hierarchie; // global veut dire qu'on utilise la variable définie en dehors de la fonction
    if (!isset($Hierarchie[$aliment]['super-categorie'])) return []; //
    return $Hierarchie[$aliment]['super-categorie'];
}

function getFilAriane($aliment) { // Renvoie le fil d'Ariane sous forme de tableau
    $chemin = [$aliment]; // chemin est un 
    $courant = $aliment; // Variable pour remonter la hiérarchie

    while (true) { // On force la boucle, on sortira avec un break ou 
        $parents = getSuperCategories($courant); // si pas de parent $parents sera vide
        if (empty($parents)) break;
        $courant = $parents[0]; // Si on a plusieurs parents, on prend le premier car on ne peut afficher qu'un seul chemin
        $chemin[] = $courant; 
    }
    return array_reverse($chemin); // on inverse le tableau pour avoir la bonne ordre car quand on  a ajoute 
}

function getDescendants($aliment) {
    global $Hierarchie;

    // Toujours inclure l'aliment lui-même
    $liste = array($aliment);

    if (!isset($Hierarchie[$aliment]['sous-categorie'])) {
        return $liste;
    }

    foreach ($Hierarchie[$aliment]['sous-categorie'] as $sc) {
        $liste = array_merge($liste, getDescendants($sc));
    }

    return $liste;
}

function getRecettesParAliment($aliment) {
    global $Recettes;

    $descendants = getDescendants($aliment);
    $resultats = array();

    foreach ($Recettes as $id => $recette) {
        // Si l'aliment OU un de ses descendants apparaît dans l'index
        if (count(array_intersect($descendants, $recette['index'])) > 0) {
            $resultats[$id] = $recette;
        }
    }

    return $resultats;
}

function afficherRecetteSynth($id) {
    global $Recettes;

    $r = $Recettes[$id];

    echo "<div style='width:200px; border:1px solid #ccc; padding:10px; position:relative;'>";

    // Charger les favoris actuels
    $favoris = chargerFavoris();
    // Vérifier si cette recette est en favoris
    $estFavori = in_array($id, chargerFavoris());

    // Déterminer le symbole du cœur
    $coeur = $estFavori ? "❤️" : "🤍";

    // Coeur cliquable en haut à droite
    echo "<a href='toggleFavori.php?id=$id'
            style='position:absolute; top:5px; right:5px; 
            font-size:22px; text-decoration:none;'>
            $coeur
        </a>";

    // Titre + lien
    echo "<h3><a href='recette.php?id=$id'>{$r['titre']}</a></h3>";

    // Photo
    $photo = ucfirst(strtolower(str_replace(' ', '_', $r['titre']))) . ".jpg";
    if (!file_exists("Photos/$photo")) {
        $photo = "default.jpg";
    }
    echo "<img src='Photos/$photo' width='100'><br>";

    // Index
    echo "<ul>";
    foreach ($r['index'] as $ing) {
        echo "<li>$ing</li>";
    }
    echo "</ul>";

    echo "</div>";
}
?>
