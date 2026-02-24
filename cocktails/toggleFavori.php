<?php
session_start();
require_once "favoris.php";

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$id = intval($_GET['id']);

$favoris = chargerFavoris();

if (in_array($id, $favoris)) {
    supprimerFavori($id);
} else {
    ajouterFavori($id);
}

$redir = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'index.php';
header("Location: " . $redir);
exit;
?>