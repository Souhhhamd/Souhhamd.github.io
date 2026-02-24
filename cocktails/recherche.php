<?php
require_once 'Donnees.inc.php';
require_once 'navigation.php';

/**
 * Parse la requête et retourne analyse complète
 * @return array ['souhaites', 'non_souhaites', 'non_reconnus', 'erreur_syntaxe']
 */
function parse_query($requete) {
    global $Hierarchie;
    
    $resultat = [
        'souhaites' => [],
        'non_souhaites' => [],
        'non_reconnus' => [],
        'erreur_syntaxe' => ''
    ];
    
    // Vérifier parité des double-quotes
    if (substr_count($requete, '"') % 2 !== 0) {
        $resultat['erreur_syntaxe'] = 'Problème de syntaxe dans votre requête : nombre impair de double-quotes';
        return $resultat;
    }
    
    // Extraire contenus entre guillemets
    $entreGuillemets = [];
    $requeteModifiee = preg_replace_callback('/"([^"]+)"/', function($m) use (&$entreGuillemets) {
        $placeholder = "__PH_" . count($entreGuillemets) . "__";
        $entreGuillemets[$placeholder] = trim($m[1]);
        return $placeholder;
    }, $requete);
    
    // Séparer par espaces
    $mots = preg_split('/\s+/', trim($requeteModifiee), -1, PREG_SPLIT_NO_EMPTY);
    
    foreach ($mots as $mot) {
        // Restaurer placeholder
        if (isset($entreGuillemets[$mot])) {
            $mot = $entreGuillemets[$mot];
        }
        
        // Détecter préfixe +/-
        $estNonSouhaite = false;
        if (strlen($mot) > 1 && $mot[0] === '-') {
            $estNonSouhaite = true;
            $mot = substr($mot, 1);
        } elseif (strlen($mot) > 1 && $mot[0] === '+') {
            $mot = substr($mot, 1);
        }
        
        $mot = trim($mot);
        if (empty($mot)) continue;
        
        // Reconnaissance stricte (sensible casse/accents)
        if (isset($Hierarchie[$mot])) {
            if ($estNonSouhaite) {
                $resultat['non_souhaites'][] = $mot;
            } else {
                $resultat['souhaites'][] = $mot;
            }
        } else {
            $resultat['non_reconnus'][] = $mot;
        }
    }
    
    return $resultat;
}

/**
 * Recherche recettes : exact si 1 critère, approximatif si >=2
 * @return array ['recettes' => [...], 'nb_exact' => int, 'nb_partiel' => int]
 */
function search_recipes($souhaites, $non_souhaites) {
    global $Recettes;
    
    $totalCriteres = count($souhaites) + count($non_souhaites);
    
    if ($totalCriteres === 0) {
        return ['recettes' => [], 'nb_exact' => 0, 'nb_partiel' => 0];
    }
    
    $resultats = [];
    $nbExact = 0;
    $nbPartiel = 0;
    
    foreach ($Recettes as $id => $recette) {
        $criteresSatisfaits = 0;
        
        // Vérifier aliments souhaités
        foreach ($souhaites as $aliment) {
            $descendants = getDescendants($aliment);
            if (count(array_intersect($descendants, $recette['index'])) > 0) {
                $criteresSatisfaits++;
            }
        }
        
        // Vérifier aliments non souhaités
        foreach ($non_souhaites as $aliment) {
            $descendants = getDescendants($aliment);
            if (count(array_intersect($descendants, $recette['index'])) === 0) {
                $criteresSatisfaits++;
            }
        }
        
        // Calcul score
        if ($criteresSatisfaits > 0) {
            $score = round(($criteresSatisfaits / $totalCriteres) * 100);
            
            $resultats[$id] = [
                'recette' => $recette,
                'score' => $score
            ];
            
            if ($score == 100) {
                $nbExact++;
            } else {
                $nbPartiel++;
            }
        }
    }
    
    // Tri décroissant par score
    uasort($resultats, function($a, $b) {
        return $b['score'] - $a['score'];
    });
    
    return [
        'recettes' => $resultats,
        'nb_exact' => $nbExact,
        'nb_partiel' => $nbPartiel,
        'total_criteres' => $totalCriteres
    ];
}

/**
 * Affiche une recette avec son score
 */
function afficherRecetteAvecScore($id, $score) {
    global $Recettes;
    
    $r = $Recettes[$id];
    
    echo "<div style='width:200px; border:1px solid #ccc; padding:10px; position:relative;'>";
    
    // Score
    if ($score == 100) { $couleur = 'green';} else { $couleur = 'orange';}

    echo "<div style='position:absolute; top:5px; left:5px; 
                     background-color:$couleur; color:white; 
                     padding:3px 8px; font-weight:bold; border-radius:3px;'>
            $score%
          </div>";
    
    // Cœur
    $estFavori = in_array($id, chargerFavoris());
    $coeur = $estFavori ? "❤️" : "🤍";
    echo "<a href='toggleFavori.php?id=$id'
            style='position:absolute; top:5px; right:5px; 
            font-size:22px; text-decoration:none;'>
            $coeur
        </a>";
    
    // Titre
    echo "<h3 style='margin-top:30px;'><a href='recette.php?id=$id'>{$r['titre']}</a></h3>";
    
    // Photo
    $photo = ucfirst(strtolower(str_replace(' ', '_', $r['titre']))) . ".jpg";
    if (!file_exists("Photos/$photo")) {
        $photo = "default.jpg";
    }
    echo "<img src='Photos/$photo' width='100'><br>";
    
    // Index
    echo "<ul style='font-size:12px;'>";
    foreach ($r['index'] as $ing) {
        echo "<li>$ing</li>";
    }
    echo "</ul>";
    
    echo "</div>";
}
?>