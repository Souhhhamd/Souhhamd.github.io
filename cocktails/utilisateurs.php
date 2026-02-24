<?php
// Utilisateurs.php ,Fonctions de gestion des utilisateurs (stockés dans utilisateurs.txt)


//// Fonction propres aux Utilisateur ////

/*Avec Chemin on retourne le bon chemin du fichier correspondant dans utilisateur.txt*/
function cheminFichierUtilisateurs() {
    //__DIR__ retourne le dossier courant dans lequel se trouve le fichier utilisateurs.php
    return __DIR__ . '/utilisateurs.txt';
}


/* ChargerUtilisateurs cette fonction permet de lire le fichier utilisateur 
Elle retourne un tableau contenant tout les utilisateurs */
function chargerUtilisateurs() {
    $fichier = cheminFichierUtilisateurs();
    $utilisateurs = [];

    if (!file_exists($fichier)) {
        return [];
    }

    //on lit tout le fichier en ignorant les "\n" avec FILE_IGNORE_NEW_LINES et les lignes vides avec FILE_SKIP_EMPTY_LINES
    $lignes = file($fichier, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lignes as $ligne) {
        //Comme le fichier se lit comme ceci : Coulisteph|hash|Courtio... une fois les 6 partie lu ont les attributs a utilisateur qu'on retourne
        //Si il n'y a pas 6 partie alors ont va ala ligne suivante...
        $parts = explode('|', $ligne);
        if (count($parts) < 6) {
            continue; 
        }
        list($login, $hash, $nom, $prenom, $sexe, $dateNaissance) = $parts;
        $utilisateurs[] = [
            'login'          => $login,
            'password_hash'  => $hash,
            'nom'            => $nom,
            'prenom'         => $prenom,
            'sexe'           => $sexe,
            'date_naissance' => $dateNaissance,
        ];
    }

    return $utilisateurs;
}

/* SauverUtilisateurs Permet de sauvegarder un utilisateur dans le fichiers utilisateur.txt
Elle retourne un booleen pour dire si elle est fonctionnelle*/
function sauverUtilisateurs(array $utilisateurs) {
    $fichier = cheminFichierUtilisateurs();
    //Texte a écrire dans utilisateur.txt
    $contenu = '';

    //On parcour le tableau utilisateur et on inclut a chaque tout un utilisateur
    foreach ($utilisateurs as $u) {
        $ligne = $u['login'] . '|' .
                 $u['password_hash'] . '|' .
                 $u['nom'] . '|' .
                 $u['prenom'] . '|' .
                 $u['sexe'] . '|' .
                 $u['date_naissance'];

                 //Ajoute a contenu la l'utilisateur qu'on vient de construire avec un saut a la ligne et ainsi de suite
        $contenu .= $ligne . PHP_EOL;
    }
    //Et on retourne le texte de contenu dans utilsateur.txt(fichier) en testant si l'action s'est bien opéré
    return file_put_contents($fichier, $contenu) !== false;
}

/* TrouverUtilisateurParLogin permet comme son nom l'indique de trouver l'utilisateur en fonction de son login*/
function trouverUtilisateurParLogin($login) {
    $utilisateurs = chargerUtilisateurs();
    foreach ($utilisateurs as $u) {
        if ($u['login'] === $login) {
            return $u;
        }
    }
    return null;
}

//// Fonctions de vérification de validation /////

/* LoginValidate permet de vérifier si le login est conforme aux règles */
function loginValide($login, &$erreur = null) {

    $regex = preg_match('/^[A-Za-z0-9]+$/', $login);

    if ($regex == 1) {
        return true;
    } else {
        $erreur = "Le login doit contenir uniquement des lettres non accentuées et des chiffres.";
        return false;
    }
}

/* HasherMDP reçoit un mot de passe et renvoi une chaine de caractère sécurisé(hashé) pour qu'on ne le voit pas */
function hasherMotDePasse($mdp) {
    return password_hash($mdp, PASSWORD_DEFAULT);
}

/* VérifierMDP compare le mot de passe passé en entré et la version hashé, retoune vrai si correspondance*/
function verifierMotDePasse($mdp, $hash) {
    return password_verify($mdp, $hash);
}


