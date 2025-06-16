
<?php
require_once 'fonctionsConnexion.php';
require_once 'fonctionsBDD.php';
require_once '../../DATA/DATABASE/CONFIG/config.php';

$conn = connexionBDD('../../DATA/DATABASE/CONFIG/config.php');

// Récupérer les données du formulaire avec les bons noms
$itemName = $_POST["newItem"];
// Vérifier si quantity existe dans le formulaire, sinon utiliser 1 par défaut
$quantity = isset($_POST["quantity"]) ? $_POST["quantity"] : 1;
$destinationAddress = $_POST["destinationAddress"];
$deliveryDate = $_POST["deliveryDate"];
$clientName = $_POST["clientName"];
$clientFirstname = $_POST["clientFirstname"];

try {
    // Enregistrer le colis
    $result = enregistreColi($itemName, $destinationAddress, $deliveryDate, $clientName, $clientFirstname, $conn);
    
    if ($result) {
        echo '<h1>Le colis a été enregistré avec succès</h1>';
        echo '<p><strong>ID du Colis :</strong> ' . $result['colis_id'] . '</p>';
        echo '<p><strong>ID du Client :</strong> ' . $result['client_id'] . '</p>';
        echo '<p><strong>Numéro de suivi :</strong> ' . $result['tracking_number'] . '</p>';
        echo '<p><strong>Article :</strong> ' . htmlspecialchars($itemName) . '</p>';
        echo '<p><strong>Client :</strong> ' . htmlspecialchars($clientFirstname . ' ' . $clientName) . '</p>';
        echo '<p><strong>Adresse de livraison :</strong> ' . htmlspecialchars($destinationAddress) . '</p>';
        echo '<p><strong>Date de livraison prévue :</strong> ' . htmlspecialchars($deliveryDate) . '</p>';
    }

} catch (Exception $e) {
    echo '<h1>Erreur lors de l\'enregistrement</h1>';
    echo '<p>' . $e->getMessage() . '</p>';
}

echo '<a href="../PUBLIC/HTML&PHP/injectionColi.html">Retour à la Page de création</a>';

deconnexionBDD($conn);
?>