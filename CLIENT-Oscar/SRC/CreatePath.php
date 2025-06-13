<?php
// Oscar Collins 2025
// SAE203 groupe A2
// AI usage: many functions copied from old project, written manually (SAE105), translated with help from AI
//           comments/docstrings written by AI. GPT4.1-mini

// This script builds a journey path between two post office nodes using the database.
// It only loads the nodes needed for the journey (start, finish, their parents, and root nodes).

require_once dirname(__DIR__) . '/DATA/DATABASE/FUNCTIONS/db_connections.php';
$pdo = db_connect();

// Retrieve the start and finish node IDs and their types from GET parameters.
$startId = $_GET['start'] ?? null;
$finishId = $_GET['finish'] ?? null;
$startType = $_GET['start_type'] ?? 'office'; // Default to 'office' for backward compatibility
$finishType = $_GET['finish_type'] ?? 'office'; // Default to 'office' for backward compatibility

// Validate input.
if (!$startId || !$finishId) {
    echo json_encode([
        'success' => false,
        'error' => 'Missing start or finish',
        'results' => null
    ]);
    exit;
}

// Validate types
if (!in_array($startType, ['office', 'shop']) || !in_array($finishType, ['office', 'shop'])) {
    echo json_encode([
        'success' => false,
        'error' => 'Invalid start_type or finish_type. Must be "office" or "shop"',
        'results' => null
    ]);
    exit;
}

// Helper to fetch parent ID for a node from the database.
function getParentId($nodeId, $pdo) {
    $stmt = $pdo->prepare('SELECT parent_office_acores_id FROM post_offices WHERE acores_id = ?');
    $stmt->execute([$nodeId]);
    $parentId = $stmt->fetchColumn();
    return $parentId ?: null;
}

/**
 * Get the ACORES ID for a shop by its database ID.
 * This assumes shops will have a parent_office_acores_id field that points to their nearest post office.
 * @param int $shopId The shop's database ID
 * @param PDO $pdo Database connection
 * @return string|null The ACORES ID of the shop's parent post office, or null if not found
 */
function getShopParentAcoresId($shopId, $pdo) {
    // Using parent_office_acores_id to match the naming convention in post_offices table
    $stmt = $pdo->prepare('SELECT parent_office_acores_id FROM shops WHERE id = ?');
    $stmt->execute([$shopId]);
    $parentAcoresId = $stmt->fetchColumn();
    return $parentAcoresId ?: null;
}

/**
 * Convert a shop or office identifier to a post office ACORES ID for path calculation.
 * @param string $nodeId The node identifier (shop ID or office ACORES ID)
 * @param string $nodeType The type of node ('shop' or 'office')
 * @param PDO $pdo Database connection
 * @return string|null The ACORES ID to use for path calculation
 */
function getPathNodeId($nodeId, $nodeType, $pdo) {
    if ($nodeType === 'office') {
        // For offices, use the ACORES ID directly
        return $nodeId;
    } elseif ($nodeType === 'shop') {
        // For shops, get their parent post office ACORES ID
        return getShopParentAcoresId($nodeId, $pdo);
    }
    return null;
}

// Convert shop/office IDs to post office ACORES IDs for path calculation
$startAcoresId = getPathNodeId($startId, $startType, $pdo);
$finishAcoresId = getPathNodeId($finishId, $finishType, $pdo);

// Validate that we got valid ACORES IDs
if (!$startAcoresId || !$finishAcoresId) {
    echo json_encode([
        'success' => false,
        'error' => 'Could not resolve start or finish location to a post office node',
        'results' => null
    ]);
    exit;
}

// Build the list of needed node IDs: start, finish, their parents, and all root nodes.
$neededIds = [$startAcoresId, $finishAcoresId];
$rootNodes = array_map('str_getcsv', file(dirname(__DIR__) . '/DATA/LOCAL/root_nodes.csv'));
$rootIds = array_column($rootNodes, 0);
$neededIds = array_unique(array_merge($neededIds, $rootIds));

// Add parents of start and finish (1 level up).
foreach ([$startAcoresId, $finishAcoresId] as $id) {
    $parentId = getParentId($id, $pdo);
    if ($parentId) $neededIds[] = $parentId;
}
$neededIds = array_unique($neededIds);

// Fetch only the needed nodes from the database.
$in = str_repeat('?,', count($neededIds) - 1) . '?';
$stmt = $pdo->prepare("SELECT * FROM post_offices WHERE acores_id IN ($in)");
$stmt->execute($neededIds);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Build associative lookup array indexed by identifiant_a.
$byId = [];
foreach ($rows as $row) {
    $byId[$row['acores_id']] = $row;
}

/**
 * Calculate the great-circle distance between two latitude/longitude points using the Haversine formula.
 * @param float $lat1 Latitude of point 1 in degrees
 * @param float $lon1 Longitude of point 1 in degrees
 * @param float $lat2 Latitude of point 2 in degrees
 * @param float $lon2 Longitude of point 2 in degrees
 * @return float Distance in kilometers
 */
