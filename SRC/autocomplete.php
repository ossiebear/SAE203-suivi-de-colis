<?php
// Oscar Collins 2025
// SAE203 groupe A2
// AI usage: Written manually, comments written by AI. GPT4.1-mini



// This script performs a search query on a CSV file containing postal data,
// returning up to 10 matching rows as a JSON array. Matches are prioritized
// by whether the query appears at the start of any field (priority matches)
// or elsewhere in the field (secondary matches).

// Set the HTTP response header to indicate the content type is JSON.
// This ensures the client interprets the response correctly.
header('Content-Type: application/json');

// Retrieve the 'q' query parameter from the URL, if set.
// Convert it to lowercase and trim whitespace to standardize the search input.
$query = isset($_GET['q']) ? strtolower(trim($_GET['q'])) : '';

// Initialize an empty array to hold the final search results.
$results = [];

// Only proceed with searching if the query string is not empty.
if ($query !== '') {
    // Open the CSV file for reading.
    // The file path is relative to the parent directory of the current script,
    // pointing to '/DATA/LOCAL/postaldata.csv'.
    $csv = fopen(dirname(__DIR__) . '/DATA/LOCAL/postaldata.csv', 'r');

    // Read and discard the first row of the CSV, assuming it contains headers.
    fgetcsv($csv);

    // Initialize two arrays to store matches:
    // - $priorityMatches: rows where the query matches the start of any field.
    // - $secondaryMatches: rows where the query appears elsewhere in any field.
    $priorityMatches = [];
    $secondaryMatches = [];

    // Loop through each row of the CSV until EOF or enough results are found.
    while (($row = fgetcsv($csv)) !== false) {
        // Flags to track if the current row matches the query with priority or secondary.
        $priorityMatch = false;
        $secondaryMatch = false;

        // Check each field in the current row for a match with the query.
        foreach ($row as $field) {
            // Convert the field to lowercase for case-insensitive comparison.
            $fieldLower = strtolower($field);

            // Check if the query matches the start of the field (priority match).
            if (strpos($fieldLower, $query) === 0) {
                $priorityMatch = true;
                // No need to check other fields if a priority match is found.
                break;
            }
            // Otherwise, check if the query appears anywhere else in the field (secondary match).
            elseif (stripos($field, $query) !== false) {
                $secondaryMatch = true;
            }
        }

        // Add the row to the appropriate matches array if limits are not exceeded.
        if ($priorityMatch && count($priorityMatches) < 10) {
            $priorityMatches[] = $row;
        } elseif ($secondaryMatch && count($secondaryMatches) < 10) {
            $secondaryMatches[] = $row;
        }

        // Stop reading further if we have reached 10 priority matches,
        // as priority matches take precedence.
        if (count($priorityMatches) >= 10) {
            break;
        }
    }

    // Close the CSV file to free system resources.
    fclose($csv);

    // Combine priority matches first, then secondary matches,
    // and limit the total number of results to 10.
    $results = array_slice(array_merge($priorityMatches, $secondaryMatches), 0, 10);
}

// Encode the results array as a JSON string and output it.
// This is the final response sent back to the client.
echo json_encode($results);