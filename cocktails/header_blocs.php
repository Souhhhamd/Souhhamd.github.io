<?php
// Fonctions pour générer les blocs du header

function afficherBlocNavigationRecettes() {
    ?>
    <div style="display:flex; align-items:center; gap:10px;">
        
        <form method="get" action="index.php">
            <button type="submit"
                    style="padding:8px 15px; font-size:18px; background-color:white; border:1px solid;">
                Navigation
            </button>
        </form>

        <form method="get" action="mes_favoris.php">
            <button type="submit"
                    style="padding:8px 15px; font-size:18px; background-color:white; border:1px solid;">
                Recettes ❤️
            </button>
        </form>

    </div>
    <?php
}

function afficherBlocRecherche($requeteRecherche) {
    ?>
    <div style="display:flex; justify-content:center;">
        
        <form method="get" action="index.php" 
              style="display:flex; align-items:center; gap:10px;">
            
            <label for="recherche" style="font-weight:bold;">Recherche :</label>

            <input type="text"
                   name="recherche"
                   id="recherche"
                   value="<?= htmlspecialchars($requeteRecherche) ?>"
                   placeholder='Ex: "Jus de fruits" +Sel -Whisky'
                   style="width:300px; padding:8px; font-size:14px;">

            <button type="submit"
                    style="padding:5px; background-color:transparent; border:none; cursor:pointer;">
                <img src="Photos/loupe.jpg" alt="Rechercher" 
                     style="width:30px; height:30px;">
            </button>
        </form>

    </div>
    <?php
}

function afficherBlocConnexionComplet() {
    ?>
    <div style="display:flex; justify-content:flex-end; align-items:center; gap:15px;">
        
        <!-- Formulaire de connexion -->
        <form method="post" action="auth.php?action=connexion" 
              style="display:flex; align-items:center; gap:10px;">
            
            <label style="font-weight:bold; white-space:nowrap;">Login :</label>
            <input type="text" name="login" 
                   style="padding:8px; font-size:16px; width:150px;">

            <label style="font-weight:bold; white-space:nowrap;">Mot de passe :</label>
            <input type="password" name="mot_de_passe" 
                   style="padding:8px; font-size:16px; width:150px;">

            <button type="submit" 
                    style="padding:8px 15px; font-size:18px; background-color:white; 
                           border:1px solid; cursor:pointer;">
                Connexion
            </button>
        </form>

        <!-- Bouton S'inscrire -->
        <form method="get" action="auth.php" style="margin:0;">
            <input type="hidden" name="action" value="inscription">
            <button type="submit" 
                    style="padding:8px 15px; font-size:18px; background-color:white; 
                           border:1px solid; cursor:pointer;">
                S'inscrire
            </button>
        </form>

    </div>
    <?php
}

function afficherBlocBoutonZoneConnexion($aliment) {
    ?>
        <form method="get" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>">
            
            <!-- Conserver TOUS les paramètres GET existants -->
            <?php foreach ($_GET as $key => $value): ?>
                <?php if ($key !== 'zone'): // On écrase 'zone' avec la nouvelle valeur ?>
                    <input type="hidden" name="<?= htmlspecialchars($key) ?>" 
                        value="<?= htmlspecialchars($value) ?>">
                <?php endif; ?>
            <?php endforeach; ?>
            
            <!-- Nouveau paramètre zone -->
            <input type="hidden" name="zone" value="connexion">

            <button type="submit"
                    style="padding:8px 15px; font-size:18px; background-color:lightgray; 
                        border:2px solid gray;">
                Zone de connexion
            </button>
        </form>
    <?php
}