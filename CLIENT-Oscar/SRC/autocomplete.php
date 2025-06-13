<?php
// Oscar Collins 2025
// SAE203 groupe A2
// AI usage: Written manually, comments written by AI. GPT4.1-mini

// This script performs a search query on either the 'post_offices' or 'shops' database table,
// returning up to 10 matching rows as a JSON array. The table is determined by the 'type' parameter.
// Matches are prioritized by whether the query appears at the start of any field (priority matches)
// or elsewhere in the field (secondary matches).

// Set the HTTP response header to indicate the content type is JSON.
// This ensures the client interprets the response correctly.
header('Content-Type: application/json');

// Retrieve the 'query' query parameter from the URL, if set.
// Convert it to lowercase and trim whitespace to standardize the search input.
$query = isset($_GET['query']) ? strtolower(trim($_GET['query'])) : '';

// Retrieve the 'type' parameter to determine which table to search ('office' or 'shop')
$type = isset($_GET['type']) ? strtolower(trim($_GET['type'])) : 'office';

// Initialize an empty array to hold the final search results.
$results = [];

// Only proceed with searching if the query string is not empty.
if ($query !== '') {
    // Limit query length for robustness
    if (strlen($query) > 10) {
        http_response_code(400);
        echo json_encode(['error' => 'Query too long.']);
        exit;
    }
      try {
        // Use database connection for autocomplete
        require_once dirname(__DIR__) . '/DATA/DATABASE/FUNCTIONS/db_connections.php';

        $pdo = db_connect();
          // Determine table and columns based on type
        if ($type === 'shop') {
            $table = 'shops';
            $columns = [
                'name', 'address', 'city', 'postal_code'
            ];
        } else {
            // Default to post offices
            $table = 'post_offices';
            $columns = [
                'name', 'street_address', 'address_complement',
                'postal_code', 'city'
            ];
        }
        
        // $fields_concat is unused but kept for reference if needed for display
        $fields_concat = "CONCAT_WS(' ', ".implode(',', $columns).")";

        // Priority: query matches start of any field (case-insensitive)
        $prioritySql = "SELECT * FROM $table WHERE (" .
            implode(' OR ', array_map(fn($col) => "$col ILIKE :priority_query", $columns)) .
            ") LIMIT 10";
        // Secondary: query appears anywhere else in any field (but not at start)
        $secondarySql = "SELECT * FROM $table WHERE (" .
            implode(' OR ', array_map(fn($col) => "$col ILIKE :secondary_query", $columns)) .
            ") AND NOT (" .
            implode(' OR ', array_map(fn($col) => "$col ILIKE :priority_query", $columns)) .
            ") LIMIT 10";

        // Execute priority match query
        $priorityStmt = $pdo->prepare($prioritySql);
        $priorityStmt->execute([':priority_query' => $query . '%']);
        $priorityMatches = $priorityStmt->fetchAll(PDO::FETCH_NUM);

        // If fewer than 10 priority matches, execute secondary match query
        $secondaryMatches = [];
        if (count($priorityMatches) < 10) {
            $secondaryStmt = $pdo->prepare($secondarySql);
            $secondaryStmt->execute([
                ':secondary_query' => '%' . $query . '%',
                ':priority_query' => $query . '%'
            ]);
            $secondaryMatches = $secondaryStmt->fetchAll(PDO::FETCH_NUM);
        }

        // Combine priority matches first, then secondary matches, limit to 10 results
        $results = array_slice(array_merge($priorityMatches, $secondaryMatches), 0, 10);    } catch (Exception $e) {
        error_log("Autocomplete error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => 'Internal server error: ' . $e->getMessage()]);
        exit;
    }
}

// Encode the results array as a JSON string and output it.
// This is the final response sent back to the client.
echo json_encode($results);