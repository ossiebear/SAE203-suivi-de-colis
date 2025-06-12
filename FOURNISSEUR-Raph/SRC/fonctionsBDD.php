<?php

function enregistreClient($clientName, $clientFirstname, $accountEmail, $accountPhone, $accountPassword, $defaultAddress, $connex) {
    // Hachage sécurisé du mot de passe avec un fonction deja intégrer a PHP
    //$hashedPassword = password_hash($accountPassword, PASSWORD_ARGON2ID);
    $hashedPassword = $accountPassword;


    $sql = "INSERT INTO clients (first_name, last_name, account_email, account_phone_number, account_password_hash, default_address) VALUES (:clientName, :clientFirstname, :accountEmail, :accountPhone, :accountPassword, :defaultAddress) RETURNING id";
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
    // Hachage sécurisé du mot de passe avec une fonction déjà intégrée à PHP
    //$hashedPassword = password_hash($accountPassword, PASSWORD_ARGON2ID);
    $hashedPassword = $accountPassword;


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