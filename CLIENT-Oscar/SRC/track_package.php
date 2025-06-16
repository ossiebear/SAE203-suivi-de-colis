<?php
/**
 * Package Tracking Backend API
 * 
 * SAE203 Package Tracking System - Core Tracking Functionality
 * Provides RESTful API endpoint for package tracking by tracking number
 * 
 * Features:
 * - GET request handling for package lookup
 * - Comprehensive package data retrieval with joins
 * - Transit event history tracking
 * - JSON response formatting
 * - Error handling and validation
 * - CORS support for frontend integration
 * 
 * API Endpoint: GET /track_package.php?tracking_number={TRACKING_NUMBER}
 * Response Format: JSON with package details, status, and history
 * 
 * Author: Oscar Collins, SAE203 Group A2
 * Date: 2025
 */

// Enable comprehensive error reporting for debugging
// TODO: Disable in production environment
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set response headers for JSON API and CORS support
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

// Include database connection functionality
require_once '../DATA/DATABASE/FUNCTIONS/db_connections.php';

// Validate that tracking number parameter is provided
if (!isset($_GET['tracking_number']) || empty($_GET['tracking_number'])) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'Numéro de suivi requis'
    ]);
    exit;
}

// Sanitize and prepare tracking number for database query
$trackingNumber = trim($_GET['tracking_number']);

try {
    // Establish database connection
    $pdo = db_connect();
    
    /**
     * Comprehensive Package Data Query
     * 
     * Retrieves complete package information including:
     * - Basic package details (tracking number, dimensions, weight, etc.)
     * - Current status and office location
     * - Sender and recipient information
     * - Shop details (if sent from registered shop)
     * - Delivery preferences (priority, fragile handling)
     * 
     * Uses LEFT JOINs to ensure data is returned even if some related records are missing
     */
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
            -- Current office location information
            o.name as office_name,
            o.street_address as office_address,
            o.city as office_city,
            -- Shop information (if package originated from registered shop)
            sh.name as shop_name,
            sh.address as shop_address
        FROM packages p
        LEFT JOIN package_statuses s ON p.current_status_id = s.id
        LEFT JOIN post_offices o ON p.current_office_id = o.id
        LEFT JOIN shops sh ON p.sender_shop_id = sh.id
        WHERE p.tracking_number = :tracking_number
    ";
    
    // Prepare and execute the query with parameter binding for security
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':tracking_number', $trackingNumber, PDO::PARAM_STR);
    $stmt->execute();
    
    // Fetch the package data
    $package = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Handle case where no package is found
    if (!$package) {
        echo json_encode([
            'success' => false,
            'error' => 'Colis non trouvé. Vérifiez votre numéro de suivi.'
        ]);
        exit;
    }
    
    // Debug logging for development (remove in production)
    error_log("Raw package data: " . print_r($package, true));    /**
     * Format API Response Data
     * 
     * Structures the database results into a standardized JSON response format
     * Includes data type conversion and null handling for frontend consumption
     */
    $response = [
        'success' => true,
        'package' => [
            'id' => $package['id'],
            'tracking_number' => $package['tracking_number'],
            
            // Sender information
            'sender' => [
                'name' => $package['onpackage_sender_name'] ?: 'Non spécifié',
                'address' => $package['onpackage_sender_address'] ?: 'Non spécifiée',
                'shop_name' => $package['shop_name'] ?: null,
                'shop_address' => $package['shop_address'] ?: null
            ],
            
            // Recipient information
            'recipient' => [
                'name' => $package['onpackage_recipient_name'] ?: 'Non spécifié',
                'address' => $package['onpackage_destination_address'] ?: 'Non spécifiée'
            ],
            
            // Package physical details
            'details' => [
                'weight_kg' => $package['weight_kg'] ? floatval($package['weight_kg']) : 0,                // Dimensions stored as JSON, decode for frontend use
                'dimensions_cm' => $package['dimensions_cm'] ? json_decode($package['dimensions_cm'], true) : null,
                'volume_m3' => $package['volume_m3'] ? floatval($package['volume_m3']) : 0,
                
                // Boolean flags for special handling
                'is_priority' => ($package['is_priority'] === 't' || $package['is_priority'] === true || $package['is_priority'] === '1'),
                'is_fragile' => ($package['is_fragile'] === 't' || $package['is_fragile'] === true || $package['is_fragile'] === '1'),
                'declared_value' => $package['declared_value'] ? floatval($package['declared_value']) : 0
            ],
            
            // Current status information
            'status' => [
                'name' => $package['status_name'] ?: 'Inconnu',
                'description' => $package['status_description'] ?: 'Aucune description'
            ],
            
            // Current location information
            'current_office' => [
                'name' => $package['office_name'] ?: null,
                'address' => $package['office_address'] ?: null,
                'city' => $package['office_city'] ?: null
            ],
            
            // Important dates for tracking
            'dates' => [
                'created_at' => $package['created_at'],
                'updated_at' => $package['updated_at'],
                'estimated_delivery' => $package['estimated_delivery_date'],
                'actual_delivery' => $package['actual_delivery_date']
            ]
        ]
    ];    /**
     * Retrieve Package Transit History
     * 
     * Gets chronological list of all events related to this package
     * Includes location changes, status updates, and delivery attempts
     * Ordered by timestamp (most recent first) for better user experience
     */
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
    
    // Fetch all transit events for this package
    $history = $historyStmt->fetchAll(PDO::FETCH_ASSOC);
    $response['package']['history'] = $history;
    
    // Output formatted JSON response
    echo json_encode($response, JSON_PRETTY_PRINT);
    
} catch (PDOException $e) {
    // Handle database-specific errors
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Erreur de base de données: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    // Handle general application errors
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Erreur interne: ' . $e->getMessage()
    ]);
}
?>
