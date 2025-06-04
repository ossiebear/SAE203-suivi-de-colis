<?php
// Oscar Collins 2025
// SAE203 groupe A2
// AI usage: many functions copied from old project, written manually (SAE105), translated with help from AI
//           comments/docstrings written by AI. GPT4.1-mini

// Load the postal data CSV file into an array of rows.
// Each row is parsed as an array of fields using str_getcsv.
// The file path is relative to the parent directory of the current script.
$csv = array_map('str_getcsv', file(dirname(__DIR__) . '/DATA/LOCAL/postaldata.csv'));

// Remove the first row which is assumed to be the header row.
array_shift($csv);

// Build an associative lookup array indexed by node ID (first column of each row).
// This allows quick access to any node's data by its ID.
$byId = [];
foreach ($csv as $row) {
    $byId[$row[0]] = $row;
}

// Load the root nodes CSV file similarly, parsing each row.
// Root nodes represent key reference points in the data.
$rootNodes = array_map('str_getcsv', file(dirname(__DIR__) . '/DATA/LOCAL/root_nodes.csv'));

// Extract the IDs of the root nodes (first column of each root node row).
$rootIds = array_column($rootNodes, 0);

// Retrieve the start and finish node IDs from GET or POST parameters.
// These represent the extremities of the journey to be calculated.
$startId = $_GET['start'] ?? $_POST['start'] ?? null;
$finishId = $_GET['finish'] ?? $_POST['finish'] ?? null;

// If either start or finish ID is missing, return an error as JSON and exit.
if (!$startId || !$finishId) {
    echo json_encode(['error' => 'Missing start or finish']);
    exit;
}

/**
 * Calculate the great-circle distance between two latitude/longitude points
 * using the Haversine formula.
 *
 * @param float $lat1 Latitude of point 1 in degrees
 * @param float $lon1 Longitude of point 1 in degrees
 * @param float $lat2 Latitude of point 2 in degrees
 * @param float $lon2 Longitude of point 2 in degrees
 * @return float Distance in kilometers
 */
function haversine($lat1, $lon1, $lat2, $lon2) {
    $R = 6371; // Earth radius in kilometers
    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);
    $a = sin($dLat/2) ** 2 +
         cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
         sin($dLon/2) ** 2;
    $c = 2 * atan2(sqrt($a), sqrt(1-$a));
    return $R * $c;
}

/**
 * Find the parent node of a given node by its ID.
 * The parent ID is assumed to be in column index 3 of the node's data row.
 *
 * @param string $nodeId The node ID to find the parent for
 * @param array $byId Associative array of nodes indexed by ID
 * @return array|null The parent node row or null if not found
 */
function findParent($nodeId, $byId) {
    if (!isset($byId[$nodeId])) return null;
    $parentId = $byId[$nodeId][3] ?? null;
    return ($parentId && isset($byId[$parentId])) ? $byId[$parentId] : null;
}

/**
 * Find the closest root node to a given node based on geographic distance.
 *
 * @param array|null $node The node to find closest root for
 * @param array $rootIds Array of root node IDs
 * @param array $byId Associative array of nodes indexed by ID
 * @return array|null The closest root node or null if none found
 */
function findClosestRoot($node, $rootIds, $byId) {
    if (!$node) return null;
    $lat1 = $node[11];
    $lon1 = $node[12];
    if (!is_numeric($lat1) || !is_numeric($lon1)) return null;

    $closestRoot = null;
    $minDist = PHP_INT_MAX;
    foreach ($rootIds as $rootId) {
        if (!isset($byId[$rootId])) continue;
        $root = $byId[$rootId];
        $lat2 = $root[11];
        $lon2 = $root[12];
        if (!is_numeric($lat2) || !is_numeric($lon2)) continue;
        $dist = haversine($lat1, $lon1, $lat2, $lon2);
        if ($dist < $minDist) {
            $minDist = $dist;
            $closestRoot = $root;
        }
    }
    return $closestRoot;
}

