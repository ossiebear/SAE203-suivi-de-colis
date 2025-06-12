<?php
require_once '../../SRC/fonctionsConnexion.php';
require_once '../../SRC/fonctionsBDD.php';
require_once '../../../DATA/DATABASE/CONFIG/config.php';

$conn=connexionBDD('../../../DATA/DATABASE/CONFIG/config.php');


// Récupération de l'ID client (par exemple via GET)
$clientId = isset($_GET['client_id']) ? intval($_GET['client_id']) : 0;

if ($clientId <= 0) {
    die("Aucun client sélectionné.");
}

// Requête pour récupérer les colis du client
$sql = "SELECT * FROM packages WHERE client_id = :client_id ORDER BY package_creation_date DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute([':client_id' => $clientId]);
$packages = $stmt->fetchAll();

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Colis du client</title>
</head>
<body>
    <h1>Liste des colis du client #<?php echo htmlspecialchars($clientId); ?></h1>

    <?php if (empty($packages)): ?>
        <p>Aucun colis trouvé pour ce client.</p>
    <?php else: ?>
        <table border="1" cellpadding="5">
            <tr>
                <th>ID</th>
                <th>Statut</th>
                <th>Poids</th>
                <th>Taille</th>
                <th>Numéro de suivi</th>
                <th>Adresse de livraison</th>
                <th>Date de livraison</th>
                <th>Date de création</th>
            </tr>
            <?php foreach ($packages as $package): ?>
                <tr>
                    <td><?php echo htmlspecialchars($package['id']); ?></td>
                    <td><?php echo htmlspecialchars($package['package_status']); ?></td>
                    <td><?php echo htmlspecialchars($package['package_weight']); ?></td>
                    <td><?php echo htmlspecialchars($package['package_size']); ?></td>
                    <td><?php echo htmlspecialchars($package['package_tracking_number']); ?></td>
                    <td><?php echo htmlspecialchars($package['package_delivery_address']); ?></td>
                    <td><?php echo htmlspecialchars($package['package_delivery_date']); ?></td>
                    <td><?php echo htmlspecialchars($package['package_creation_date']); ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>
</body>
</html>
