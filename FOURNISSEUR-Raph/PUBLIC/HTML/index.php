<?php
require_once '../../SRC/fonctionsConnexion.php';
require_once '../../SRC/fonctionsBDD.php';
require_once '../../../DATA/DATABASE/CONFIG/config.php';

$conn = connexionBDD('../../../DATA/DATABASE/CONFIG/config.php');

// Récupération de l'id pour le filtre client si un id a ete selectionné 
$clientId = isset($_GET['client_id']) ? intval($_GET['client_id']) : 0;
$clients = ListerClients($conn);
$packages = GetPackages($conn, $clientId);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des colis</title>
    <link rel="stylesheet" href="../CSS/styles-listeClient.css">
</head>
<body>
    <!-- Toggle mode sombre -->
    <div class="dark-mode-toggle">
        <span class="dark-mode-label">Mode sombre</span>
        <label class="switch">
            <input type="checkbox" id="darkModeToggle">
            <span class="slider"></span>
        </label>
    </div>
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
                            <?php echo htmlspecialchars($client['last_name'] . ' ' . $client['first_name']); ?>
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
                            echo htmlspecialchars($client['last_name'] . ' ' . $client['first_name']);
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
                                <strong><?php echo htmlspecialchars($package['last_name'] . ' ' . $package['first_name']); ?></strong>
                            </td>
                            <td>
                                <span style="padding: 3px 8px; border-radius: 3px; font-size: 12px; 
                                      background-color: 
                                        <?php 
                                            switch(strtolower($package['package_status'])) {
                                                case 'Delivered': echo '#d4edda; color: #155724;'; break;
                                                case 'Picked Up': echo '#d4edda; color: #155724;'; break;
                                                case 'Created': echo '#fff3cd; color: #856404;'; break;
                                                case 'In Transit': echo '#fff3cd; color: #856404;'; break;
                                                case 'Arrived at Hub': echo '#fff3cd; color: #856404;'; break;
                                                case 'Out for Delivery': echo '#fff3cd; color: #856404;'; break;
                                                case 'Delayed': echo '#f8d7da; color: #721c24;'; break;
                                                case 'Delivery Failed': echo '#f8d7da; color: #721c24;'; break;
                                                case 'Returned to Sender': echo '#f8d7da; color: #721c24;'; break;
                                                default: echo '#e2e3e5; color: #383d41;';
                                            }
                                        ?>">
                                    <?php echo htmlspecialchars($package['package_status']); ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars($package['weight_kg']); ?></td>
                            <td><?php echo htmlspecialchars($package['dimensions_cm']); ?></td>
                            <td><code><?php echo htmlspecialchars($package['tracking_number']); ?></code></td>
                            <td><?php echo htmlspecialchars($package['onpackage_destination_address']); ?></td>
                            <td><?php echo $package['actual_delivery_date'] ? htmlspecialchars($package['actual_delivery_date']) : '-'; ?></td>
                            <td><?php echo htmlspecialchars($package['created_at']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <script src="../JS/script.js"></script>
</body>
</html>

<?php
// Fermer la connexion
deconnexionBDD($conn);
?>