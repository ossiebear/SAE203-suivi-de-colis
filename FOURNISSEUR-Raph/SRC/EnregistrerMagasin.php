<?php
require_once 'fonctionsConnexion.php';
require_once 'fonctionsBDD.php';
require_once '../../DATA/DATABASE/CONFIG/config.php';

$conn=connexionBDD('../../DATA/DATABASE/CONFIG/config.php');

// Récupérer les données du formulaire
$magasinName = $_POST["shopName2"];
$categorieID = $_POST["P_idcategorie"];
$ownerID = $_POST["P_idgerant"];
$addressMagasin = $_POST["shopAddress"];
$villeLocation = $_POST["villeLocation"];
$codePostal = $_POST["codePostal"];
$pays = $_POST["pays"];

try {
    // Insérer le client d'abord
    $idMagasin = enregistreMagasin($magasinName, $ownerID, $addressMagasin, $villeLocation, $codePostal ,$pays, $conn);
    if ($idMagasin) {
        echo'<h1>Le magasin à été enregistré avec succès</h1>';
        echo'<p>ID du Magasin est ' . $idMagasin . '</p>';
    }

} catch (Exception $e) {
    echo '<h1>Erreur lors de l\'enregistrement</h1>';
    echo '<p>' . $e->getMessage() . '</p>';
}
?>