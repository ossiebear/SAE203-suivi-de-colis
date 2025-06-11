<?php
require_once 'SRC/fonctionsConnexion.php';
require_once '../../DATA/config.php';

$conn=connexionBDD('../../DATA/config.php');

// Récupérer les données du formulaire
$newItem = $_POST["newItem"];
$quantity = $_POST["quantity"];
$destinationAddress = $_POST["destinationAddress"];
$deliveryDate = $_POST["deliveryDate"];
$clientName = $_POST["clientName"];
$clientFirstname = $_POST["clientFirstname"];

// Validation des données
if (empty($newItem) || empty($quantity) || empty($clientName) || empty($clientFirstname)) {
    die("Le nom de l'article, la quantité, le nom et le prénom du client sont obligatoires.");
}

try {
    // D'abord, rechercher l'ID du client
    $sqlClient = "SELECT id FROM clients WHERE nom = :clientName AND prenom = :clientFirstname";
    $stmtClient = $conn->prepare($sqlClient);
    $stmtClient->bindParam(':clientName', $clientName);
    $stmtClient->bindParam(':clientFirstname', $clientFirstname);
    $stmtClient->execute();
    
    $client = $stmtClient->fetch(PDO::FETCH_ASSOC);
    
    if (!$client) {
        die("Client non trouvé. Veuillez d'abord enregistrer le client.");
    }
    
    $idClient = $client['id'];
    
    // Insérer le colis dans la table colis
    $sql = "INSERT INTO colis (nom_article, quantite, adresse_livraison, date_livraison, client_id) 
            VALUES (:newItem, :quantity, :destinationAddress, :deliveryDate, :idClient) 
            RETURNING id";
    
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':newItem', $newItem);
    $stmt->bindParam(':quantity', $quantity);
    $stmt->bindParam(':destinationAddress', $destinationAddress);
    $stmt->bindParam(':deliveryDate', $deliveryDate);
    $stmt->bindParam(':idClient', $idClient);
    
    $stmt->execute();
    $idColis = $stmt->fetchColumn();
    
    // Maintenant créer une commande pour ce colis
    $sqlCommande = "INSERT INTO COMMANDES (refclient, refcolis) VALUES (:idClient, :idColis) RETURNING idcommande";
    echo $sqlCommande . "<br>"; // Pour debug
    
    $stmtCommande = $conn->prepare($sqlCommande);
    $stmtCommande->bindParam(':idClient', $idClient);
    $stmtCommande->bindParam(':idColis', $idColis);
    $stmtCommande->execute();
    
    $nouvelIdCommande = $stmtCommande->fetchColumn();
    
    echo "Nouveau colis enregistré avec succès<br>";
    echo "ID du colis : " . $idColis . "<br>";
    echo "Nouvelle commande créée avec l'ID : " . $nouvelIdCommande;
    
    // Rediriger vers une page de confirmation
    // header("Location: confirmation.php?commande=" . $nouvelIdCommande . "&colis=" . $idColis);
    
} catch(PDOException $e) {
    echo "Erreur: " . $e->getMessage();
}

deconnexionBDD($conn);
?>