<?php 
require_once '../../SRC/fonctionsConnexion.php';
require_once '../../SRC/fonctionsBDD.php';
require_once '../../../DATA/DATABASE/CONFIG/config.php';

$conn = connexionBDD('../../../DATA/DATABASE/CONFIG/config.php');
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redboot - Gestion des Colis</title>
    <link rel="stylesheet" href="../CSS/index.css">
    <link rel="stylesheet" href="../CSS/accueil.css">
</head>
<body>
    <!-- Navigation principale -->
    <nav class="main-nav">
        <ul>
            <li><a href="index.php" class="active">Accueil</a></li>
            <li><a href="gestionColi.php">Liste des Colis</a></li>
            <li><a href="listeClient.php">Liste des Clients</a></li>
        </ul>
    </nav>

    <div class="container">
        <!-- Section de bienvenue -->
        <div class="welcome-section">
            <h2>🚚 Redboot - Gestion des Colis</h2>
            <p>
                Bienvenue dans votre système de gestion de colis. 
                Gérez facilement vos clients, propriétaires, magasins et suivez tous vos colis en temps réel.
            </p>
        </div>

        <!-- Statistiques rapides -->
        <div class="stats-overview">
            <div class="stat-overview-card">
                <h4>📦 Colis Actifs</h4>
                <p class="stat-number">
                    <?php 
                    $nb = CountPackages($conn);
                    echo $nb;
                    ?>
                </p>
            </div>
            <div class="stat-overview-card">
                <h4>👥 Clients</h4>
                <p class="stat-number">
                    <?php 
                    $nb = CountClients($conn);
                    echo $nb;
                    ?>
                </p>
            </div>
            <div class="stat-overview-card">
                <h4>🏪 Magasins</h4>
                <p class="stat-number">
                    <?php 
                    $nb = CountMagasin($conn);
                    echo $nb;
                    ?>
                </p>
            </div>
            <div class="stat-overview-card">
                <h4>👨‍💼 Propriétaires</h4>
                <p class="stat-number">
                    <?php 
                    $nb = CountOwnerMagasin($conn);
                    echo $nb;
                    ?>
                </p>
            </div>
        </div>

        <!-- Actions principales -->
        <div class="actions-grid">
            <a href="injectionColi.html" class="action-card">
                <div class="action-card-icon">📦</div>
                <h3>Créer un Colis</h3>
                <p>Enregistrez un nouveau colis avec toutes les informations de livraison nécessaires.</p>
                <span class="action-btn">Créer un colis</span>
            </a>

            <a href="injectionClient.html" class="action-card">
                <div class="action-card-icon">👤</div>
                <h3>Nouveau Client</h3>
                <p>Ajoutez un nouveau client à votre base de données avec ses informations personnelles.</p>
                <span class="action-btn">Ajouter un client</span>
            </a>

            <a href="injectionMagasin.php" class="action-card">
                <div class="action-card-icon">🏪</div>
                <h3>Nouveau Magasin</h3>
                <p>Enregistrez un nouveau magasin avec sa localisation et ses informations.</p>
                <span class="action-btn">Créer un magasin</span>
            </a>

            <a href="injectionOwner.html" class="action-card">
                <div class="action-card-icon">👨‍💼</div>
                <h3>Nouveau Propriétaire</h3>
                <p>Ajoutez un nouveau propriétaire de magasin à votre système.</p>
                <span class="action-btn">Ajouter un propriétaire</span>
            </a>

            <a href="gestionColi.php" class="action-card">
                <div class="action-card-icon">📋</div>
                <h3>Gérer les Colis</h3>
                <p>Consultez, filtrez et gérez tous vos colis existants.</p>
                <span class="action-btn">Voir les colis</span>
            </a>

            <a href="listeClient.php" class="action-card">
                <div class="action-card-icon">👥</div>
                <h3>Liste des Clients</h3>
                <p>Consultez et gérez votre base de données clients.</p>
                <span class="action-btn">Voir les clients</span>
            </a>
        </div>
    </div>

    <script src="../JS/script.js"></script>
    <script>
        // Script pour charger les statistiques dynamiquement (optionnel)
        document.addEventListener('DOMContentLoaded', function() {
            // Vous pouvez ajouter ici du code JavaScript pour charger
            // les statistiques réelles depuis votre base de données
            console.log('Page d\'accueil chargée');
        });
    </script>
</body>
</html>