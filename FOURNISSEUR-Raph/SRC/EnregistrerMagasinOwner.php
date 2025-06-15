<?php
require_once 'fonctionsConnexion.php';
require_once 'fonctionsBDD.php';
require_once '../../DATA/DATABASE/CONFIG/config.php';

$conn=connexionBDD('../../DATA/DATABASE/CONFIG/config.php');

// Récupérer les données du formulaire
$ownerName = $_POST["shopName"];
$ownerFirstname = $_POST["shopFirstname"];
$emailAddressClient = $_POST["emailAddress"];
$phoneNumberClient = $_POST["phoneNumber"];
$passwordEncryptClient = $_POST["password"];
$destinationAddress = $_POST["destinationAddress"];

try {
    // Insérer le client d'abord
    $idOwner = enregistreMagasinOwner($ownerName, $ownerFirstname, $emailAddressClient, $phoneNumberClient, $passwordEncryptClient ,$destinationAddress, $conn);
    if ($idOwner) {
        echo'<h1>Gérant enregistré avec succès</h1>';
        echo'<p>ID du Gérant est ' . $idOwner . '</p>';
    }

} catch (Exception $e) {
    echo '<h1>Erreur lors de l\'enregistrement</h1>';
    echo '<p>' . $e->getMessage() . '</p>';
}

echo '<a href="../PUBLIC/HTML&PHP/injectionMagasin.php"><p>Retour à la Page de création</p></a>';

deconnexionBDD($conn);
?>