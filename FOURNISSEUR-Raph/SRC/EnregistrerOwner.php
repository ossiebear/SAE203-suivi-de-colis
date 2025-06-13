<?php
require_once 'fonctionsConnexion.php';
require_once 'fonctionsBDD.php';
require_once '../../DATA/DATABASE/CONFIG/config.php';

$conn=connexionBDD('../../DATA/DATABASE/CONFIG/config.php');

// Récupérer les données du formulaire
$OwnerName = $_POST["OwnerName"];
$OwnerFirstname = $_POST["OwnerFirstname"];
$emailAddressClient = $_POST["emailAddress"];
$phoneNumberClient = $_POST["phoneNumber"];
$passwordEncryptClient = $_POST["password"];

try {
    // Insérer le client d'abord
    $idOwner = enregistreClient($OwnerName, $OwnerFirstname, $emailAddressOwner, $phoneNumberOwner, $passwordEncryptOwner, $conn);
    if ($idOwner) {
        echo'<h1>Owner enregistré avec succès</h1>';
        echo'<p>ID du Owner est ' . $idOwner . '</p>';
    }

} catch (Exception $e) {
    echo '<h1>Erreur lors de l\'enregistrement</h1>';
    echo '<p>' . $e->getMessage() . '</p>';
}

deconnexionBDD($conn);
?>