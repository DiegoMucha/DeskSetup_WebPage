<?php
include 'db_connection.php';

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Access-Control-Allow-Credentials: true");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$method = $_SERVER['REQUEST_METHOD'];
$action = isset($_GET['action']) ? $_GET['action'] : '';

try {
    if ($method === 'GET' && $action === 'all') {
        // READ productos (de productos.php)
        $query = "SELECT * FROM productos";
        $result = pg_query($conn, $query);
        if (!$result) throw new Exception(pg_last_error());
        $productos = [];
        while ($row = pg_fetch_assoc($result)) {
            $productos[] = $row;
        }
        echo json_encode($productos, JSON_UNESCAPED_UNICODE);
    } else {
        throw new Exception("Acción o método no soportado para productos");
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
} finally {
    if (isset($conn)) { pg_close($conn); }
}
?>
