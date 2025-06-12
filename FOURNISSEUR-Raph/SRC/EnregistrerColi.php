<?php
require_once 'fonctionsConnexion.php';
require_once 'fonctionsBDD.php';
require_once '../../../DATA/DATABASE/CONFIG/config.php';

$conn=connexionBDD('../../../DATA/DATABASE/CONFIG/config.php');

// Récupérer les données du formulaire
$clientName = $_POST["shopName"];
$clientFirstname = $_POST["shopFirstname"];
$emailAddressClient = $_POST["emailAddress"];
$phoneNumberClient = $_POST["phoneNumber"];
$passwordEncryptClient = $_POST["password"];
$destinationAddress = $_POST["destinationAddress"];

try {
    // Insérer le client d'abord
    $idMagasin = enregistreMagasin($clientName, $clientFirstname, $emailAddressClient, $phoneNumberClient $passwordEncryptClient ,$destinationAddress, $conn);
    if ($idMagasin) {
        echo'<h1>Le magasin à été enregistré avec succès</h1>';
        echo'<p>ID du Magasin est ' . $idMagasin . '</p>';
    }

} catch (Exception $e) {
    echo '<h1>Erreur lors de l\'enregistrement</h1>';
    echo '<p>' . $e->getMessage() . '</p>';
}

deconnexionBDD($conn);
?>