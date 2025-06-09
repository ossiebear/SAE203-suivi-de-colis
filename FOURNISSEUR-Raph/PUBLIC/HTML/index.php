<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recherche Commande Client</title>
    <link rel="stylesheet" href="../CSS/styles-php.css"></link>
</head>
<body>
    <div class="container">
        <h1>🔍 Recherche de commandes client</h1>

        <!-- Formulaire de recherche -->
        <div class="search-form">
            <form id="searchForm">
                <label for="name">Nom du client :</label>
                <input type="text" 
                       name="name" 
                       id="name" 
                       placeholder="Entrez le nom du client">
                <input type="submit" value="Rechercher">
            </form>
        </div>

        <div id="searchResults"></div>

        <?php
        // Configuration et initialisation
        try {
            require_once '../../SRC/fonctionsConnexion.php';
            require_once '../../SRC/fonctionsBDD.php';
            require_once '../../SRC/fonctionSys.php';
            
            $conn1 = connexionBDD('../../DATA/config.php');
        } catch (Exception $e) {
            echo '<div class="error">Erreur de connexion : ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . '</div>';
            exit;
        }
        ?>
    </div>
    <script src="../JS/script.js"></script>
</body>
</html>