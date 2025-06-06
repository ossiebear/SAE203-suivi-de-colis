<?php
// Oscar Collins 2025
// SAE203 groupe A2
// AI usage: Written fully manually, comments written by AI. GPT4.1-mini 

// Include the database connection functions from the specified path.
// This file should provide the db_connect() function to establish a DB connection.
include dirname(__DIR__) . '/DATA/DATABASE/FUNCTIONS/db_connections.php';

// Set the HTTP response header to indicate JSON content.
// This ensures the client interprets the response correctly.
header('Content-Type: application/json');

// Check if 'pathData' is provided in the GET request.
// 'pathData' is expected to be a JSON string representing an array of path nodes.
if (isset($_GET['pathData'])) {
    // Decode the JSON string into a PHP array.
    $pathData = json_decode($_GET['pathData'], true);

    // Validate that decoding was successful and the result is an array.
    if (!is_array($pathData)) {
        // Return an error response if the format is invalid.
        echo json_encode([
            'success' => false,
            'error' => 'Invalid pathData format',
            'results' => null
        ]);
        exit;
    }
} else {
    // Return an error response if 'pathData' is missing from the request.
    echo json_encode([
        'success' => false,
        'error' => 'Missing pathData',
        'results' => null
    ]);
    exit;
}

// Generate a unique tracking code as a 12-character hexadecimal string (6 random bytes).
$trackingCode = strtoupper(bin2hex(random_bytes(6)));

try {
    // Establish a database connection using the included function.
    $DBconn_savePathToDB = db_connect();

    // Check if the connection was successful.
    if ($DBconn_savePathToDB === null) {
        throw new Exception('Database connection failed.');
    }

    // Encode the path data array back to JSON for storage in the database.
    $pathDataJson = json_encode($pathData);

    // Prepare an SQL INSERT statement to save the tracking number and path data.
    // The table is "Packages" with columns "trackingNumber" and "path".
    $query = $DBconn_savePathToDB->prepare(
        'INSERT INTO "Packages" ("trackingNumber", "path") VALUES (:trackingNumber, :pathData)'
    );

    // Bind the tracking code and path JSON to the prepared statement parameters.
    $query->bindParam(':trackingNumber', $trackingCode);
    $query->bindParam(':pathData', $pathDataJson, PDO::PARAM_STR);

    // Execute the query to insert the data.
    $query->execute();

    // Check if the insertion affected any rows (i.e., was successful).
    if ($query->rowCount() > 0) {
        // Return a success response with the generated tracking code.
        echo json_encode([
            'success' => true,
            'error' => null,
            'results' => $trackingCode
        ]);
    } else {
        // Return an error if the insertion failed without an exception.
        echo json_encode([
            'success' => false,
            'error' => 'Failed to save tracking code to the database',
            'results' => null
        ]);
    }
} catch (Exception $e) {
    // Catch any exceptions (e.g., DB errors) and return the error message as JSON.
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'results' => null
    ]);
}