<?php
require_once 'SRC/fonctionsConnexion.php';
require_once 'fonctionsBDD.php';
require_once '../../DATA/config.php';

$conn=connexionBDD('../../DATA/config.php');

// Récupérer les données du formulaire
$clientName = $_POST["shopName"];
$clientFirstname = $_POST["shopFirstname"];
$emailAddressClient = $_POST["emailAddress"];
$phoneNumberClient = $_POST["phoneNumber"];
$passwordEncryptClient = $_POST["password"];
$destinationAddress = $_POST["destinationAddress"];

try {
    // Insérer le client d'abord
    $idMagasin = enregistreMagasin($clientName, $clientFirstname, $emailAddressClient, $phoneNumberClient $passwordEncryptClient ,$destinationAddress, date('Y-m-d'), $conn);
    if ($idMagasin) {
        echo'<h1>Le magasin à été enregistré avec succès</h1>';
        echo'<p>ID du Magasin est ' . $idMagasin . '</p>';
    }

}

deconnexionBDD($conn);
?>