function haversine($lat1, $lon1, $lat2, $lon2) {
    $R = 6371;
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
 * @param string $nodeId The node ID to find the parent for
 * @param array $byId Associative array of nodes indexed by ID
 * @return array|null The parent node row or null if not found
 */
function findParent($nodeId, $byId) {
    if (!isset($byId[$nodeId])) return null;
    $parentId = $byId[$nodeId]['parent_office_acores_id'] ?? null;
    return ($parentId && isset($byId[$parentId])) ? $byId[$parentId] : null;
}

/**
 * Find the closest root node to a given node based on geographic distance.
 * @param array|null $node The node to find closest root for
 * @param array $rootIds Array of root node IDs
 * @param array $byId Associative array of nodes indexed by ID
 * @return array|null The closest root node or null if none found
 */
function findClosestRoot($node, $rootIds, $byId) {
    if (!$node) return null;
    $lat1 = $node['latitude'];
    $lon1 = $node['longitude'];
    if (!is_numeric($lat1) || !is_numeric($lon1)) return null;
    $closestRoot = null;
    $minDist = PHP_INT_MAX;
    foreach ($rootIds as $rootId) {
        if (!isset($byId[$rootId])) continue;
        $root = $byId[$rootId];
        $lat2 = $root['latitude'];
        $lon2 = $root['longitude'];
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
 * @param array|null $rootA First root node
 * @param array|null $rootB Second root node
 * @param array $rootIds Array of root node IDs
 * @param array $byId Associative array of nodes indexed by ID
 * @return array|null The common root node closest to midpoint or null if none found
 */
function findCommonRoot($rootA, $rootB, $rootIds, $byId) {
    if (!$rootA || !$rootB) return null;
    $midLat = ($rootA['latitude'] + $rootB['latitude']) / 2;
    $midLon = ($rootA['longitude'] + $rootB['longitude']) / 2;
    $closestRoot = null;
    $minDist = PHP_INT_MAX;
    foreach ($rootIds as $rootId) {
        if (!isset($byId[$rootId])) continue;
        $root = $byId[$rootId];
        $lat = $root['latitude'];
        $lon = $root['longitude'];
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
    $parent = findParent($extremityId, $byId);
    if ($parent) {
        $path[] = $parent;
    } else {
        $parent = $extremity;
    }
    $root = findClosestRoot($parent, $rootIds, $byId);
    if ($root && $root['acores_id'] !== $parent['acores_id']) {
        $path[] = $root;
    }
    return $path;
}

/**
 * Build the full journey path from start to finish nodes via a common root node.
 * @param string $startId Start node ID
 * @param string $finishId Finish node ID
 * @param array $byId Associative array of nodes indexed by ID
 * @param array $rootIds Array of root node IDs
 * @return array The full journey as an array of node rows
 */
function buildFullJourney($startId, $finishId, $byId, $rootIds) {
    $startPath = buildPathToRoot($startId, $byId, $rootIds);
    $finishPath = buildPathToRoot($finishId, $byId, $rootIds);
    $startRoot = end($startPath);
    $finishRoot = end($finishPath);
    $commonRoot = findCommonRoot($startRoot, $finishRoot, $rootIds, $byId);
    $journey = [];
    foreach ($startPath as $node) {
        $journey[] = $node;
    }
    if ($commonRoot && $commonRoot['acores_id'] !== $startRoot['acores_id']) {
        $journey[] = $commonRoot;
    }
    if ($finishRoot && $finishRoot['acores_id'] !== $commonRoot['acores_id']) {
        $journey[] = $finishRoot;
    }
    $finishPathReversed = array_reverse($finishPath);
    foreach ($finishPathReversed as $node) {
        if ($node['acores_id'] !== $finishRoot['acores_id'] && $node['acores_id'] !== $commonRoot['acores_id']) {
            $journey[] = $node;
        }
    }
    return $journey;
}

// Build the journey from the provided start and finish ACORES IDs.
$journey = buildFullJourney($startAcoresId, $finishAcoresId, $byId, $rootIds);
if (!is_array($journey)) {
    $journey = [];
}
// Filter out nodes with invalid or missing latitude/longitude before output and re-index
$journey = array_values(array_filter($journey, function($node) {
    if (!isset($node['latitude'], $node['longitude'])) return false;
    $lat = $node['latitude'];
    $lon = $node['longitude'];
    return is_numeric($lat) && is_numeric($lon)
        && $lat !== '' && $lon !== ''
        && $lat >= -90 && $lat <= 90
        && $lon >= -180 && $lon <= 180;
}));
echo json_encode([
    'success' => true,
    'error' => null,
    'results' => ['journey' => $journey]
]);