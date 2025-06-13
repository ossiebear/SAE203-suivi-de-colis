<?php
// Oscar Collins 2025
// SAE203 groupe A2
// Step 1: Receive and validate the data from the frontend

// Set JSON response header
header('Content-Type: application/json');

// Enable CORS if needed
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

// Receive the parameters
$startID = isset($_GET['startID']) ? trim($_GET['startID']) : null;
$startType = isset($_GET['startType']) ? trim($_GET['startType']) : null;
$endID = isset($_GET['endID']) ? trim($_GET['endID']) : null;
$endType = isset($_GET['endType']) ? trim($_GET['endType']) : null;

// Initialize response
$response = [
    'status' => 'received',
    'validation' => []
];

// Basic validation
if (empty($startID)) {
    $response['validation'][] = 'startID is required';
}

if (empty($startType)) {
    $response['validation'][] = 'startType is required';
}

if (empty($endID)) {
    $response['validation'][] = 'endID is required';
}

if (empty($endType)) {
    $response['validation'][] = 'endType is required';
}

// Check if startType and endType are valid
$validTypes = ['office', 'shop'];
if (!empty($startType) && !in_array($startType, $validTypes)) {
    $response['validation'][] = 'startType must be either "office" or "shop"';
}

if (!empty($endType) && !in_array($endType, $validTypes)) {
    $response['validation'][] = 'endType must be either "office" or "shop"';
}

