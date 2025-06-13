<?php 
require_once '../../SRC/fonctionsConnexion.php';
require_once '../../SRC/fonctionsBDD.php';
require_once '../../../DATA/DATABASE/CONFIG/config.php';

$conn=connexionBDD('../../../DATA/DATABASE/CONFIG/config.php');
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Enregistrement Client & Colis</title>
    <link rel="stylesheet" href="../CSS/styles.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
    <nav class="main-nav">
        <ul>
            <li><a href="injectionClient.html">Création Client</a></li>
            <li><a href="injectionColi.html">Création Colis</a></li>
            <li><a href="injectionMagasin.php">Création Magasin</a></li>
        </ul>
    </nav>

    <h1>Création d'un Directeur de Magasin</h1>
    <div class="container">
        <div class="add-items">
            <form action="../../SRC/EnregistrerMagasinOwner.php" method="post" autocomplete="off">
                <label for="shopName">Nom du Gérant du Magasin</label>
                <input type="text" id="shopName" name="shopName" placeholder="Entrez le nom" required />
                <label for="shopFirstname">Prénom du Gérant du Magasin</label>
                <input type="text" id="shopFirstname" name="shopFirstname" placeholder="Entrez le prénom" required />
                <label for="emailAddress">Adresse Email</label>
                <input type="text" id="emailAddress" name="emailAddress" placeholder="Entrez l'adresse votre email" required />
                <label for="phoneNumber">Numéro de Téléphone</label>
                <input type="text" id="phoneNumber" name="phoneNumber" placeholder="Entrez votre numéro de téléphone" required />
                <label for="password">Entrez un Mot de passe</label>
                <input type="password" id="password" name="password" placeholder="Tapez votre password" required />
                <label for="destinationAddress">Adresse de livraison</label>
                <input type="text" id="destinationAddress" name="destinationAddress" placeholder="Entrez l'adresse" required />
                <button type="submit">Enregistrer le directeur</button>
            </form>
        </div>
    </div>

    <h1>Création d'un Magasin</h1>
    <div class="container">
        <div class="add-items">
            <form action="../../SRC/EnregistrerMagasin.php" method="post" autocomplete="off">
                <label for="shopName2">Nom du Magasin</label>
                <input type="text" id="shopName2" name="shopName" placeholder="Entrez le nom du magasin" required />
                
                <?php
                $resultat = listerGerants($conn);
                $resuTab = $resultat->fetchAll();

                // pour voir le contenu de resuTab (à décommenter pour execution) :
                    /*
                    print("<pre>");
                    print_r($resuTab);
                    print("</pre>");
                    */

                print '<select name="P_idgerant">';
                print '<option value="">-- Choisir un gérant --</option>';
                foreach ($resuTab as $ligne) {
                    $nom_complet = $ligne["first_name"] . ' ' . $ligne["last_name"];
                    print '<option value="'.$ligne["id"].'">'.htmlspecialchars($nom_complet).'</option>';
                }
                print "</select>";
                ?>
                
                <label for="shopAddress">Adresse du Magasin</label>
                <input type="text" id="shopAddress" name="shopAddress" placeholder="Entrez l'adresse du magasin" required />
                <label for="villeLocation">Ville de location du Magasin</label>
                <input type="text" id="villeLocation" name="villeLocation" placeholder="Entrez la ville de location du magasin" required />
                <label for="codePostal">Code Postal du Magasin</label>
                <input type="text" id="codePostal" name="codePostal" placeholder="Entrez le code postal du magasin" required />
                <label for="pays">Pays de location du Magasin</label>
                <input type="text" id="pays" name="pays" placeholder="Entrez le pays de location du magasin" required />
                <button type="submit">Enregistrer le magasin</button>
            </form>
        </div>
    </div>
    <?php 
    deconnexionBDD($conn);
    ?>
</body>
</html>