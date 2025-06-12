<?php
require_once '../../SRC/fonctionsConnexion.php';
require_once '../../SRC/fonctionsBDD.php';
require_once '../../../DATA/DATABASE/CONFIG/config.php';

$conn = connexionBDD('../../../DATA/DATABASE/CONFIG/config.php');

// Récupération du filtre client (optionnel)
$clientId = isset($_GET['client_id']) ? intval($_GET['client_id']) : 0;

// Requête pour récupérer tous les clients pour le menu déroulant
$sqlClients = "SELECT id, client_name, client_firstname FROM clients ORDER BY client_name, client_firstname";
$stmtClients = $conn->prepare($sqlClients);
$stmtClients->execute();
$clients = $stmtClients->fetchAll(PDO::FETCH_ASSOC);

// Requête pour récupérer les colis (tous ou filtrés par client)
if ($clientId > 0) {
    // Colis d'un client spécifique
    $sql = "SELECT p.*, c.client_name, c.client_firstname 
            FROM packages p 
            JOIN clients c ON p.client_id = c.id 
            WHERE p.client_id = :client_id 
            ORDER BY p.package_creation_date DESC";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':client_id' => $clientId]);
} else {
    // Tous les colis
    $sql = "SELECT p.*, c.client_name, c.client_firstname 
            FROM packages p 
            JOIN clients c ON p.client_id = c.id 
            ORDER BY p.package_creation_date DESC";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
}

$packages = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des colis</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .filter-section {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        select, button {
            padding: 8px 12px;
            margin: 5px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        button {
            background-color: #007bff;
            color: white;
            cursor: pointer;
        }
        button:hover {
            background-color: #0056b3;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        tr:hover {
            background-color: #f5f5f5;
        }
        .no-data {
            text-align: center;
            padding: 40px;
            color: #666;
        }
        .stats {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
        }
        .stat-card {
            background-color: #e9ecef;
            padding: 15px;
            border-radius: 5px;
            flex: 1;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Gestion des colis</h1>
        
        <!-- Section de filtrage -->
        <div class="filter-section">
            <h3>Filtrer par client</h3>
            <form method="GET" action="">
                <select name="client_id" onchange="this.form.submit()">
                    <option value="0">-- Tous les clients --</option>
                    <?php foreach ($clients as $client): ?>
                        <option value="<?php echo $client['id']; ?>" 
                                <?php echo ($clientId == $client['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($client['client_name'] . ' ' . $client['client_firstname']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit">Filtrer</button>
                <?php if ($clientId > 0): ?>
                    <a href="?client_id=0" style="text-decoration: none;">
                        <button type="button">Réinitialiser</button>
                    </a>
                <?php endif; ?>
            </form>
        </div>

        <!-- Statistiques -->
        <div class="stats">
            <div class="stat-card">
                <h4>Total colis</h4>
                <p><strong><?php echo count($packages); ?></strong></p>
            </div>
            <div class="stat-card">
                <h4>Clients actifs</h4>
                <p><strong><?php echo count($clients); ?></strong></p>
            </div>
            <?php if ($clientId > 0): ?>
                <div class="stat-card">
                    <h4>Client sélectionné</h4>
                    <p><strong>
                        <?php 
                        $selectedClient = array_filter($clients, function($c) use ($clientId) {
                            return $c['id'] == $clientId;
                        });
                        if (!empty($selectedClient)) {
                            $client = reset($selectedClient);
                            echo htmlspecialchars($client['client_name'] . ' ' . $client['client_firstname']);
                        }
                        ?>
                    </strong></p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Tableau des colis -->
        <?php if (empty($packages)): ?>
            <div class="no-data">
                <h3>Aucun colis trouvé</h3>
                <p><?php echo $clientId > 0 ? 'Ce client n\'a aucun colis.' : 'Aucun colis dans la base de données.'; ?></p>
            </div>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>ID Colis</th>
                        <th>Client</th>
                        <th>Statut</th>
                        <th>Poids (kg)</th>
                        <th>Taille</th>
                        <th>N° de suivi</th>
                        <th>Adresse de livraison</th>
                        <th>Date de livraison</th>
                        <th>Date de création</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($packages as $package): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($package['id']); ?></td>
                            <td>
                                <strong><?php echo htmlspecialchars($package['client_name'] . ' ' . $package['client_firstname']); ?></strong>
                            </td>
                            <td>
                                <span style="padding: 3px 8px; border-radius: 3px; font-size: 12px; 
                                      background-color: <?php 
                                          switch(strtolower($package['package_status'])) {
                                              case 'livré': echo '#d4edda; color: #155724;'; break;
                                              case 'en transit': echo '#fff3cd; color: #856404;'; break;
                                              case 'en attente': echo '#f8d7da; color: #721c24;'; break;
                                              default: echo '#e2e3e5; color: #383d41;';
                                          }
                                      ?>">
                                    <?php echo htmlspecialchars($package['package_status']); ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars($package['package_weight']); ?></td>
                            <td><?php echo htmlspecialchars($package['package_size']); ?></td>
                            <td><code><?php echo htmlspecialchars($package['package_tracking_number']); ?></code></td>
                            <td><?php echo htmlspecialchars($package['package_delivery_address']); ?></td>
                            <td><?php echo $package['package_delivery_date'] ? htmlspecialchars($package['package_delivery_date']) : '-'; ?></td>
                            <td><?php echo htmlspecialchars($package['package_creation_date']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <script>
        // Auto-submit du formulaire quand on change la sélection
        document.addEventListener('DOMContentLoaded', function() {
            const select = document.querySelector('select[name="client_id"]');
            if (select) {
                select.addEventListener('change', function() {
                    this.form.submit();
                });
            }
        });
    </script>
</body>
</html>

<?php
// Fermer la connexion
deconnexionBDD($conn);
?>