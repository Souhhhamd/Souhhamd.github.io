<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

/**
 * Donne le chemin du fichier favoris pour un login
 */
function fichierFavorisUser($login) {
    return "favoris_" . $login . ".txt";
}

/**
 * Charge les favoris (en session si pas connecté, sinon dans fichier TXT)
 */
function chargerFavoris() {

    // PAS CONNECTÉ → favoris en session
    if (!isset($_SESSION['login'])) {
        if (!isset($_SESSION['favoris'])) {
            $_SESSION['favoris'] = [];
        }
        return $_SESSION['favoris'];
    }

    // CONNECTÉ → favoris dans fichier texte
    $login = $_SESSION['login'];
    $fichier = fichierFavorisUser($login);

    if (!file_exists($fichier)) {
        return [];
    }

    $lignes = file($fichier, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    // Convertir en entiers
    return array_map('intval', $lignes);
}

/**
 * Sauvegarde les favoris dans fichier texte OU session selon connexion
 */
function sauvegarderFavoris($favoris) {

    // pas connecté → session
    if (!isset($_SESSION['login'])) {
        $_SESSION['favoris'] = $favoris;
        return;
    }

    // connecté → fichier.txt
    $login = $_SESSION['login'];
    $fichier = fichierFavorisUser($login);

    $contenu = "";
    foreach ($favoris as $id) {
        $contenu .= $id . PHP_EOL;
    }

    file_put_contents($fichier, $contenu);
}

/**
 * Ajout d’un favori
 */
function ajouterFavori($idRecette) {
    $favoris = chargerFavoris();

    if (!in_array($idRecette, $favoris)) {
        $favoris[] = $idRecette;
    }

    sauvegarderFavoris($favoris);
}

/**
 * Suppression
 */
function supprimerFavori($idRecette) {
    $favoris = chargerFavoris();

    $favoris = array_diff($favoris, [$idRecette]);
    $favoris = array_values($favoris);

    sauvegarderFavoris($favoris);
}
?>