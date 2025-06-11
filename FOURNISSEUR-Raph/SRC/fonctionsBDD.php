<?php

function enregistreClient($clientName, $clientFirstname, $accountEmail, $accountPhone, $accountPassword, $defaultAddress, $connex) {
    // Hachage sécurisé du mot de passe avec un fonction deja intégrer a PHP
    $hashedPassword = password_hash($accountPassword, PASSWORD_ARGON2ID);
    
    $sql = "INSERT INTO clients (first_name, last_name, account_email, account_phone_number, account_password_hash, default_address) VALUES (:clientName, :clientFirstname, :accountEmail, :accountPhone, :accountPassword, :defaultAddress) RETURNING id";
    $res = $connex->prepare($sql);
    
    $data = [
        ':clientName' => $clientName,
        ':clientFirstname' => $clientFirstname,
        ':accountEmail' => $accountEmail,
        ':accountPhone' => $accountPhone,
        ':accountPassword' => $hashedPassword,
        ':defaultAddress' => $defaultAddress
    ];
    
    $res->execute($data);
    $idClient = $res->fetchColumn();
    return $idClient;
}

function enregistreMagasinOwner($clientName, $clientFirstname, $accountEmail, $accountPhone, $accountPassword, $defaultAddress, $connex) {
    // Hachage sécurisé du mot de passe avec un fonction deja intégrer a PHP
    $hashedPassword = password_hash($accountPassword, PASSWORD_ARGON2ID);
    
    $sql = "INSERT INTO shop_owners (first_name, last_name, account_email, account_phone_number, account_password_hash, default_address) VALUES (:clientName, :clientFirstname, :accountEmail, :accountPhone, :accountPassword, :defaultAddress) RETURNING id";
    $res = $connex->prepare($sql);
    
    $data = [
        ':clientName' => $clientName,
        ':clientFirstname' => $clientFirstname,
        ':accountEmail' => $accountEmail,
        ':accountPhone' => $accountPhone,
        ':accountPassword' => $hashedPassword,
        ':defaultAddress' => $defaultAddress
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

function ListerClients($connex) {
    $sql = "SELECT * FROM CLIENTS";
    $res = $connex->query($sql);
    return $res->fetchAll();
}