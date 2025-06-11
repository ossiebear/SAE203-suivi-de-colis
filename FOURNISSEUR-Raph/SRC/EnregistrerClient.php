<?php
require_once 'SRC/fonctionsConnexion.php';
require_once 'fonctionsBDD.php';
require_once '../../DATA/config.php';

$conn=connexionBDD('../../DATA/config.php');

// Récupérer les données du formulaire
$clientName = $_POST["clientName"];
$clientFirstname = $_POST["clientFirstname"];
$emailAddressClient = $_POST["emailAddress"];
$phoneNumberClient = $_POST["phoneNumber"];
$passwordEncryptClient = $_POST["password"];
$destinationAddress = $_POST["destinationAddress"];

try {
    // Insérer le client d'abord
    $idClient = enregistreClient($clientName, $clientFirstname, $emailAddressClient, $phoneNumberClient $passwordEncryptClient ,$destinationAddress, date('Y-m-d'), $conn);
    if ($idClient) {
        echo'<h1>Client enregistré avec succès</h1>';
        echo'<p>ID du client est ' . $idClient . '</p>';
    }

}

deconnexionBDD($conn);
?>