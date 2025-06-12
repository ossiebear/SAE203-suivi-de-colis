<?php
require_once 'fonctionsConnexion.php';
require_once 'fonctionsBDD.php';
require_once '../../../DATA/DATABASE/CONFIG/config.php';

$conn=connexionBDD('../../../DATA/DATABASE/CONFIG/config.php');

// Récupérer les données du formulaire
$clientName = $_POST["clientName"];
$clientFirstname = $_POST["clientFirstname"];
$emailAddressClient = $_POST["emailAddress"];
$phoneNumberClient = $_POST["phoneNumber"];
$passwordEncryptClient = $_POST["password"];
$destinationAddress = $_POST["destinationAddress"];

try {
    // Insérer le client d'abord
    $idClient = enregistreClient($clientName, $clientFirstname, $emailAddressClient, $phoneNumberClient $passwordEncryptClient ,$destinationAddress, $conn);
    if ($idClient) {
        echo'<h1>Client enregistré avec succès</h1>';
        echo'<p>ID du client est ' . $idClient . '</p>';
    }
} catch (Exception $e) {
    echo '<h1>Erreur lors de l\'enregistrement</h1>';
    echo '<p>' . $e->getMessage() . '</p>';
}

deconnexionBDD($conn);
?>