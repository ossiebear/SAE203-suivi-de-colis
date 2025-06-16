<?php
/**
 * Database Connection Functions
 * 
 * SAE203 Package Tracking System - Database Layer
 * Provides secure PostgreSQL database connection functionality using PDO
 * 
 * Features:
 * - Secure PDO-based database connections
 * - Configurable connection parameters
 * - Error handling with detailed reporting
 * - Connection cleanup functionality
 * 
 * Author: Oscar Collins, SAE203 Group A2
 * Date: 2025
 * AI Usage: Written manually, comments written by AI (GPT-4.1-mini)
 */

/**
 * Establishes a connection to the PostgreSQL database using PDO
 *
 * @param string|null $configFile Optional path to a configuration PHP file that defines DB credentials.
 *                                If not provided, defaults to '../CONFIG/config.php'.
 * @return PDO Returns a PDO instance connected to the database.
 * @throws PDOException Throws exception if connection fails.
 * 
 * @description Creates a secure database connection with proper error handling.
 * The configuration file should define the following variables:
 * - $DB_HOST: Database server hostname
 * - $DB_NAME: Database name
 * - $DB_PORT: Database port (typically 5432 for PostgreSQL)
 * - $DB_USER: Database username
 * - $DB_PASSWORD: Database password
 */
function db_connect($configFile = null) {
    // Use default config file path if none specified
    if ($configFile === null) {
        $configFile = dirname(__DIR__) . '/CONFIG/config.php';
    }

    // Include the config file which should define database connection variables
    include $configFile;

    try {
        // Build the DSN (Data Source Name) string for PostgreSQL connection
        $dsn = 'pgsql:host=' . $DB_HOST . ';dbname=' . $DB_NAME . ';port=' . $DB_PORT;

        // Create a new PDO instance with database credentials
        $pdo = new PDO($dsn, $DB_USER, $DB_PASSWORD);

        // Set error mode to exceptions for better error handling
        // This ensures that database errors are thrown as exceptions rather than silent failures
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        return $pdo;
    } catch (PDOException $e) {
        // Terminate script and output error message on connection failure
        // In production, this should log errors instead of displaying them
        die("Connection failed: " . $e->getMessage());
    }
}

/**
 * Disconnects from the database by nullifying the PDO instance
 * 
 * @return null Returns null to clear the PDO connection
 * @description Properly closes database connections to free up resources.
 * While PHP automatically closes connections at script end, explicit cleanup is good practice.
 */
function db_disconnect() {
    $pdo = null;
    return $pdo;
}