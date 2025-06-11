<?php
// savePathToDB.php
// Author: Oscar Collins 2025
// SAE203 Group A2
// Comments revised by AI (GitHub Copilot)

// This script handles the insertion of a new package record into the database.
// It expects a GET request with specific parameters (see below) and returns a JSON response indicating success or failure.
// The response will include an error message if validation fails or the database operation is unsuccessful.
// On success, the response contains the tracking number of the newly inserted package.
//
// Example response on success:
//   { "success": true, "error": null, "results": "1234567890" }
// Example response on error:
//   { "success": false, "error": "Missing required fields: tracking_number", "results": null }



/*
EXPECTED GET REQUEST FORMAT:

GET /CLIENT-Oscar/SRC/savePathToDB.php?tracking_number=1234567890&current_status_id=1&current_office_id=2&recipient_client_id=3&onpackage_sender_name=John%20Doe&onpackage_sender_address=123%20Main%20St&sender_shop_id=4&onpackage_recipient_name=Jane%20Smith&onpackage_destination_address=456%20Elm%20St&weight_kg=2.5&dimensions_cm=%7B%22length%22%3A30%2C%22width%22%3A20%2C%22height%22%3A10%7D&volume_m3=0.006&is_priority=true&is_fragile=false&declared_value=100.00&estimated_delivery_date=2025-06-15&actual_delivery_date=

Required fields:
- tracking_number (string)
- current_status_id (int)
- current_office_id (int)
- recipient_client_id (int)
- onpackage_sender_name (string)
- onpackage_sender_address (string)
- sender_shop_id (int)
- onpackage_recipient_name (string)
- onpackage_destination_address (string)

Optional fields:
- weight_kg (float)
- dimensions_cm (JSON string: {"length":X,"width":Y,"height":Z})
- volume_m3 (float)
- is_priority (bool)
- is_fragile (bool)
- declared_value (float)
- estimated_delivery_date (YYYY-MM-DD)
- actual_delivery_date (YYYY-MM-DD)
*/





// Include the database connection utility, which provides db_connect().
include dirname(__DIR__) . '/DATA/DATABASE/FUNCTIONS/db_connections.php';

// Set the response header to indicate JSON output.
header('Content-Type: application/json');

// Only allow GET requests for this endpoint.
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode([
        'success' => false,
        'error' => 'Invalid request method. Use GET.',
        'results' => null
    ]);
    exit;
}

// Retrieve input parameters from the GET request.
$input = $_GET;

// Required fields for creating a new package record.
$requiredFields = [ 
    'tracking_number',
    'current_status_id',
    'current_office_id',
    'recipient_client_id',
    'onpackage_sender_name',
    'onpackage_sender_address',
    'sender_shop_id',
    'onpackage_recipient_name',
    'onpackage_destination_address',
];

// Check for missing required fields.
$missingFields = array_filter($requiredFields, function($field) use ($input) {
    return !isset($input[$field]);
});
if (!empty($missingFields)) {
    echo json_encode([
        'success' => false,
        'error' => 'Missing required fields: ' . implode(', ', $missingFields),
        'results' => null
    ]);
    exit;
}

// Optional fields with their default values if not provided.
$optionalFields = [
    'weight_kg' => null, // float
    'dimensions_cm' => null, // JSON string: {"length":X,"width":Y,"height":Z}
    'volume_m3' => null, // float
    'is_priority' => false, // boolean
    'is_fragile' => false, // boolean
    'declared_value' => null, // float
    'estimated_delivery_date' => null, // string (YYYY-MM-DD)
    'actual_delivery_date' => null // string (YYYY-MM-DD)
];
foreach ($optionalFields as $field => $default) {
    if (!isset($input[$field])) {
        $input[$field] = $default;
    }
}

// Parse booleans and numbers from GET parameters (all GET params are strings by default).
$input['is_priority'] = filter_var($input['is_priority'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
$input['is_fragile'] = filter_var($input['is_fragile'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
$input['weight_kg'] = isset($input['weight_kg']) ? floatval($input['weight_kg']) : null;
$input['volume_m3'] = isset($input['volume_m3']) ? floatval($input['volume_m3']) : null;
$input['declared_value'] = isset($input['declared_value']) ? floatval($input['declared_value']) : null;

// If present, decode dimensions_cm from JSON string to array, then re-encode for DB storage.
if (!empty($input['dimensions_cm'])) {
    $decoded = json_decode($input['dimensions_cm'], true);
    if (is_array($decoded)) {
        $input['dimensions_cm'] = json_encode($decoded);
    } else {
        $input['dimensions_cm'] = null;
    }
}

try {
    $DBconn_savePathToDB = db_connect();
    if ($DBconn_savePathToDB === null) {
        throw new Exception('Database connection failed.');
    }

    $query = $DBconn_savePathToDB->prepare(
        'INSERT INTO "packages" (
            tracking_number, current_status_id, current_office_id, recipient_client_id,
            onpackage_sender_name, onpackage_sender_address, sender_shop_id,
            onpackage_recipient_name, onpackage_destination_address, weight_kg, dimensions_cm, volume_m3,
            is_priority, is_fragile, declared_value, estimated_delivery_date, actual_delivery_date
        ) VALUES (
            :tracking_number, :current_status_id, :current_office_id, :recipient_client_id,
            :onpackage_sender_name, :onpackage_sender_address, :sender_shop_id,
            :onpackage_recipient_name, :onpackage_destination_address, :weight_kg, :dimensions_cm, :volume_m3,
            :is_priority, :is_fragile, :declared_value, :estimated_delivery_date, :actual_delivery_date
        )'
    );

    $query->bindParam(':tracking_number', $input['tracking_number']);
    $query->bindParam(':current_status_id', $input['current_status_id'], PDO::PARAM_INT);
    $query->bindParam(':current_office_id', $input['current_office_id'], PDO::PARAM_INT);
    $query->bindParam(':recipient_client_id', $input['recipient_client_id'], PDO::PARAM_INT);
    $query->bindParam(':onpackage_sender_name', $input['onpackage_sender_name']);
    $query->bindParam(':onpackage_sender_address', $input['onpackage_sender_address']);
    $query->bindParam(':sender_shop_id', $input['sender_shop_id'], PDO::PARAM_INT);
    $query->bindParam(':onpackage_recipient_name', $input['onpackage_recipient_name']);
    $query->bindParam(':onpackage_destination_address', $input['onpackage_destination_address']);
    $query->bindParam(':weight_kg', $input['weight_kg']);
    $query->bindParam(':dimensions_cm', $input['dimensions_cm']);
    $query->bindParam(':volume_m3', $input['volume_m3']);
    $query->bindParam(':is_priority', $input['is_priority'], PDO::PARAM_BOOL);
    $query->bindParam(':is_fragile', $input['is_fragile'], PDO::PARAM_BOOL);
    $query->bindParam(':declared_value', $input['declared_value']);
    $query->bindParam(':estimated_delivery_date', $input['estimated_delivery_date']);
    $query->bindParam(':actual_delivery_date', $input['actual_delivery_date']);

    $query->execute();

    if ($query->rowCount() > 0) {
        echo json_encode([
            'success' => true,
            'error' => null,
            'results' => $input['tracking_number']
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'error' => 'Failed to save package to the database',
            'results' => null
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'results' => null
    ]);
}