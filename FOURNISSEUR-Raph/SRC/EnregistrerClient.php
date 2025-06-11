<?php
require_once 'SRC/fonctionsConnexion.php';
require_once '../../DATA/config.php';

$conn=connexionBDD('../../DATA/config.php');

// Récupérer les données du formulaire
$clientName = $_POST["clientName"];
$clientFirstname = $_POST["clientFirstname"];
$destinationAddress = $_POST["destinationAddress"];
$deliveryDate = $_POST["deliveryDate"];

// Validation des données (exemple simple)
if (empty($clientName) || empty($clientFirstname)) {
    die("Le nom et le prénom du client sont obligatoires.");
}

try {
    // Insérer le client d'abord
    $sql = "INSERT INTO clients (nom, prenom, adresse_livraison, date_livraison) 
            VALUES (:clientName, :clientFirstname, :destinationAddress, :deliveryDate) 
            RETURNING id";
    
    $res = $conn->prepare($sql);
    $res->bindParam(':clientName', $clientName);
    $res->bindParam(':clientFirstname', $clientFirstname);
    $res->bindParam(':destinationAddress', $destinationAddress);
    $res->bindParam(':deliveryDate', $deliveryDate);
    
    $res->execute();
    $idClient = $res->fetchColumn();
    
    // Maintenant insérer dans COMMANDES avec RETURNING
    $sql = "INSERT INTO COMMANDES (refclient) VALUES ('$idClient') RETURNING idcommande";
    echo $sql . "<br>"; // Pour debug
    
    $res = $conn->query($sql);
    $nouvelIdCommande = $res->fetchColumn();
    
    echo "Nouveau client enregistré avec succès<br>";
    echo "Nouvelle commande créée avec l'ID : " . $nouvelIdCommande;
    
    // Rediriger vers une page de confirmation
    // header("Location: confirmation.php?id=" . $nouvelIdCommande);
    
} catch(PDOException $e) {
    echo "Erreur: " . $e->getMessage();
}

deconnexionBDD($conn);
?>