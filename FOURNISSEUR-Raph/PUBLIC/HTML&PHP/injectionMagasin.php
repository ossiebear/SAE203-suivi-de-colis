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
    <title>Enregistrement Client & Colis</title>
    <link rel="stylesheet" href="../CSS/index.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
    <nav class="main-nav">
        <ul>
            <li><a href="index.php" class="active">Accueil</a></li>
            <li><a href="gestionColi.php">Liste des Colis</a></li>
            <li><a href="listeClient.php">Liste des Clients</a></li>
        </ul>
    </nav>

    <h1>🏪 Création d'un Directeur de Magasin</h1>
    <div class="container">
        <div class="add-items">
            <form action="../../SRC/EnregistrerMagasinOwner.php" method="post" autocomplete="off">
                <label for="shopName">Nom du Gérant du Magasin</label>
                <input type="text" id="shopName" name="shopName" placeholder="Entrez le nom" required />
                <label for="shopFirstname">Prénom du Gérant du Magasin</label>
                <input type="text" id="shopFirstname" name="shopFirstname" placeholder="Entrez le prénom" required />
                <label for="emailAddress">Adresse Email</label>
                <input type="text" id="emailAddress" name="emailAddress" placeholder="Entrez votre email" required />
                <label for="phoneNumber">Numéro de Téléphone</label>
                <input type="text" id="phoneNumber" name="phoneNumber" placeholder="Entrez votre numéro de téléphone" required />
                <label for="password">Entrez un Mot de passe</label>
                <input type="password" id="password" name="password" placeholder="Tapez votre mot de passe" required />
                <label for="destinationAddress">Adresse de livraison</label>
                <input type="text" id="destinationAddress" name="destinationAddress" placeholder="Entrez l'adresse" required />
                <button type="submit">Enregistrer le directeur</button>
            </form>
        </div>
    </div>

    <h1>🏪 Création d'un Magasin</h1>
    <div class="container">
        <div class="add-items">
            <form action="../../SRC/EnregistrerMagasin.php" method="post" autocomplete="off">
                <label for="shopName2">Nom du Magasin</label>
                <input type="text" id="shopName2" name="shopName2" placeholder="Entrez le nom du magasin" required />

                <label for="P_idcategorie">Catégorie du Magasin</label>
                <?php 
                $resuTab = ListerShopsCategories($conn); //car deja un tableau

                echo '<select name="P_idcategorie" id="P_idcategorie" required>';
                echo '<option value="">-- Choisir une catégorie --</option>';
                foreach ($resuTab as $ligne) {
                    $categories = $ligne["category_name"];
                    echo '<option value="'.htmlspecialchars($ligne["id"]).'">'.htmlspecialchars($categories).'</option>';
                }
                echo "</select>";
                ?>

                <label for="P_idgerant">Gérant du Magasin</label>
                <?php
                $resuTab = listerGerants($conn); //car deja un tableau

                echo '<select name="P_idgerant" id="P_idgerant" required>';
                echo '<option value="">-- Choisir un gérant --</option>';
                foreach ($resuTab as $ligne) {
                    $nom_complet = $ligne["first_name"] . ' ' . $ligne["last_name"];
                    echo '<option value="'.htmlspecialchars($ligne["id"]).'">'.htmlspecialchars($nom_complet).'</option>';
                }
                echo "</select>";
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