<?php
include 'db_connection.php';

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json; charset=UTF-8");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$method = $_SERVER['REQUEST_METHOD'];
$action = isset($_GET['action']) ? $_GET['action'] : '';

try {
    if ($method === 'POST' && $action === 'add') {
        // CREATE reseña (de add_resena.php)
        $data = json_decode(file_get_contents("php://input"));
        if (!isset($data->id_cliente) || !isset($data->id_producto) || 
            !isset($data->puntuacion) || !isset($data->comentario)) {
            throw new Exception("Datos incompletos");
        }
        // Validar cliente
        $checkCliente = pg_query_params($conn, 
            "SELECT id_cliente FROM clientes WHERE id_cliente = $1", 
            array($data->id_cliente)
        );
        if (pg_num_rows($checkCliente) == 0) {
            throw new Exception("Cliente no encontrado");
        }
        // Validar producto
        $checkProducto = pg_query_params($conn, 
            "SELECT id_producto FROM productos WHERE id_producto = $1", 
            array($data->id_producto)
        );
        if (pg_num_rows($checkProducto) == 0) {
            throw new Exception("Producto no encontrado");
        }
        // Obtener último ID
        $lastIdQuery = "SELECT id_resena FROM resenas ORDER BY id_resena DESC LIMIT 1";
        $lastIdResult = pg_query($conn, $lastIdQuery);
        if (!$lastIdResult) throw new Exception(pg_last_error());
        $row = pg_fetch_assoc($lastIdResult);
        $lastId = $row ? $row['id_resena'] : 'RES0000000';
        $numericPart = intval(substr($lastId, 3)) + 1;
        $newId = 'RES' . str_pad($numericPart, 7, '0', STR_PAD_LEFT);
        $query = "INSERT INTO resenas (id_resena, id_cliente, id_producto, comentario, puntuacion, fecha_resena) 
                  VALUES ($1, $2, $3, $4, $5, CURRENT_TIMESTAMP)";
        $result = pg_query_params($conn, $query, array(
            $newId, $data->id_cliente, $data->id_producto, $data->comentario, $data->puntuacion
        ));
        if (!$result) throw new Exception(pg_last_error());
        echo json_encode(["status" => "success", "id_resena" => $newId]);
    } else if ($method === 'GET' && $action === 'all') {
        // READ todas las reseñas (de get_resenas.php)
        $query = "SELECT r.id_resena, r.comentario, r.puntuacion, r.fecha_resena, c.nombre as cliente_nombre, p.nombre as producto_nombre, cat.nombre_categoria FROM resenas r JOIN clientes c ON r.id_cliente = c.id_cliente JOIN productos p ON r.id_producto = p.id_producto JOIN categorias cat ON p.id_categoria = cat.id_categoria ORDER BY r.fecha_resena DESC";
        $result = pg_query($conn, $query);
        if (!$result) throw new Exception(pg_last_error());
        $resenas = [];
        while ($row = pg_fetch_assoc($result)) {
            $resenas[] = $row;
        }
        echo json_encode($resenas, JSON_UNESCAPED_UNICODE);
    } else if ($method === 'GET' && $action === 'user' && isset($_GET['id_cliente'])) {
        // READ reseñas de un usuario (de perfil.php)
        $id_cliente = $_GET['id_cliente'];
        $query = "SELECT r.id_resena, r.comentario, r.puntuacion, r.fecha_resena, p.nombre as producto_nombre, p.imagen_url, p.marca, cat.nombre_categoria FROM resenas r JOIN productos p ON r.id_producto = p.id_producto JOIN categorias cat ON p.id_categoria = cat.id_categoria WHERE r.id_cliente = $1 ORDER BY r.fecha_resena DESC";
        $result = pg_query_params($conn, $query, array($id_cliente));
        if (!$result) throw new Exception(pg_last_error());
        $reviews = [];
        while ($row = pg_fetch_assoc($result)) {
            $reviews[] = array(
                'id_resena' => $row['id_resena'],
                'producto_nombre' => $row['producto_nombre'],
                'marca' => $row['marca'],
                'categoria' => $row['nombre_categoria'],
                'puntuacion' => intval($row['puntuacion']),
                'comentario' => $row['comentario'],
                'fecha_resena' => $row['fecha_resena'],
                'imagen_url' => $row['imagen_url']
            );
        }
        echo json_encode($reviews, JSON_UNESCAPED_UNICODE);
    } else {
        throw new Exception("Acción o método no soportado para reseñas");
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
