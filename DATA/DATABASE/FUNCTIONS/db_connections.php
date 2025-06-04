<?php
// Oscar Collins 2025
// SAE203 groupe A2
// AI usage: Written manually, 
//           comments written by AI. GPT4.1-mini

/**
 * Establishes a connection to the PostgreSQL database using PDO.
 *
 * @param string|null $configFile Optional path to a configuration PHP file that defines DB credentials.
 *                                If not provided, defaults to '../CONFIG/config.php'.
 * @return PDO Returns a PDO instance connected to the database.
 * @throws PDOException Throws exception if connection fails.
 */
function db_connect($configFile = null) {
    // Use default config file path if none specified
    if ($configFile === null) {
        $configFile = dirname(__DIR__) . '/CONFIG/config.php';
    }

    // Include the config file which should define $DB_HOST, $DB_NAME, $DB_PORT, $DB_USER, $DB_PASSWORD
    include $configFile;

    try {
        // Build the DSN string for PostgreSQL connection
        $dsn = 'pgsql:host=' . $DB_HOST . ';dbname=' . $DB_NAME . ';port=' . $DB_PORT;

        // Create a new PDO instance
        $pdo = new PDO($dsn, $DB_USER, $DB_PASSWORD);

        // Set error mode to exceptions for better error handling
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        return $pdo;
    } catch (PDOException $e) {
        // Terminate script and output error message on failure
        die("Connection failed: " . $e->getMessage());
    }
}

/**
 * Disconnects from the database by nullifying the PDO instance.
 */
function db_disconnect() {
    $pdo = null;
    return $pdo;
}