<?php
require_once '../../SRC/fonctionsConnexion.php';
require_once '../../SRC/fonctionsBDD.php';

$conn = connexionBDD('../../../DATA/DATABASE/CONFIG/config.php');

try {
    // Créer quelques clients de test
    $clientId1 = enregistreClient('Dupont', 'Jean', 'jean.dupont@email.com', '0123456789', 'password123', '123 Rue de la Paix, Paris', $conn);
    $clientId2 = enregistreClient('Martin', 'Marie', 'marie.martin@email.com', '0987654321', 'password456', '456 Avenue des Champs, Lyon', $conn);
    
    echo "<h2>Clients créés :</h2>";
    echo "<p>Client 1 ID: $clientId1</p>";
    echo "<p>Client 2 ID: $clientId2</p>";
    
    // Créer quelques colis de test
    $sqlPackage = "INSERT INTO packages (client_id, package_status, package_weight, package_size, package_tracking_number, package_delivery_address, package_creation_date) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sqlPackage);
    
    $stmt->execute([$clientId1, 'En transit', 2.5, 'Moyen', 'TR001234567', '123 Rue de la Paix, Paris', date('Y-m-d')]);
    $stmt->execute([$clientId2, 'Livré', 1.2, 'Petit', 'TR001234568', '456 Avenue des Champs, Lyon', date('Y-m-d')]);
    
    echo "<h2>Colis créés avec succès !</h2>";
    echo '<a href="index.php">Retour à la liste</a>';
    
} catch (Exception $e) {
    echo "<h2>Erreur : " . $e->getMessage() . "</h2>";
}

deconnexionBDD($conn);
?>