<?php
require_once '../../SRC/fonctionsConnexion.php';
require_once '../../SRC/fonctionsBDD.php';
require_once '../../../DATA/DATABASE/CONFIG/config.php';

$conn = connexionBDD('../../../DATA/DATABASE/CONFIG/config.php');

// Récupération des paramètres de filtre
$searchTerm = isset($_GET['search']) ? trim($_GET['search']) : '';

$clients = GetClientsWithSearch($conn, $searchTerm);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des Clients</title>
    <link rel="stylesheet" href="../CSS/index.css">
    <link rel="stylesheet" href="../CSS/listeClient.css">
</head>
<body>
    <nav class="main-nav">
        <ul>
            <li><a href="index.php" class="active">Accueil</a></li>
            <li><a href="injectionClient.html">Création Client</a></li>
            <li><a href="injectionOwner.html">Création Owner</a></li>
            <li><a href="injectionColi.html">Création Colis</a></li>
            <li><a href="injectionMagasin.php">Création Magasin</a></li>
            <li><a href="gestionColi.php">Liste des Colis</a></li>
            <li><a href="listeClient.php">Liste des Clients</a></li>
        </ul>
    </nav>

    <div class="container">
        <h1>👥 Liste des Clients</h1>
        
        <!-- Section de recherche -->
        <div class="filter-section">
            <h3>🔍 Rechercher un client</h3>
            <form method="GET" action="">
                <div class="search-container">
                    <input type="text" name="search" class="search-input"
                           value="<?php echo htmlspecialchars($searchTerm); ?>" 
                           placeholder="Nom, prénom, email, téléphone ou adresse...">
                    <button type="submit" class="search-btn">
                        🔍 Rechercher
                    </button>
                    <?php if (!empty($searchTerm)): ?>
                        <a href="listeClient.php" class="reset-link">
                            <button type="button" class="reset-btn">
                                🔄 Réinitialiser
                            </button>
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- Statistiques -->
        <div class="stats">
            <div class="stat-card">
                <h4>Total clients</h4>
                <p><strong><?php echo count($clients); ?></strong></p>
            </div>
            <?php if (!empty($searchTerm)): ?>
                <div class="stat-card">
                    <h4>Résultats trouvés</h4>
                    <p><strong><?php echo count($clients); ?></strong></p>
                </div>
                <div class="stat-card">
                    <h4>Terme recherché</h4>
                    <p><strong><?php echo htmlspecialchars($searchTerm); ?></strong></p>
                </div>
            <?php endif; ?>
            <div class="stat-card">
                <h4>Actions rapides</h4>
                <p><a href="injectionClient.html" class="quick-action-link">➕ Nouveau client</a></p>
            </div>
        </div>

        <!-- Tableau des clients -->
        <?php if (empty($clients)): ?>
            <div class="no-data">
                <h3>Aucun client trouvé</h3>
                <p>
                    <?php 
                    if (!empty($searchTerm)) {
                        echo 'Aucun client ne correspond aux critères de recherche.';
                    } else {
                        echo 'Aucun client dans la base de données.';
                    }
                    ?>
                </p>
                <p><a href="injectionClient.html" class="create-first-link">➕ Créer le premier client</a></p>
            </div>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nom</th>
                        <th>Prénom</th>
                        <th>Email</th>
                        <th>Téléphone</th>
                        <th>Adresse par défaut</th>
                        <th>Date de création</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($clients as $client): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($client['id']); ?></td>
                            <td><strong><?php echo htmlspecialchars($client['last_name']); ?></strong></td>
                            <td><?php echo htmlspecialchars($client['first_name']); ?></td>
                            <td>
                                <a href="mailto:<?php echo htmlspecialchars($client['account_email']); ?>" 
                                   class="email-link">
                                    <?php echo htmlspecialchars($client['account_email']); ?>
                                </a>
                            </td>
                            <td>
                                <a href="tel:<?php echo htmlspecialchars($client['account_phone_number']); ?>" 
                                   class="phone-link">
                                    <?php echo htmlspecialchars($client['account_phone_number']); ?>
                                </a>
                            </td>
                            <td><?php echo htmlspecialchars($client['default_address']); ?></td>
                            <td><?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($client['created_at']))); ?></td>
                            <td>
                                <a href="gestionColi.php?client_id=<?php echo $client['id']; ?>" 
                                   class="action-link">
                                    📦 Voir colis
                                </a>
                            </td>
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