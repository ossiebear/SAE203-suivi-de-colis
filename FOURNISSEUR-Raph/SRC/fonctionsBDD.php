<?php

function enregistreClient($clientName, $clientFirstname, $accountEmail, $accountPhone, $accountPassword, $defaultAddress, $connex) {
    $hashedPassword = password_hash($accountPassword, PASSWORD_DEFAULT);

    $sql = "INSERT INTO clients (first_name, last_name, account_email, account_phone_number, account_password_hash, default_address) VALUES (:first_name, :last_name, :account_email, :account_phone_number, :account_password_hash, :default_address) RETURNING id";
    $res = $connex->prepare($sql);

    $data = [
        ':first_name' => $clientFirstname,         // prénom
        ':last_name' => $clientName,              // nom de famille
        ':account_email' => $accountEmail,
        ':account_phone_number' => $accountPhone,
        ':account_password_hash' => $hashedPassword,
        ':default_address' => $defaultAddress
    ];

    $res->execute($data);
    $idClient = $res->fetchColumn();
    return $idClient;
}

function enregistreMagasinOwner($clientName, $clientFirstname, $accountEmail, $accountPhone, $accountPassword, $defaultAddress, $connex) {
    $hashedPassword = password_hash($accountPassword, PASSWORD_DEFAULT);

    $sql = "INSERT INTO shop_owners (first_name, last_name, account_email, account_phone_number, account_password_hash, default_address) VALUES (:first_name, :last_name, :account_email, :account_phone_number, :account_password_hash, :default_address) RETURNING id";
    $res = $connex->prepare($sql);

    $data = [
        ':first_name' => $clientFirstname,         // prénom
        ':last_name' => $clientName,              // nom de famille
        ':account_email' => $accountEmail,
        ':account_phone_number' => $accountPhone,
        ':account_password_hash' => $hashedPassword,
        ':default_address' => $defaultAddress
    ];

    $res->execute($data);
    $idClient = $res->fetchColumn();
    return $idClient;
}

function enregistreMagasin($magasinName, $ownerID, $addressMagasin, $villeLocation, $codePostal, $pays, $connex) {
    $sql = "INSERT INTO shops (name, owner_id, address, city, postal_code, country) VALUES (:name, :owner_id, :address, :city, :postal_code, :country) RETURNING id";
    $res = $connex->prepare($sql);

    $data = [
        ':name' => $magasinName,         // prénom
        ':owner_id' => $ownerID,              // nom de famille
        ':address' => $addressMagasin,
        ':city' => $villeLocation,
        ':postal_code' => $codePostal,
        ':country' => $pays
    ];

    $res->execute($data);
    $idClient = $res->fetchColumn();
    return $idClient;
}

function listerGerants($conn) {
    $sql = "SELECT id, first_name, last_name FROM shop_owners ORDER BY first_name, last_name";
    $res = $conn->query($sql);
    return $res;
}

function ListerClients($conn) {
    // Requête pour récupérer tous les clients pour le menu déroulant
    $sqlClients = "SELECT id, first_name, last_name FROM clients ORDER BY last_name, first_name";
    $res = $conn->prepare($sqlClients);
    $res->execute();
    $clients = $res->fetchAll();
    return $clients;
}

function GetPackages($conn, $clientId = 0) {    
    // Requête pour récupérer les colis (tous ou filtrés par client)
    if ($clientId > 0) {
        // Colis d'un client spécifique
        $sql = "SELECT p.*, c.last_name, c.first_name, ps.status_name as package_status FROM packages p JOIN clients c ON p.recipient_client_id = c.id JOIN package_statuses ps ON p.current_status_id = ps.id WHERE p.recipient_client_id = :recipient_client_id ORDER BY p.created_at DESC";
        $res = $conn->prepare($sql);
        $res->execute([':recipient_client_id' => $clientId]);
    } else {
        // Tous les colis
        $sql = "SELECT p.*, c.last_name, c.first_name, ps.status_name as package_status FROM packages p JOIN clients c ON p.recipient_client_id = c.id JOIN package_statuses ps ON p.current_status_id = ps.id ORDER BY p.created_at DESC";
        $res = $conn->prepare($sql);
        $res->execute();
    }
    $packages = $res->fetchAll();
    return $packages;
}

?>