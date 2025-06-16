<?php
// track_package.php - Backend for package tracking
// SAE203 Package Tracking System

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

// Include database connection
require_once '../DATA/DATABASE/FUNCTIONS/db_connections.php';

// Check if tracking number is provided
if (!isset($_GET['tracking_number']) || empty($_GET['tracking_number'])) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'Numéro de suivi requis'
    ]);
    exit;
}

$trackingNumber = trim($_GET['tracking_number']);

try {
    // Connect to database
    $pdo = db_connect();
      // Prepare SQL query to get package information with related data
    $sql = "
        SELECT 
            p.id,
            p.tracking_number,
            p.onpackage_sender_name,
            p.onpackage_sender_address,
            p.onpackage_recipient_name,
            p.onpackage_destination_address,
            p.weight_kg,
            p.dimensions_cm,
            p.volume_m3,
            p.is_priority,
            p.is_fragile,
            p.declared_value,
            p.estimated_delivery_date,
            p.actual_delivery_date,
            p.created_at,
            p.updated_at,
            -- Status information
            s.status_name as status_name,
            s.description as status_description,
            -- Office information
            o.name as office_name,
            o.street_address as office_address,
            o.city as office_city,
            -- Shop information (if applicable)
            sh.name as shop_name,
            sh.address as shop_address
        FROM packages p
        LEFT JOIN package_statuses s ON p.current_status_id = s.id
        LEFT JOIN post_offices o ON p.current_office_id = o.id
        LEFT JOIN shops sh ON p.sender_shop_id = sh.id
        WHERE p.tracking_number = :tracking_number
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':tracking_number', $trackingNumber, PDO::PARAM_STR);
    $stmt->execute();
      $package = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$package) {
        echo json_encode([
            'success' => false,
            'error' => 'Colis non trouvé. Vérifiez votre numéro de suivi.'
        ]);
        exit;
    }
    
    // Debug: log the raw package data
    error_log("Raw package data: " . print_r($package, true));
      // Format the response data
    $response = [
        'success' => true,
        'package' => [
            'id' => $package['id'],
            'tracking_number' => $package['tracking_number'],
            'sender' => [
                'name' => $package['onpackage_sender_name'] ?: 'Non spécifié',
                'address' => $package['onpackage_sender_address'] ?: 'Non spécifiée',
                'shop_name' => $package['shop_name'] ?: null,
                'shop_address' => $package['shop_address'] ?: null
            ],
            'recipient' => [
                'name' => $package['onpackage_recipient_name'] ?: 'Non spécifié',
                'address' => $package['onpackage_destination_address'] ?: 'Non spécifiée'
            ],
            'details' => [
                'weight_kg' => $package['weight_kg'] ? floatval($package['weight_kg']) : 0,
                'dimensions_cm' => $package['dimensions_cm'] ? json_decode($package['dimensions_cm'], true) : null,
                'volume_m3' => $package['volume_m3'] ? floatval($package['volume_m3']) : 0,
                'is_priority' => ($package['is_priority'] === 't' || $package['is_priority'] === true || $package['is_priority'] === '1'),
                'is_fragile' => ($package['is_fragile'] === 't' || $package['is_fragile'] === true || $package['is_fragile'] === '1'),
                'declared_value' => $package['declared_value'] ? floatval($package['declared_value']) : 0
            ],
            'status' => [
                'name' => $package['status_name'] ?: 'Inconnu',
                'description' => $package['status_description'] ?: 'Aucune description'
            ],
            'current_office' => [
                'name' => $package['office_name'] ?: null,
                'address' => $package['office_address'] ?: null,
                'city' => $package['office_city'] ?: null
            ],
            'dates' => [
                'created_at' => $package['created_at'],
                'updated_at' => $package['updated_at'],
                'estimated_delivery' => $package['estimated_delivery_date'],
                'actual_delivery' => $package['actual_delivery_date']
            ]
        ]
    ];
      // Optionally get tracking history/transit events
    $historySQL = "
        SELECT 
            te.id,
            te.event_timestamp as timestamp,
            te.event_description as description,
            te.exception_reason as notes,
            o.name as office_name,
            o.city as office_city,
            s.status_name as status_name
        FROM transit_events te
        LEFT JOIN post_offices o ON te.office_id = o.id
        LEFT JOIN package_statuses s ON te.status_id = s.id
        WHERE te.package_id = :package_id
        ORDER BY te.event_timestamp DESC
    ";
    
    $historyStmt = $pdo->prepare($historySQL);
    $historyStmt->bindParam(':package_id', $package['id'], PDO::PARAM_INT);
    $historyStmt->execute();
    
    $history = $historyStmt->fetchAll(PDO::FETCH_ASSOC);
    $response['package']['history'] = $history;
    
    echo json_encode($response, JSON_PRETTY_PRINT);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Erreur de base de données: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Erreur interne: ' . $e->getMessage()
    ]);
}
?>
