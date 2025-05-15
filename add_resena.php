<?php
include 'db_connection.php';

// Add these CORS headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Access-Control-Allow-Credentials: true");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    $data = json_decode(file_get_contents("php://input"));
    
    if (!isset($data->id_cliente) || !isset($data->id_producto) || 
        !isset($data->puntuacion) || !isset($data->comentario)) {
        throw new Exception("Datos incompletos");
    }

    // Validar que el cliente existe
    $checkCliente = pg_query_params($conn, 
        "SELECT id_cliente FROM clientes WHERE id_cliente = $1", 
        array($data->id_cliente)
    );
    if (pg_num_rows($checkCliente) == 0) {
        throw new Exception("Cliente no encontrado");
    }

    // Validar que el producto existe
    $checkProducto = pg_query_params($conn, 
        "SELECT id_producto FROM productos WHERE id_producto = $1", 
        array($data->id_producto)
    );
    if (pg_num_rows($checkProducto) == 0) {
        throw new Exception("Producto no encontrado");
    }

    // Obtener el último ID de reseña
    $lastIdQuery = "SELECT id_resena FROM resenas ORDER BY id_resena DESC LIMIT 1";
    $lastIdResult = pg_query($conn, $lastIdQuery);
    
    if (!$lastIdResult) {
        throw new Exception(pg_last_error());
    }
    
    $row = pg_fetch_assoc($lastIdResult);
    $lastId = $row ? $row['id_resena'] : 'RES0000000';
    
    // Generar nuevo ID secuencial
    $numericPart = intval(substr($lastId, 3)) + 1;
    $newId = 'RES' . str_pad($numericPart, 7, '0', STR_PAD_LEFT);

    // Insertar la reseña con todos los campos en orden
    $query = "INSERT INTO resenas (id_resena, id_cliente, id_producto, comentario, puntuacion, fecha_resena) 
              VALUES ($1, $2, $3, $4, $5, CURRENT_TIMESTAMP)";
    
    $result = pg_query_params($conn, $query, array(
        $newId,               // $1 para id_resena (RES0000001, RES0000002, etc)
        $data->id_cliente,    // $2 para id_cliente
        $data->id_producto,   // $3 para id_producto
        $data->comentario,    // $4 para comentario
        $data->puntuacion     // $5 para puntuacion
    ));

    if (!$result) {
        throw new Exception(pg_last_error());
    }

    echo json_encode([
        "status" => "success",
        "message" => "Reseña agregada exitosamente",
        "id_resena" => $newId
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
} finally {
    if (isset($conn)) {
        pg_close($conn);
    }
}
?>