/* Vérifie la validité du nom et du prénom*/
function nomPrenomValide($texte) {
    
     $regex =  preg_match('/^\p{L}+(?:[ ]+\p{L}+|[-\']\p{L}+)*$/u', $texte);
    if ($regex == 1) {
        return true;
    } else {
        $erreur = "Le nom ou le prenom n'est pas valide.";
        return false;
    }
}

/* Vérifie la validité de la date naissance*/
function dateNaissanceValide($date) {

    // Champ facultatif
    if ($date === null || trim($date) === '') {
        return true;
    }

    // Vérifier le format YYYY-MM-DD
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        return false;
    }

    // Découper la date
    list($y, $m, $d) = explode('-', $date);

    // Vérifier que la date existe
    if (!checkdate((int)$m, (int)$d, (int)$y)) {
        return false;
    }

    // Vérifier âge minimum (18 ans)
    $today = new DateTime();
    $birth = new DateTime($date);

    $age = $today->diff($birth)->y;

    return $age >= 18;
}


//// Fonctions d'inscripions (Bouton s'inscrire) ////
function inscrireUtilisateur(array $donnees, array &$erreurs) {
    $utilisateurs = chargerUtilisateurs();

    $login = trim($donnees['login']);
    $mdp = $donnees['mot_de_passe'];
    $nom = trim($donnees['nom']);
    $prenom = trim($donnees['prenom']);
    $sexe = $donnees['sexe'];
    $dateNaissance = $donnees['date_naissance'];

    // --- VALIDATION LOGIN ---
    if ($login === '' || !loginValide($login)) {
        $erreurs['login'] = "Login invalide (lettres non accentuées et/ou chiffres).";
    } else {
        foreach ($utilisateurs as $u) {
            if ($u['login'] === $login) {
                $erreurs['login'] = "Ce login est déjà utilisé.";
                break;
            }
        }
    }

    // --- VALIDATION MOT DE PASSE ---
    if ($mdp === '') {
        $erreurs['mot_de_passe'] = "Mot de passe obligatoire.";
    }

    // --- VALIDATION DATE ---
    if (!dateNaissanceValide($dateNaissance)) {
        $erreurs['date_naissance'] = "Date invalide.";
    }

    // --- VALIDATION NOM ---
    if ($nom !== '' && !nomPrenomValide($nom)) {
        $erreurs['nom'] = "Nom invalide.";
    }

    // --- VALIDATION PRÉNOM ---
    if ($prenom !== '' && !nomPrenomValide($prenom)) {
        $erreurs['prenom'] = "Prénom invalide.";
    }

    // === STOP SI ERREURS ===
    if (!empty($erreurs)) {
        return false;
    }

    // --- CONSTRUCTION UTILISATEUR ---
    $nouvelUtilisateur = [
        'login'          => $login,
        'password_hash'  => hasherMotDePasse($mdp),
        'nom'            => $nom,
        'prenom'         => $prenom,
        'sexe'           => $sexe,
        'date_naissance' => $dateNaissance,
    ];

    $utilisateurs[] = $nouvelUtilisateur;

    // --- ENREGISTREMENT ---
    if (!sauverUtilisateurs($utilisateurs)) {
        $erreurs['global'] = "Erreur lors de l'enregistrement du compte.";
        return false;
    }

    // Connexion automatique
    $_SESSION['login'] = $login;
    return true;
}


//// Fonction de connexion / deconnexion ////

/*Bouton connexion */
function Connexion($login, $mdp) {

    $user = trouverUtilisateurParLogin($login);
    if (!$user) return false;

    if (!verifierMotDePasse($mdp, $user['password_hash'])) return false;

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Mémoriser le login dans la session
    $_SESSION['login'] = $login;

    return true;
}



/* Bouton Deconnexion*/
function Deconnexion() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    $_SESSION = [];     
    session_destroy(); 
}


//// Fonction pour modifier le profil ////

