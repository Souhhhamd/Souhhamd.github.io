<?php
require_once 'utilisateurs.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$action = isset($_POST['action']) ? $_POST['action'] :
          (isset($_GET['action']) ? $_GET['action'] : '');

$erreurs = [];
$message = '';

/*Quand on clique sur connexion,
Le formulaire envoie :
action = "connexion" avec login et mdp, on récupère ces valeurs, puis on appelle Connexion($login, $mdp) qui;
vérifie si le login existe et vérifie si le mot de passe correspond.
Si oui démarre la session et memorise le login.

Si la connexion réussit :
    on redirige vers index.php pour afficher la page principale avec la zone"connecté" (login + bouton Profil + bouton Déconnexion).
Sinon  on garde $message avec "Login ou mot de passe incorrect."*/
if ($action === 'connexion' && $_SERVER['REQUEST_METHOD'] === 'POST') { 

    $login = isset($_POST['login']) ? $_POST['login'] : '';
    $mdp = isset($_POST['mot_de_passe']) ? $_POST['mot_de_passe'] : '';

    if (Connexion($login, $mdp)) {

        // À la connexion : ON REMPLACE les favoris
        require_once "favoris.php";
        $_SESSION['favoris'] = chargerFavoris(); 

        header('Location: index.php');
        exit;
    } 
    else {
        $message = "Login ou mot de passe incorrect.";
    }
}




/* Quand on clique sur deconnexion :
   On fusionne les favoris du compte avec ceux de la session,
   puis on détruit la session et redirige.
*/
if ($action === 'deconnexion' && $_SERVER['REQUEST_METHOD'] === 'POST') {

    require_once "favoris.php";

    // Récupérer les favoris du compte AVANT de supprimer le login
    $favorisCompte = chargerFavoris();

    // Nettoyer uniquement le login
    unset($_SESSION['login']);

    // Remplacer les favoris de session par ceux du compte
    $_SESSION['favoris'] = $favorisCompte;

    header('Location: index.php');
    exit;
}



/*Quand l'utilisateur clique sur s'inscire et créé son compte :
On envoi tout les champs a inscireUtilisateur et on vérifie ceux-ci?
Si il y a un problème on ajoute l'erreur a un tableau et on ne redirige pas + on met un message d'erreur
Sinon si tout est ok on l'ajoute a utlisateur.txt et on le connecte automatiquement a la session 
*/
if ($action === 'inscription_traitement' && $_SERVER['REQUEST_METHOD'] === 'POST') {

    $erreurs = [];   // Très important : réinitialiser ici

    if (inscrireUtilisateur($_POST, $erreurs)) {

        // Inscription réussie → on renvoie vers index (déjà connecté automatiquement)
        header('Location: index.php');
        exit;

    } else {
        // On reste SUR la page d'inscription → on affiche les erreurs juste après
        $messageErreur = "Erreur dans le formulaire.";
        $action = "inscription";   // Force l'affichage du formulaire d'inscription
    }
}




?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Authentification</title>
</head>
<body>
<!-- pour mettre la zone de connexion toute à droite on ajoute autour un div avec style display flex et justify content flex end comme ci dessous-->
<div style="display:flex; justify-content:flex-end; border-bottom:1px solid #ccc; padding-bottom:10px; margin-bottom:10px;">
    <?php afficherZoneConnexion();?>
</div>
<hr>

<?php if ($action == 'inscription') {?>

    <h1>Inscription</h1>

    <?php
        if (!empty($erreurs)) {
        echo "<div style='color:red; background:#fee; padding:10px; border:1px solid red; margin-bottom:10px;'>";
        foreach ($erreurs as $e) {
            echo "<p>$e</p>";
        }
        echo "</div>";
        }
    ?>

    <form method="post" action="auth.php?action=connexion">
        <input type="hidden" name="action" value="inscription_traitement">

       Login* : 
        <input type="text" name="login" value="<?= isset($_POST['login']) ? htmlspecialchars($_POST['login']) : '' ?>">

        <br><br>

        Mot de passe* :
        <input type="password" name="mot_de_passe">
        <br><br>

        Nom :
        <input type="text" name="nom" value ="<?= isset($_POST['nom']) ? htmlspecialchars($_POST['nom']) : '' ?>">
        <br><br>

        Prénom :
        <input type="text" name="prenom" value ="<?= isset($_POST['prenom']) ? htmlspecialchars($_POST['prenom']) : '' ?>">
        <br><br>

        Sexe :
        <select name="sexe">
            <option value="" <?= (isset($_POST['sexe']) && $_POST['sexe'] == '') ? 'selected' : '' ?>></option>
            <option value="H" <?= (isset($_POST['sexe']) && $_POST['sexe'] == 'H') ? 'selected' : '' ?>>Homme</option>
            <option value="F" <?= (isset($_POST['sexe']) && $_POST['sexe'] == 'F') ? 'selected' : '' ?>>Femme</option>
        </select>
        <br><br>

        Date de naissance :
        <input type="date" name="date_naissance" value ="<?= isset($_POST['date_naissance']) ? htmlspecialchars($_POST['date_naissance']) : '' ?>">
        <br><br>

        <button type="submit">Créer mon compte</button>
    </form>


        <?php } else { ?>
        <?php 
            if (!empty($message)) {
                echo "<p style='color:red'>$message</p>";
            }
            ?>

            <?php if (!isset($_SESSION['login'])) { ?>

                <p>Utilisez la zone en haut pour vous connecter.</p>
                <p><a href="auth.php?action=inscription">Créer un compte</a></p>

            <?php }

        } ?>
</body>
</html>