// Set status based on validation
if (empty($response['validation'])) {
    $response['status'] = 'valid';
    $response['message'] = 'All parameters are valid and ready for processing';

    // Step 2: Database connection and parent node lookup
    try {        // Include database configuration
        require_once '../DATA/DATABASE/CONFIG/config.php';
        require_once '../DATA/DATABASE/FUNCTIONS/db_connections.php';
        
        $pdo = db_connect();
        
        // Initialize node data structure
        $nodes = [
            'start' => [
                'extremity' => null,
                'parent' => null,
                'type' => $startType
            ],
            'end' => [
                'extremity' => null,
                'parent' => null,
                'type' => $endType
            ]
        ];
        
        // Get extremity nodes data from database
        $response['step2'] = 'Finding extremity nodes and their parents';
        
        // Find start extremity node
        if ($startType === 'office') {
            $stmt = $pdo->prepare("SELECT * FROM post_offices WHERE id = ?");
        } else {
            $stmt = $pdo->prepare("SELECT * FROM shops WHERE id = ?");
        }
        $stmt->execute([$startID]);
        $nodes['start']['extremity'] = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Find end extremity node
        if ($endType === 'office') {
            $stmt = $pdo->prepare("SELECT * FROM post_offices WHERE id = ?");
        } else {
            $stmt = $pdo->prepare("SELECT * FROM shops WHERE id = ?");
        }
        $stmt->execute([$endID]);
        $nodes['end']['extremity'] = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Check if extremity nodes were found
        if (!$nodes['start']['extremity']) {
            throw new Exception("Start {$startType} with ID {$startID} not found");
        }
        if (!$nodes['end']['extremity']) {
            throw new Exception("End {$endType} with ID {$endID} not found");
        }
        
        // Find parent nodes if they exist
        // Start node parent
        if (!empty($nodes['start']['extremity']['parent_office_acores_id'])) {
            $parentAcoresId = $nodes['start']['extremity']['parent_office_acores_id'];
            
            // Look for parent in post_offices table (parents are always offices)
            $stmt = $pdo->prepare("SELECT * FROM post_offices WHERE acores_id = ?");
            $stmt->execute([$parentAcoresId]);
            $nodes['start']['parent'] = $stmt->fetch(PDO::FETCH_ASSOC);
        }
        
        // End node parent
        if (!empty($nodes['end']['extremity']['parent_office_acores_id'])) {
            $parentAcoresId = $nodes['end']['extremity']['parent_office_acores_id'];
            
            // Look for parent in post_offices table (parents are always offices)
            $stmt = $pdo->prepare("SELECT * FROM post_offices WHERE acores_id = ?");
            $stmt->execute([$parentAcoresId]);
            $nodes['end']['parent'] = $stmt->fetch(PDO::FETCH_ASSOC);
        }
        
        // Add nodes data to response
        $response['nodes'] = $nodes;
        $response['debug'] = [
            'start_extremity_found' => !empty($nodes['start']['extremity']),
            'start_parent_acores_id' => $nodes['start']['extremity']['parent_office_acores_id'] ?? 'none',
            'start_parent_found' => !empty($nodes['start']['parent']),
            'end_extremity_found' => !empty($nodes['end']['extremity']),
            'end_parent_acores_id' => $nodes['end']['extremity']['parent_office_acores_id'] ?? 'none',
            'end_parent_found' => !empty($nodes['end']['parent'])
        ];
        
        // Step 3: Find root nodes and construct path
        $response['step3'] = 'Finding root nodes and constructing path';
        
        // Initialize root node variables
        $startRootNode = null;
        $endRootNode = null;
        $commonRootNode = null;
        
        // Function to calculate distance between two points using Haversine formula
        function calculateDistance($lat1, $lon1, $lat2, $lon2) {
            $earthRadius = 6371; // Earth's radius in kilometers
            
            $lat1Rad = deg2rad($lat1);
            $lon1Rad = deg2rad($lon1);
            $lat2Rad = deg2rad($lat2);
            $lon2Rad = deg2rad($lon2);
            
            $deltaLat = $lat2Rad - $lat1Rad;
            $deltaLon = $lon2Rad - $lon1Rad;
            
            $a = sin($deltaLat/2) * sin($deltaLat/2) + cos($lat1Rad) * cos($lat2Rad) * sin($deltaLon/2) * sin($deltaLon/2);
            $c = 2 * atan2(sqrt($a), sqrt(1-$a));
            
            return $earthRadius * $c;
        }          // Get all root nodes from the database
        $stmt = $pdo->prepare("SELECT * FROM department_offices");
        $stmt->execute();
        $rootNodes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($rootNodes)) {
            throw new Exception("No department offices found in database");
        }
        
        // Find closest root node for start side
        $startParent = $nodes['start']['parent'] ?? $nodes['start']['extremity'];
        if ($startParent && isset($startParent['latitude']) && isset($startParent['longitude'])) {
            $minDistance = PHP_FLOAT_MAX;
            foreach ($rootNodes as $rootNode) {
                if (isset($rootNode['latitude']) && isset($rootNode['longitude'])) {
                    $distance = calculateDistance(
                        $startParent['latitude'], 
                        $startParent['longitude'],
                        $rootNode['latitude'], 
                        $rootNode['longitude']
                    );
                    if ($distance < $minDistance) {
                        $minDistance = $distance;
                        $startRootNode = $rootNode;
                        $startRootNode['distance_from_parent'] = $distance;
                    }
                }
            }
        }
        
        // Find closest root node for end side
        $endParent = $nodes['end']['parent'] ?? $nodes['end']['extremity'];
        if ($endParent && isset($endParent['latitude']) && isset($endParent['longitude'])) {
            $minDistance = PHP_FLOAT_MAX;
            foreach ($rootNodes as $rootNode) {
                if (isset($rootNode['latitude']) && isset($rootNode['longitude'])) {
                    $distance = calculateDistance(
                        $endParent['latitude'], 
                        $endParent['longitude'],
                        $rootNode['latitude'], 
                        $rootNode['longitude']
                    );
                    if ($distance < $minDistance) {
                        $minDistance = $distance;
                        $endRootNode = $rootNode;
                        $endRootNode['distance_from_parent'] = $distance;
                    }
                }
            }
        }
        
        // Find common root node that bridges both sides
        if ($startRootNode && $endRootNode) {
            // If both sides have the same root node, use it
            if ($startRootNode['id'] === $endRootNode['id']) {
                $commonRootNode = $startRootNode;
            } else {
                // Find root node closest to both start and end root nodes
                $minTotalDistance = PHP_FLOAT_MAX;
                foreach ($rootNodes as $rootNode) {
                    if (isset($rootNode['latitude']) && isset($rootNode['longitude'])) {
                        $distanceToStart = calculateDistance(
                            $startRootNode['latitude'], 
                            $startRootNode['longitude'],
                            $rootNode['latitude'], 
                            $rootNode['longitude']
                        );
                        $distanceToEnd = calculateDistance(
                            $endRootNode['latitude'], 
                            $endRootNode['longitude'],
                            $rootNode['latitude'], 
                            $rootNode['longitude']
                        );
                        $totalDistance = $distanceToStart + $distanceToEnd;
                        
                        if ($totalDistance < $minTotalDistance) {
                            $minTotalDistance = $totalDistance;
                            $commonRootNode = $rootNode;
                            $commonRootNode['distance_to_start_root'] = $distanceToStart;
                            $commonRootNode['distance_to_end_root'] = $distanceToEnd;
                            $commonRootNode['total_distance'] = $totalDistance;
                        }
                    }
                }
            }
        }
        
        // Construct the complete path
        $path = [];
        
        // Start extremity -> Start parent (if exists) -> Start root -> Common root -> End root -> End parent (if exists) -> End extremity
        
        // Add start extremity
        $path[] = [
            'node_type' => 'extremity',
            'side' => 'start',
            'data' => $nodes['start']['extremity']
        ];
          // Add start parent if exists and different from extremity
        if ($nodes['start']['parent'] && $nodes['start']['parent']['id'] !== $nodes['start']['extremity']['id']) {
            // Check if this parent is also the root node
            $isParentAlsoRoot = $startRootNode && $startRootNode['id'] === $nodes['start']['parent']['id'];
            
            $path[] = [
                'node_type' => $isParentAlsoRoot ? 'parent_root' : 'parent',
                'side' => 'start',
                'data' => $nodes['start']['parent']
            ];
        }
        
        // Add start root if different from parent/extremity
        if ($startRootNode && (!$nodes['start']['parent'] || $startRootNode['id'] !== $nodes['start']['parent']['id'])) {
            $path[] = [
                'node_type' => 'root',
                'side' => 'start',
                'data' => $startRootNode
            ];
        }
        
        // Add common root if different from start/end roots
        if ($commonRootNode && 
            (!$startRootNode || $commonRootNode['id'] !== $startRootNode['id']) && 
            (!$endRootNode || $commonRootNode['id'] !== $endRootNode['id'])) {
            $path[] = [
                'node_type' => 'common_root',
                'side' => 'bridge',
                'data' => $commonRootNode
            ];
        }
        
        // Add end root if different from common root and start root
        if ($endRootNode && 
            (!$commonRootNode || $endRootNode['id'] !== $commonRootNode['id']) &&
            (!$startRootNode || $endRootNode['id'] !== $startRootNode['id'])) {
            $path[] = [
                'node_type' => 'root',
                'side' => 'end',
                'data' => $endRootNode
            ];
        }
        
        // Add end parent if exists and different from extremity and root
        if ($nodes['end']['parent'] && 
            $nodes['end']['parent']['id'] !== $nodes['end']['extremity']['id'] &&
            (!$endRootNode || $nodes['end']['parent']['id'] !== $endRootNode['id'])) {
            $path[] = [
                'node_type' => 'parent',
                'side' => 'end',
                'data' => $nodes['end']['parent']
            ];
        }
        
        // Add end extremity
        $path[] = [
            'node_type' => 'extremity',
            'side' => 'end',
            'data' => $nodes['end']['extremity']
        ];
        
        // Add path data to response
        $response['path'] = $path;
        $response['path_summary'] = [
            'total_nodes' => count($path),
            'start_root_node' => $startRootNode ? $startRootNode['id'] : null,
            'end_root_node' => $endRootNode ? $endRootNode['id'] : null,
            'common_root_node' => $commonRootNode ? $commonRootNode['id'] : null,
            'path_complete' => !empty($path)
        ];
        
    } catch (Exception $e) {
        $response['status'] = 'error';
        $response['message'] = 'Database error: ' . $e->getMessage();
    }
    
} else {
    $response['status'] = 'error';
    $response['message'] = 'Validation errors found';
}

// Output the response
echo json_encode($response, JSON_PRETTY_PRINT);
?>