/* Fonction permettant d*/
function modifierProfil($login, $donnees, &$erreurs) {
    $utilisateurs = chargerUtilisateurs();
    
    $nom = trim($donnees['nom']); 
    $prenom = trim($donnees['prenom']); 
    $sexe = $donnees['sexe'];
    $dateNaissance = $donnees['date_naissance'];
    if ($nom != '' && !nomPrenomValide($nom)) {
        $erreurs['nom'] = "Nom invalide.";
    }
    if ($prenom != '' && !nomPrenomValide($prenom)) {
        $erreurs['prenom'] = "Prénom invalide.";
    }
    if (!dateNaissanceValide($dateNaissance)) {
        $erreurs['date_naissance'] = "Date invalide.";
    }
    //Si il y a une erreur sur le profil on ne modifie rien
    if (!empty($erreurs)) {
        return false;
    }
    foreach ($utilisateurs as &$u) {
        if (trim($u['login']) === trim($login)) {
            $u['nom'] = $nom;
            $u['prenom'] = $prenom;
            $u['sexe'] = $sexe;
            $u['date_naissance'] = $dateNaissance;
        }
    }
    //On retourne $utilisateur dans utilisateur.txt et on retourne si cela fonctionne
    return sauverUtilisateurs($utilisateurs);
}


//// Zone de connexion ////

/*  Ensemble HTML/PHP permettant d’afficher la zone de connexion en haut à droite de la page web.
La fonction afficherZoneConnexion() est appelée dans la page principale.
Elle agit comme un intermédiaire entre l’interface utilisateur et le systèmede connexion.
 -Si l’utilisateur pas connecté
elle affiche le formulaire de connexion (login / mot de passe) , le bouton connexion ainsi qu’un lien vers la page d’inscription.
 -Si l’utilisateur est connecté : elle affiche son login, un lien vers son profil et un bouton de déconnexion.
*/
function afficherZoneConnexion() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (isset($_SESSION['login'])) {
        
        // ========== UTILISATEUR CONNECTÉ ==========
        $login = htmlspecialchars($_SESSION['login'], ENT_QUOTES, 'UTF-8');
        
        // pour mettre à droite on fait dans le style du div : float:right;
        echo '<div style="display:flex; align-items:center; gap:10px;">';
        
        echo '<strong>' . $login . '</strong>';
        
        // Bouton Profil
        echo '<form method="get" action="profil.php" style="display:inline;">';
        echo '<button type="submit" 
                      style="padding:8px 15px; font-size:18px; background-color:white; border:1px solid; cursor:pointer;">
                Profil
              </button>';
        echo '</form>';
        
        // Bouton Déconnexion
        echo '<form method="post" action="auth.php" style="display:inline;">';
        echo '<input type="hidden" name="action" value="deconnexion">';
        echo '<button type="submit" 
                      style="padding:8px 15px; font-size:18px; background-color:white; border:1px solid; cursor:pointer;">
                Se déconnecter
              </button>';
        echo '</form>';
        
        echo '</div>';
        
    } else {
        // ========== UTILISATEUR NON CONNECTÉ ==========

        // COLONNE DROITE : FORMULAIRE DE CONNEXION
        echo '<div style="display:flex; justify-content:flex-end; align-items:center; gap:15px;">';
        echo '    <form method="post" action="auth.php?action=connexion" style="display:flex; align-items:center; gap:10px;">';
        
        echo '        <label style="font-weight:bold; margin-right: 5px; white-space: nowrap;">Login :</label>';
        echo '        <input type="text" name="login" style="padding:8px; font-size:16px;">';

        echo '        <label style="font-weight:bold; margin-right: 5px; white-space: nowrap;">Mot de passe :</label>';
        echo '        <input type="password" name="mot_de_passe" style="padding:8px; font-size:16px;">';

        echo '        <button type="submit" style="padding:8px 15px; font-size:18px; background-color:white; border:1px solid; cursor:pointer;">';
        echo '            Connexion';
        echo '        </button>';
        echo '    </form>';

        // FORMULAIRE D’INSCRIPTION
        echo '    <form method="get" action="auth.php" style="margin:0;">';
        echo '        <input type="hidden" name="action" value="inscription">';
        echo '        <button type="submit" style="padding:8px 15px; font-size:18px; background-color:white; border:1px solid; cursor:pointer;">';
        echo '            S\'inscrire';
        echo '        </button>';
        echo '    </form>';
        echo '</div>';
    }
}

