<?php
include 'db_connection.php';

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

try {
    // Query para obtener las reseñas con información relacionada
    $query = "
        SELECT 
            r.id_resena,
            r.comentario,
            r.puntuacion,
            r.fecha_resena,
            c.nombre as cliente_nombre,
            p.nombre as producto_nombre,
            cat.nombre_categoria as categoria_nombre
        FROM resenas r
        JOIN clientes c ON r.id_cliente = c.id_cliente
        JOIN productos p ON r.id_producto = p.id_producto
        JOIN categorias cat ON p.id_categoria = cat.id_categoria
        ORDER BY r.fecha_resena DESC
    ";
    
    $result = pg_query($conn, $query);

    if (!$result) {
        throw new Exception(pg_last_error());
    }

    // Create reseñas array
    $resenas = [];
    while ($row = pg_fetch_assoc($result)) {
        $resenas[] = $row;
    }

    // Return JSON response
    echo json_encode($resenas, JSON_UNESCAPED_UNICODE);

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