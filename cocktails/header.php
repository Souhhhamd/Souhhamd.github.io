<?php
require_once "utilisateurs.php";
require_once "header_blocs.php";

function afficherHeader($zone, $aliment, $requeteRecherche) {

    $estConnecte = isset($_SESSION['login']);
    $modeConnexion = ($zone === 'connexion' && !$estConnecte);
?>
    <header>
        <div style="border-bottom:1px solid #ccc; padding-bottom:10px; margin-bottom:10px;">

            <?php if ($modeConnexion): ?>
                
                <!-- MODE CONNEXION : GRILLE 2 COLONNES -->
                <div style="display:grid; grid-template-columns: auto 1fr; 
                            align-items:center; gap:20px; padding:10px;">

                    <?php afficherBlocNavigationRecettes(); ?>
                    <?php afficherBlocConnexionComplet(); ?>

                </div>

            <?php else: ?>

                <!-- MODE NORMAL : GRILLE 3 COLONNES -->
                <div style="display:grid; grid-template-columns: auto 1fr auto; 
                            align-items:center; gap:20px; padding:10px;">

                    <?php afficherBlocNavigationRecettes(); ?>
                    <?php afficherBlocRecherche($requeteRecherche); ?>
                    
                    <div>
                        <?php
                        if ($estConnecte) {
                            afficherZoneConnexion();
                        } else {
                            afficherBlocBoutonZoneConnexion($aliment);
                        }
                        ?>
                    </div>

                </div>

            <?php endif; ?>

        </div>
    </header>
<?php
}