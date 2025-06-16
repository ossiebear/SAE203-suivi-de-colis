<?php
// debug_package.php - Debug a specific package
header('Content-Type: text/html; charset=utf-8');
require_once '../DATA/DATABASE/FUNCTIONS/db_connections.php';

$trackingNumber = $_GET['tracking'] ?? '70B8A5B95105';

echo "<h2>Debug Package: $trackingNumber</h2>";

try {
    $pdo = db_connect();
    
    // Get raw package data
    $sql = "SELECT * FROM packages WHERE tracking_number = :tracking_number";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':tracking_number', $trackingNumber);
    $stmt->execute();
    $package = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($package) {
        echo "<h3>Raw Package Data:</h3>";
        echo "<table border='1' style='border-collapse: collapse; font-family: monospace;'>";
        foreach ($package as $key => $value) {
            echo "<tr>";
            echo "<td style='padding: 5px; background: #f0f0f0;'><strong>$key</strong></td>";
            echo "<td style='padding: 5px;'>" . htmlspecialchars($value ?? 'NULL') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Test the join query
        echo "<h3>Join Query Test:</h3>";
        $joinSQL = "
            SELECT 
                p.*,
                s.status_name,
                s.description as status_description,
                o.name as office_name,
                o.street_address as office_address,
                o.city as office_city,
                sh.name as shop_name,
                sh.address as shop_address
            FROM packages p
            LEFT JOIN package_statuses s ON p.current_status_id = s.id
            LEFT JOIN post_offices o ON p.current_office_id = o.id
            LEFT JOIN shops sh ON p.sender_shop_id = sh.id
            WHERE p.tracking_number = :tracking_number
        ";
        
        $joinStmt = $pdo->prepare($joinSQL);
        $joinStmt->bindParam(':tracking_number', $trackingNumber);
        $joinStmt->execute();
        $joinResult = $joinStmt->fetch(PDO::FETCH_ASSOC);
        
        if ($joinResult) {
            echo "<table border='1' style='border-collapse: collapse; font-family: monospace;'>";
            foreach ($joinResult as $key => $value) {
                echo "<tr>";
                echo "<td style='padding: 5px; background: #f0f0f0;'><strong>$key</strong></td>";
                echo "<td style='padding: 5px;'>" . htmlspecialchars($value ?? 'NULL') . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p style='color: red;'>Join query returned no results!</p>";
        }
        
    } else {
        echo "<p style='color: red;'>Package not found!</p>";
        
        // Show all tracking numbers in database
        echo "<h3>Available tracking numbers:</h3>";
        $allSQL = "SELECT tracking_number FROM packages LIMIT 10";
        $allStmt = $pdo->query($allSQL);
        $allPackages = $allStmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<ul>";
        foreach ($allPackages as $pkg) {
            $num = $pkg['tracking_number'];
            echo "<li><a href='?tracking=$num'>$num</a></li>";
        }
        echo "</ul>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
