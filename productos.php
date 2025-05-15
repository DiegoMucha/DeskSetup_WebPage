<?php
include 'db_connection.php';

// Set CORS and JSON headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Access-Control-Allow-Credentials: true");

try {
    // Query productos
    $query = "SELECT * FROM productos";
    $result = pg_query($conn, $query);

    if (!$result) {
        throw new Exception(pg_last_error());
    }

    // Create productos array
    $productos = [];
    while ($row = pg_fetch_assoc($result)) {
        $productos[] = $row;
    }

    // Return JSON response
    echo json_encode($productos, JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
} finally {
    // Close connection
    if (isset($conn)) {
        pg_close($conn);
    }
}