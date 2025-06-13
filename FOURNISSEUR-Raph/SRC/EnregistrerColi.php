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
        echo '</div>';
        echo '<a href="../HTML/injectionColi.html" class="btn-back">Créer un autre colis</a>';
        echo '</div>';
    }

} catch (Exception $e) {
    echo '<div class="container">';
    echo '<h1>Erreur lors de l\'enregistrement</h1>';
    echo '<div class="error">';
    echo '<p>' . $e->getMessage() . '</p>';
    echo '</div>';
    echo '<a href="../HTML/injectionColi.html" class="btn-back">Retour au formulaire</a>';
    echo '</div>';
}

deconnexionBDD($conn);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Résultat - Création Colis</title>
    <link rel="stylesheet" href="../CSS/index.css">
    <style>
        .success-info {
            background: var(--bg-secondary);
            padding: 20px;
            border-radius: 8px;
            border: 1px solid var(--border-light);
            margin: 20px 0;
        }
        .success-info p {
            margin: 10px 0;
            color: var(--text-primary);
        }
        .btn-back {
            display: inline-block;
            background: var(--brand-primary);
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 4px;
            font-weight: 600;
            margin-top: 20px;
            transition: all 0.3s ease;
        }
        .btn-back:hover {
            background: #a00000;
            transform: translateY(-1px);
        }
    </style>
</head>
<body>
</body>
</html>