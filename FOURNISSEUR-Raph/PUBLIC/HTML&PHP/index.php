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
    <style>
        /* Styles spécifiques pour la page d'accueil */
        .welcome-section {
            text-align: center;
            margin-bottom: 40px;
            padding: 30px;
            background: var(--bg-secondary);
            border-radius: 12px;
            border: 1px solid var(--border-light);
            box-shadow: 0 4px 12px var(--shadow);
        }

        .welcome-section h2 {
            color: var(--brand-primary);
            font-size: 2.5rem;
            margin-bottom: 15px;
            font-family: var(--font-title);
        }

        .welcome-section p {
            color: var(--text-secondary);
            font-size: 1.1rem;
            max-width: 600px;
            margin: 0 auto;
            line-height: 1.6;
        }

        .actions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }

        .action-card {
            background: var(--bg-secondary);
            border-radius: 12px;
            padding: 25px;
            text-align: center;
            border: 1px solid var(--border-light);
            box-shadow: 0 4px 12px var(--shadow);
            transition: all 0.3s ease;
            cursor: pointer;
            text-decoration: none;
            color: inherit;
        }

        .action-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px var(--shadow-modal);
            border-color: var(--brand-primary);
        }

        .action-card-icon {
            font-size: 3rem;
            margin-bottom: 15px;
            color: var(--brand-primary);
        }

        .action-card h3 {
            color: var(--text-primary);
            margin-bottom: 10px;
            font-size: 1.3rem;
        }

        .action-card p {
            color: var(--text-secondary);
            margin-bottom: 20px;
            font-size: 0.95rem;
            line-height: 1.5;
        }

        .action-btn {
            background: var(--brand-primary);
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .action-btn:hover {
            background: #a00000;
            transform: translateY(-1px);
        }

        .stats-overview {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .stat-overview-card {
            background: var(--bg-secondary);
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            border: 1px solid var(--border-light);
            box-shadow: 0 2px 8px var(--shadow);
        }

        .stat-overview-card h4 {
            color: var(--text-primary);
            margin: 0 0 10px 0;
            font-size: 1rem;
        }

        .stat-overview-card .stat-number {
            color: var(--brand-primary);
            font-size: 2rem;
            font-weight: bold;
            margin: 0;
        }

        .quick-access {
            background: var(--bg-secondary);
            border-radius: 12px;
            padding: 25px;
            border: 1px solid var(--border-light);
            box-shadow: 0 4px 12px var(--shadow);
        }

        .quick-access h3 {
            color: var(--text-primary);
            margin-bottom: 20px;
            text-align: center;
        }

        .quick-links {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            justify-content: center;
        }

        .quick-link {
            background: var(--bg-primary);
            color: var(--text-primary);
            padding: 10px 20px;
            border-radius: 6px;
            text-decoration: none;
            border: 1px solid var(--border-color);
            transition: all 0.3s ease;
            font-size: 0.9rem;
        }

        .quick-link:hover {
            background: var(--brand-primary);
            color: white;
            transform: translateY(-1px);
        }

        .system-info {
            background: var(--bg-secondary);
            border-radius: 12px;
            padding: 25px;
            border: 1px solid var(--border-light);
            box-shadow: 0 4px 12px var(--shadow);
            margin-top: 30px;
        }

        .system-info h3 {
            color: var(--text-primary);
            margin-bottom: 15px;
        }

        .system-info ul {
            color: var(--text-secondary);
            line-height: 1.6;
        }

        .system-info li {
            margin-bottom: 8px;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .actions-grid {
                grid-template-columns: 1fr;
            }
            
            .stats-overview {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .welcome-section h2 {
                font-size: 2rem;
            }
            
            .quick-links {
                flex-direction: column;
                align-items: center;
            }
            
            .quick-link {
                width: 100%;
                max-width: 300px;
                text-align: center;
            }
        }

        @media (max-width: 480px) {
            .stats-overview {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation principale -->
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

            <a href="index.php" class="action-card">
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

        <!-- Accès rapide -->
        <div class="quick-access">
            <h3>🔗 Accès Rapide</h3>
            <div class="quick-links">
                <a href="index.php?status=in_transit" class="quick-link">📦 Colis en transit</a>
                <a href="index.php?status=delivered" class="quick-link">✅ Colis livrés</a>
                <a href="index.php?status=delayed" class="quick-link">⚠️ Colis en retard</a>
                <a href="listeClient.php" class="quick-link">👥 Tous les clients</a>
                <a href="injectionColi.html" class="quick-link">➕ Nouveau colis</a>
                <a href="injectionClient.html" class="quick-link">👤 Nouveau client</a>
            </div>
        </div>

        <!-- Informations système -->
        <div class="system-info">
            <h3>ℹ️ Informations du Système</h3>
            <ul>
                <li><strong>Fonctionnalités disponibles :</strong></li>
                <li>✅ Gestion complète des colis</li>
                <li>✅ Suivi des statuts en temps réel</li>
                <li>✅ Gestion des clients et propriétaires</li>
                <li>✅ Localisation automatique des magasins</li>
                <li>✅ Filtrage et recherche avancée</li>
                <li>✅ Interface responsive et mode sombre</li>
            </ul>
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