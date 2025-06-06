<?php
require_once __DIR__ . '/../DATA/DATABASE/FUNCTIONS/db_connections.php';

// Check if trackingNumber is set in the GET parameters
if (isset($_GET['trackingNumber'])) {
    $trackingNumber = $_GET['trackingNumber'];
    
    // Validate the tracking number format (12-character hexadecimal string)
    if (preg_match('/^[a-fA-F0-9]{12}$/', $trackingNumber)) {
        // Valid tracking number, proceed with logic
        // Connect to the database
        $pdo = db_connect();
        // Query the journey for the given tracking number
        $stmt = $pdo->prepare('SELECT "path" FROM "Packages" WHERE "trackingNumber" = :trackingNumber');
        $stmt->execute([':trackingNumber' => $trackingNumber]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (count($results) === 1 && isset($results[0]['path'])) {
            $journey = json_decode($results[0]['path'], true);
            // Query all post office details for the journey identifiants
            if (is_array($journey) && count($journey) > 0) {
                // Prepare placeholders for the IN clause
                $placeholders = implode(',', array_fill(0, count($journey), '?'));
                $stmt2 = $pdo->prepare('SELECT * FROM post_offices WHERE identifiant_a IN (' . $placeholders . ')');
                $stmt2->execute($journey);
                $offices = $stmt2->fetchAll(PDO::FETCH_ASSOC);
                // Reorder $offices to match the order in $journey
                $officeMap = [];
                foreach ($offices as $office) {
                    // Use the correct key for your schema: 'identifiant_a' or 'site_id'
                    $key = isset($office['identifiant_a']) ? $office['identifiant_a'] : $office['site_id'];
                    $officeMap[$key] = $office;
                }
                $orderedOffices = [];
                foreach ($journey as $id) {
                    if (isset($officeMap[$id])) {
                        $orderedOffices[] = $officeMap[$id];
                    }
                }
                echo json_encode([
                    'success' => true,
                    'error' => null,
                    'results' => $orderedOffices,
                    'trackingNumber' => $trackingNumber
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'error' => 'No journey data found.',
                    'results' => [],
                    'trackingNumber' => $trackingNumber
                ]);
            }
        } elseif (count($results) > 1) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'Multiple journeys found for this tracking number. Database integrity error.',
                'results' => $results,
                'trackingNumber' => $trackingNumber
            ]);
        } else {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'error' => 'Tracking number not found.',
                'results' => [],
                'trackingNumber' => $trackingNumber
            ]);
        }
    } else {
        // Invalid tracking number format
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid tracking number format.']);
    }
} else {
    // trackingNumber parameter is missing
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing trackingNumber parameter.']);
}





















?>