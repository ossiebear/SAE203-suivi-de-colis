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

function enregistreOwner($OwnerName, $OwnerFirstname, $accountEmail, $accountPhone, $accountPassword, $connex) {
    $hashedPassword = password_hash($accountPassword, PASSWORD_DEFAULT);

    $sql = "INSERT INTO shop_owners (first_name, last_name, account_email, account_phone_number, account_password_hash) VALUES (:first_name, :last_name, :account_email, :account_phone_number, :account_password_hash) RETURNING id";
    $res = $connex->prepare($sql);

    $data = [
        ':first_name' => $OwnerName,         // prénom
        ':last_name' => $OwnerFirstname,              // nom de famille
        ':account_email' => $accountEmail,
        ':account_phone_number' => $accountPhone,
        ':account_password_hash' => $hashedPassword,
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

function enregistreMagasin($magasinName, $parentOffice, $category_id, $ownerID, $addressMagasin, $villeLocation, $codePostal, $pays, $latitude, $longitude, $connex) {
    $sql = "INSERT INTO shops (name, parent_office_acores_id, category_id, owner_id, address, city, postal_code, country, latitude, longitude) VALUES (:name, :parent_office_acores_id, :category_id, :owner_id, :address, :city, :postal_code, :country, :latitude, :longitude) RETURNING id";
    $res = $connex->prepare($sql);

    $data = [
        ':name' => $magasinName,         // prénom
        ':parent_office_acores_id' => $parentOffice,
        ':category_id' => $category_id,
        ':owner_id' => $ownerID,              // nom de famille
        ':address' => $addressMagasin,
        ':city' => $villeLocation,
        ':postal_code' => $codePostal,
        ':country' => $pays,
        ':latitude' => $latitude,
        ':longitude' => $longitude
    ];

    $res->execute($data);
    $idMagasin = $res->fetchColumn();
    return $idMagasin;
}

function enregistreColi($itemName, $destinationAddress, $deliveryDate, $clientName, $clientFirstname, $connex) {
    try {
        // Commencer une transaction pour assurer la cohérence des données
        $connex->beginTransaction();
        
        // Vérifier si le client existe déjà
        $sqlCheckClient = "SELECT id FROM clients WHERE first_name = :first_name AND last_name = :last_name";
        $resCheck = $connex->prepare($sqlCheckClient);
        $resCheck->execute([
            ':first_name' => $clientFirstname,
            ':last_name' => $clientName
        ]);
        
        $clientId = $resCheck->fetchColumn();
        
        // Si le client n'existe pas, utiliser la fonction existante pour le créer
        if (!$clientId) {
            // Données par défaut pour un client créé via colis
            $defaultEmail = strtolower($clientFirstname . '.' . $clientName . '@temp.com');
            $defaultPassword = 'temp123'; 
            $defaultPhone = '0000';
            
            $clientId = enregistreClient(
                $clientName,           // clientName (nom de famille)
                $clientFirstname,      // clientFirstname (prénom)  
                $defaultEmail,         // accountEmail
                $defaultPhone,         // accountPhone
                $defaultPassword,      // accountPassword
                $destinationAddress,   // defaultAddress
                $connex               // connex
            );
        }
        
        // Générer un numéro de suivi unique de 20 caractères (lettres et chiffres)
        $trackingNumber = generateTrackingNumber();
        
        // Vérifier l'unicité du numéro de suivi
        do {
            $sqlCheckTracking = "SELECT COUNT(*) FROM packages WHERE tracking_number = :tracking_number";
            $resCheckTracking = $connex->prepare($sqlCheckTracking);
            $resCheckTracking->execute([':tracking_number' => $trackingNumber]);
            $exists = $resCheckTracking->fetchColumn();
            
            if ($exists > 0) {
                $trackingNumber = generateTrackingNumber();
            }
        } while ($exists > 0);
        
        // CORRECTION : Supprimer la colonne 'id' car elle est auto-générée
        $sqlPackage = "INSERT INTO packages (onpackage_recipient_name, recipient_client_id, onpackage_destination_address, actual_delivery_date, tracking_number, current_status_id) 
                      VALUES (:onpackage_recipient_name, :recipient_client_id, :onpackage_destination_address, :actual_delivery_date, :tracking_number, :current_status_id) 
                      RETURNING id";
        $resPackage = $connex->prepare($sqlPackage);
        
        $defaultStatusId = 1;
        
        // CORRECTION : Supprimer le paramètre ':id'
        $resPackage->execute([
            ':onpackage_recipient_name' => $clientFirstname . ' ' . $clientName,  // Nom complet du destinataire
            ':recipient_client_id' => $clientId,                                  // ID du client
            ':onpackage_destination_address' => $destinationAddress,              // Adresse de destination
            ':actual_delivery_date' => $deliveryDate,                            // Date de livraison
            ':tracking_number' => $trackingNumber,                               // Numéro de suivi
            ':current_status_id' => $defaultStatusId                             // Statut actuel
        ]);
        
        $packageId = $resPackage->fetchColumn();
        
        // DEBUG : Vérifier que l'ID a été récupéré
        if (!$packageId) {
            throw new Exception("Erreur: Impossible de récupérer l'ID du package créé");
        }
        
        // Valider la transaction
        $connex->commit();
        
        // Retourner les informations du colis créé
        return [
            'colis_id' => $packageId,
            'client_id' => $clientId,
            'tracking_number' => $trackingNumber
        ];
        
    } catch (Exception $e) {
        // Annuler la transaction en cas d'erreur
        $connex->rollback();
        throw new Exception("Erreur lors de l'enregistrement du colis : " . $e->getMessage());
    }
}

// Fonction pour générer un code de tracking de 20 caractères alphanumériques
function generateTrackingNumber() {
    $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $trackingNumber = '';
    
    for ($i = 0; $i < 20; $i++) {
        $trackingNumber .= $characters[mt_rand(0, strlen($characters) - 1)];
    }
    
    return $trackingNumber;
}

function listerGerants($conn) {
    $sql = "SELECT id, first_name, last_name FROM shop_owners ORDER BY first_name, last_name";
    $res = $conn->prepare($sql);
    $res->execute();
    $gerants = $res->fetchAll();
    return $gerants;
}

function ListerClients($conn) {
    // Requête pour récupérer tous les clients pour le menu déroulant
    $sqlClients = "SELECT id, first_name, last_name FROM clients ORDER BY last_name, first_name";
    $res = $conn->prepare($sqlClients);
    $res->execute();
    $clients = $res->fetchAll();
    return $clients;
}

function ListerShopsCategories($conn) {
    $sql = "SELECT id, category_name FROM shop_categories ORDER BY category_name";
    $res = $conn->prepare($sql);
    $res->execute();
    $categories = $res->fetchAll();
    return $categories;
}

function GetPackages($conn, $clientId = 0) {    
    // Requête pour récupérer les colis (tous ou filtrés par client)
    if ($clientId > 0) {
        // Colis d'un client spécifique
        $sql = "SELECT p.*, c.last_name, c.first_name, ps.status_name AS package_status 
        FROM packages p 
        JOIN clients c ON p.recipient_client_id = c.id 
        JOIN package_statuses ps ON p.current_status_id = ps.id 
        WHERE p.recipient_client_id = :recipient_client_id 
        ORDER BY p.created_at DESC";
        $res = $conn->prepare($sql);
        $res->execute([':recipient_client_id' => $clientId]);
    } else {
        // Tous les colis
        $sql = "SELECT p.*, c.last_name, c.first_name, ps.status_name AS package_status 
        FROM packages p 
        JOIN clients c ON p.recipient_client_id = c.id 
        JOIN package_statuses ps ON p.current_status_id = ps.id 
        ORDER BY p.created_at DESC";
        $res = $conn->prepare($sql);
        $res->execute();
    }
    $packages = $res->fetchAll();
    return $packages;
}

// Fonction pour récupérer les clients avec filtre de recherche
// le ILIKE sert a faire une recherche sans avoir a tout taper dans PostgreSQL
function GetClientsWithSearch($conn, $searchTerm = '') {
    if (!empty($searchTerm)) {
        $sql = "SELECT * FROM clients 
                WHERE first_name ILIKE :search_term 
                OR last_name ILIKE :search_term 
                OR account_email ILIKE :search_term 
                OR account_phone_number ILIKE :search_term 
                OR default_address ILIKE :search_term
                ORDER BY last_name, first_name";
        $res = $conn->prepare($sql);
        $res->execute([':search_term' => '%' . $searchTerm . '%']);
    } else {
        $sql = "SELECT * FROM clients ORDER BY last_name, first_name";
        $res = $conn->prepare($sql);
        $res->execute();
    }
    return $res->fetchAll();
}

function CountClients($conn) {
    $sql = "SELECT COUNT(*) as client_count FROM clients";
    $res = $conn->prepare($sql);
    $res->execute();
    $result = $res->fetch();
    return $result['client_count'];
}

function CountPackages($conn) {
    $sql = "SELECT COUNT(*) as package_count FROM packages";
    $res = $conn->prepare($sql);
    $res->execute();
    $result = $res->fetch();
    return $result['package_count'];
}

function CountMagasin($conn) {
    $sql = "SELECT COUNT(*) as shops_count FROM shops";
    $res = $conn->prepare($sql);
    $res->execute();
    $result = $res->fetch();
    return $result['shops_count'];
}

function CountOwnerMagasin($conn) {
    $sql = "SELECT COUNT(*) as shop_owner_count FROM shop_owners";
    $res = $conn->prepare($sql);
    $res->execute();
    $result = $res->fetch();
    return $result['shop_owner_count'];
}

function GetParentOffice($conn, $ville) {
    $sql = "SELECT d.acores_id, d.city FROM department_offices AS d 
            JOIN shops AS s ON d.city = s.city";
    $res = $conn->prepare($sql);
    $res->execute();
    $result = $res->fetch();
    return $result['acores_id'];
}

function GetLocalisationMagasin($ville) {
    $url = "https://nominatim.openstreetmap.org/search?q=".urlencode($ville)."&format=json&limit=1";

    // Nominatim demande un User-Agent personnalisé
    $options = [
        "http" => [
            "header" => "User-Agent: MyApp/1.0\r\n"
        ]
    ];

    // context sert a personnaliser la requet avec un user agent personalisé 
    $context = stream_context_create($options); // doc https://www.php.net/manual/fr/function.stream-context-create.php

    $response = file_get_contents($url, false, $context);
    $data = json_decode($response, true);

    if (!empty($data)) {
        return [
            "lat" => $data[0]['lat'],
            "lon" => $data[0]['lon']
        ];
    } else {
        return null;
    }
}


?>