<?php
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    require_once 'utilisateurs.php';
    require_once "favoris.php";
    require_once 'navigation.php';
    require_once 'recherche.php';

    // Zone demandée (via l'URL)
    $zone = isset($_GET['zone']) ? $_GET['zone'] : '';
    $aliment = isset($_GET['aliment']) ? $_GET['aliment'] : 'Aliment';
    
    // Gestion de la recherche
    $requeteRecherche = isset($_GET['recherche']) ? $_GET['recherche'] : '';
    $modeRecherche = !empty($requeteRecherche);

    require_once 'header.php';
    afficherHeader($zone, $aliment, $requeteRecherche);
?>

<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="UTF-8">
        <title>Accueil</title>
    </head>


    <body>
        
        <div style="display:flex; gap:30px; align-items:flex-start;">
            <?php if (!$modeRecherche) { ?>
                <fieldset style="width:260px; min-width:260px; padding:10px;">

                    <strong>Aliment courant :</strong><br><br>

                    <?php
                        $ariane = getFilAriane($aliment);
                        foreach ($ariane as $i => $elem) {
                            echo "<a href='index.php?aliment=".urlencode($elem)."'>$elem</a>";
                            if ($i < count($ariane)-1) echo " / ";
                        }
                    ?>

                    <br><br>

                    Sous-catégories :<br>

                    <?php
                        $sous = getSousCategories($aliment);
                        if (empty($sous)) {
                            echo "<em>Aucune sous-catégorie</em>";
                        } else {
                            echo "<ul style='margin:0; padding-left:15px;'>";
                            foreach ($sous as $sc) {
                                echo "<li><a href='index.php?aliment=".urlencode($sc)."'>$sc</a></li>";
                            }
                            echo "</ul>";
                        }
                    ?>
                </fieldset>
            <?php } ?>

            <fieldset>
                <h3>
                    <?= $modeRecherche ? "Résultats de recherche" : "Liste des cocktails" ?>
                </h3>

                <?php
                if ($modeRecherche) {

                    // === PHASE 1 : ANALYSE ===
                    $analyse = parse_query($requeteRecherche);

                    // Erreur syntaxe (quotes impaires)
                    if (!empty($analyse['erreur_syntaxe'])) {
                        echo "<p style='color:red; font-weight:bold;'>"
                            . htmlspecialchars($analyse['erreur_syntaxe'])
                            . "</p>";
                    } else {
                        
                        // Afficher l'analyse
                        echo "<div style='background-color:#f0f0f0; padding:15px; margin-bottom:20px; border-radius:5px;'>";
                        
                        // Aliments souhaités
                        if (!empty($analyse['souhaites'])) {
                            echo "<p><strong>Liste des aliments souhaités :</strong> " 
                                . htmlspecialchars(implode(', ', $analyse['souhaites'])) 
                                . "</p>";
                        }
                        
                        // Aliments non souhaités
                        if (!empty($analyse['non_souhaites'])) {
                            echo "<p><strong>Liste des aliments non souhaités :</strong> " 
                                . htmlspecialchars(implode(', ', $analyse['non_souhaites'])) 
                                . "</p>";
                        }
                        
                        // Éléments non reconnus
                        if (!empty($analyse['non_reconnus'])) {
                            echo "<p style='color:orange;'><strong>Éléments non reconnus dans la requête :</strong> " 
                                . htmlspecialchars(implode(', ', $analyse['non_reconnus'])) 
                                . "</p>";
                        }
                        
                        echo "</div>";

                        // === PHASE 2 : RECHERCHE ===
                        
                        // Vérifier si recherche possible
                        if (empty($analyse['souhaites']) && empty($analyse['non_souhaites'])) {
                            echo "<p style='color:red; font-weight:bold;'>Problème dans votre requête : recherche impossible</p>";
                        } else {
                            
                            $resultatsRecherche = search_recipes($analyse['souhaites'], $analyse['non_souhaites']);
                            
                            // Afficher compteurs
                            if ($resultatsRecherche['nb_exact'] > 0 || $resultatsRecherche['nb_partiel'] > 0) {
                                echo "<div style='background-color:#e7f3ff; padding:10px; margin-bottom:15px; border-radius:5px;'>";
            
                                if ($resultatsRecherche['nb_exact'] > 0) {
                                    echo "<p><strong>" . $resultatsRecherche['nb_exact'] 
                                        . "</strong> recette(s) satisfont entièrement la recherche </p>";
                                }
                                
                                // Afficher nb_partiel SEULEMENT si recherche approximative (≥2 critères)
                                if ($resultatsRecherche['total_criteres'] >= 2 && $resultatsRecherche['nb_partiel'] > 0) {
                                    echo "<p><strong>" . $resultatsRecherche['nb_partiel'] 
                                        . "</strong> recette(s) satisfont partiellement la recherche </p>";
                                }
                                
                                echo "</div>";
                            }
                            
                            // Afficher recettes
                            if (empty($resultatsRecherche['recettes'])) {
                                echo "<p>Aucune recette trouvée pour ces critères.</p>";
                            } else {
                                echo "<div style='display:flex; flex-wrap:wrap; gap:20px;'>";
                                foreach ($resultatsRecherche['recettes'] as $id => $res) {
                                    afficherRecetteAvecScore($id, $res['score']);
                                }
                                echo "</div>";
                            }
                        }
                    }

                } else {
                    // Mode normal (navigation par aliment) - INCHANGÉ
                    $recettes = getRecettesParAliment($aliment);
                    
                    if (empty($recettes)) {
                        echo "<p>Aucune recette trouvée pour cet aliment.</p>";
                    } else {
                        echo "<div style='display:flex; flex-wrap:wrap; gap:20px;'>";
                        foreach ($recettes as $id => $recette) {
                            afficherRecetteSynth($id);
                        }
                        echo "</div>";
                    }
                }
                ?>
            </fieldset>

        </div>

    </body>
</html>