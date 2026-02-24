<?php
    require_once 'utilisateurs.php';

    session_start();

    if (!isset($_SESSION['login'])) {
        header("Location: index.php");
        exit;
    }

    $login = $_SESSION['login'];
    $utilisateur = trouverUtilisateurParLogin($login);
    $messageErreur = "";

    // Traitement de la modification du profil
    if (isset($_POST['action']) && $_POST['action'] === 'modifier_profil') {
        $erreurs = [];

        // Appel de ta vraie fonction pour modifier le profil
        if (modifierProfil($login, $_POST, $erreurs)) {

            // Mise à jour réussie
            $messageSucces = "Profil mis à jour avec succès.";

            // On recharge les infos mises à jour
            $utilisateur = trouverUtilisateurParLogin($login);

        } else {

            // Affiche les erreurs renvoyées par modifierProfil()
            if (!empty($erreurs)) {
                foreach ($erreurs as $err) {
                    $messageErreur .= $err . " ";
                }
            } else {
                $messageErreur = "Erreur lors de la mise à jour.";
            }
        }
    }

    //Boutton pour supprimer le compte
    if (isset($_POST['action']) && $_POST['action'] === 'supprimer_compte') {

        $utilisateurs = chargerUtilisateurs();
        $nouveaux = [];

        foreach ($utilisateurs as $u) {
            if ($u['login'] != $login) {
                $nouveaux[] = $u;
            }
        }

        if (sauverUtilisateurs($nouveaux)) {
            Deconnexion();
            header("Location: index.php");
            exit;
        } else {
            $messageErreur = "Erreur lors de la suppression du compte.";
        }   
    }

    $zone = isset($_GET['zone']) ? $_GET['zone'] : '';
    $aliment = isset($_GET['aliment']) ? $_GET['aliment'] : 'Aliment';
    $estConnecte = isset($_SESSION['login']);
    $requeteRecherche = isset($_GET['recherche']) ? $_GET['recherche'] : '';
    $modeRecherche = !empty($requeteRecherche);

    require_once 'header.php';
    afficherHeader($zone, $aliment, $requeteRecherche);
?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Mon profil</title>
    </head>
    <body>

    
    <hr>

    <h1>Mon Profil</h1>

    <?php 
    if ($messageErreur != "") {
        echo "<p style='color:red'>$messageErreur</p>";
    }
    ?>

    <form method="post" action="profil.php" id="formProfil" style="background:lightgray; padding:15px; width:400px;">

        <input type="hidden" name="action" value="modifier_profil">

        <p><b>Nom :</b><br>
            <input type="text" name="nom" value="<?= htmlspecialchars($utilisateur['nom']) ?>"
                style="width:100%; padding:5px;">
        </p>

        <p><b>Prénom :</b><br>
            <input type="text" name="prenom" value="<?= htmlspecialchars($utilisateur['prenom']) ?>"
                style="width:100%; padding:5px;">
        </p>

        <p><b>Sexe :</b><br>
            <select name="sexe" style="width:100%; padding:5px;">
                <option value=""></option>
                <option value="H" <?= $utilisateur['sexe']=='H' ? 'selected':'' ?>>Homme</option>
                <option value="F" <?= $utilisateur['sexe']=='F' ? 'selected':'' ?>>Femme</option>
            </select>
        </p>

        <p><b>Date de naissance :</b><br>
            <input type="date" name="date_naissance" value="<?= $utilisateur['date_naissance'] ?>"
                style="width:100%; padding:5px;">
        </p>

        <!-- Bouton masqué tant que rien n'a changé -->
        <button id="btnSave" type="submit"
                style="margin-top:10px; padding:10px; background:#4CAF50; color:white; border:none; display:none;">
            Mettre à jour
        </button>

    </form>


    <br>

    <form method="post" action="profil.php" onsubmit="return confirm('Supprimer définitivement votre compte ?');">

        <input type="hidden" name="action" value="supprimer_compte">

        <button type="submit"
                style="padding:10px; background:red; color:white; border:none;">
            Supprimer mon compte
        </button>
    </form>

    <br>

    <form action="index.php" method="get">
        <button type="submit"
                style="padding:10px 15px; font-size:16px; background:white; border:2px solid #999;">
            Retour à l'accueil
        </button>
    </form>

    <script>
    document.querySelectorAll('#formProfil input, #formProfil select').forEach(el => {
        el.addEventListener('change', () => {
            document.getElementById('btnSave').style.display = 'block';
        });
    });
    </script>

    </body>
</html>

