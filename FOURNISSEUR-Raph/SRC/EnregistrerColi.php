<?php
require_once 'fonctionsConnexion.php';
require_once 'fonctionsBDD.php';
require_once '../../DATA/DATABASE/CONFIG/config.php';

$conn = connexionBDD('../../DATA/DATABASE/CONFIG/config.php');

// Récupérer les données du formulaire avec les bons noms
$itemName = $_POST["newItem"];
$quantity = $_POST["quantity"];
$destinationAddress = $_POST["destinationAddress"];
$deliveryDate = $_POST["deliveryDate"];
$clientName = $_POST["clientName"];
$clientFirstname = $_POST["clientFirstname"];

try {
    // Enregistrer le colis
    $result = enregistreColi($itemName, $quantity, $destinationAddress, $deliveryDate, $clientName, $clientFirstname, $conn);
    
    if ($result) {
        echo '<div class="container">';
        echo '<h1>Le colis a été enregistré avec succès</h1>';
        echo '<div class="success-info">';
        echo '<p><strong>ID du Colis :</strong> ' . $result['colis_id'] . '</p>';
        echo '<p><strong>ID du Client :</strong> ' . $result['client_id'] . '</p>';
        echo '<p><strong>Numéro de suivi :</strong> ' . $result['tracking_number'] . '</p>';
        echo '<p><strong>Article :</strong> ' . htmlspecialchars($itemName) . '</p>';
        echo '<p><strong>Quantité :</strong> ' . htmlspecialchars($quantity) . '</p>';
        echo '<p><strong>Client :</strong> ' . htmlspecialchars($clientFirstname . ' ' . $clientName) . '</p>';
        echo '<p><strong>Adresse de livraison :</strong> ' . htmlspecialchars($destinationAddress) . '</p>';
        echo '<p><strong>Date de livraison prévue :</strong> ' . htmlspecialchars($deliveryDate) . '</p>';
    }

} catch (Exception $e) {
    echo '<div class="container">';
    echo '<h1>Erreur lors de l\'enregistrement</h1>';
    echo '<div class="error">';
    echo '<p>' . $e->getMessage() . '</p>';
}
echo '<a href="../PUBLIC/HTML&PHP/injectionColi.html">Retour à la Page de création</a>';

deconnexionBDD($conn);
?>