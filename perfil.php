<?php
include 'db_connection.php';

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

try {
    // Verificar que se proporcionó el id_cliente
    if (!isset($_GET['id_cliente'])) {
        throw new Exception("ID de cliente no proporcionado");
    }

    $id_cliente = $_GET['id_cliente'];

    // Query para obtener las reseñas del usuario específico
    $query = "
        SELECT 
            r.id_resena,
            r.comentario,
            r.puntuacion,
            r.fecha_resena,
            p.nombre as producto_nombre,
            p.imagen_url,
            p.marca,
            cat.nombre_categoria
        FROM resenas r
        JOIN productos p ON r.id_producto = p.id_producto
        JOIN categorias cat ON p.id_categoria = cat.id_categoria
        WHERE r.id_cliente = $1
        ORDER BY r.fecha_resena DESC
    ";
    
    $result = pg_query_params($conn, $query, array($id_cliente));

    if (!$result) {
        throw new Exception(pg_last_error());
    }

    // Create reviews array
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

    // Return JSON response
    echo json_encode($reviews, JSON_UNESCAPED_UNICODE);

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