/**
 * Find a common root node closest to the midpoint between two root nodes.
 * This helps identify a shared reference point for a journey between two nodes.
 *
 * @param array|null $rootA First root node
 * @param array|null $rootB Second root node
 * @param array $rootIds Array of root node IDs
 * @param array $byId Associative array of nodes indexed by ID
 * @return array|null The common root node closest to midpoint or null if none found
 */
function findCommonRoot($rootA, $rootB, $rootIds, $byId) {
    if (!$rootA || !$rootB) return null;
    $midLat = ($rootA[11] + $rootB[11]) / 2;
    $midLon = ($rootA[12] + $rootB[12]) / 2;

    $closestRoot = null;
    $minDist = PHP_INT_MAX;
    foreach ($rootIds as $rootId) {
        if (!isset($byId[$rootId])) continue;
        $root = $byId[$rootId];
        $lat = $root[11];
        $lon = $root[12];
        if (!is_numeric($lat) || !is_numeric($lon)) continue;
        $dist = haversine($midLat, $midLon, $lat, $lon);
        if ($dist < $minDist) {
            $minDist = $dist;
            $closestRoot = $root;
        }
    }
    return $closestRoot;
}

/**
 * Build a path from an extremity node up to its parent and then to the closest root node.
 * The path is an array of nodes representing this route.
 *
 * @param string $extremityId The starting node ID
 * @param array $byId Associative array of nodes indexed by ID
 * @param array $rootIds Array of root node IDs
 * @return array The path as an array of node rows
 */
function buildPathToRoot($extremityId, $byId, $rootIds) {
    if (!isset($byId[$extremityId])) return [];

    $path = [];
    $extremity = $byId[$extremityId];
    $path[] = $extremity;

    // Find the parent node of the extremity
    $parent = findParent($extremityId, $byId);
    if ($parent) {
        $path[] = $parent;
    } else {
        // If no parent found, fallback to using the extremity itself for root search
        $parent = $extremity;
    }

    // Find the closest root node to the parent node
    $root = findClosestRoot($parent, $rootIds, $byId);
    if ($root && $root[0] !== $parent[0]) {
        $path[] = $root;
    }

    return $path;
}

/**
 * Build the full journey path from start to finish nodes via a common root node.
 * The journey includes the start path, common root, finish root, and finish path.
 *
 * @param string $startId Start node ID
 * @param string $finishId Finish node ID
 * @param array $byId Associative array of nodes indexed by ID
 * @param array $rootIds Array of root node IDs
 * @return array The full journey as an array of node rows
 */
function buildFullJourney($startId, $finishId, $byId, $rootIds) {
    // Build paths from start and finish nodes to their respective roots
    $startPath = buildPathToRoot($startId, $byId, $rootIds);
    $finishPath = buildPathToRoot($finishId, $byId, $rootIds);

    // Identify the root nodes at the end of each path
    $startRoot = end($startPath);
    $finishRoot = end($finishPath);

    // Find a common root node closest to the midpoint between the two roots
    $commonRoot = findCommonRoot($startRoot, $finishRoot, $rootIds, $byId);

    $journey = [];

    // Add all nodes from the start path
    foreach ($startPath as $node) {
        $journey[] = $node;
    }

    // Add the common root node if it is distinct from the start root
    if ($commonRoot && $commonRoot[0] !== $startRoot[0]) {
        $journey[] = $commonRoot;
    }

    // Add the finish root node if it is distinct from the common root
    if ($finishRoot && $finishRoot[0] !== $commonRoot[0]) {
        $journey[] = $finishRoot;
    }

    // Add the finish path nodes in reverse order, excluding roots already added
    $finishPathReversed = array_reverse($finishPath);
    foreach ($finishPathReversed as $node) {
        if ($node[0] !== $finishRoot[0] && $node[0] !== $commonRoot[0]) {
            $journey[] = $node;
        }
    }

    return $journey;
}

// Build the journey from the provided start and finish node IDs.
$journey = buildFullJourney($startId, $finishId, $byId, $rootIds);

// Output the journey as a JSON object with a 'journey' key.
echo json_encode(['journey' => $journey]);