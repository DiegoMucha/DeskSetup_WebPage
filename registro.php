<?php
include 'db_connection.php';

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
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
    
    if (!isset($data->nombre) || !isset($data->apellido) || 
        !isset($data->email) || !isset($data->password) || 
        !isset($data->telefono) || !isset($data->direccion)) {
        throw new Exception("Datos incompletos");
    }

    // Verificar si el correo ya existe
    $checkEmail = pg_query_params($conn, 
        "SELECT correo_electronico FROM clientes WHERE correo_electronico = $1", 
        array($data->email)
    );
    
    if (pg_num_rows($checkEmail) > 0) {
        throw new Exception("El correo electrónico ya está registrado");
    }

    // Obtener el último ID de cliente
    $lastIdQuery = "SELECT id_cliente FROM clientes ORDER BY id_cliente DESC LIMIT 1";
    $lastIdResult = pg_query($conn, $lastIdQuery);
    
    if (!$lastIdResult) {
        throw new Exception(pg_last_error());
    }
    
    $row = pg_fetch_assoc($lastIdResult);
    $lastId = $row ? $row['id_cliente'] : 'CLI0000000';
    
    // Generar nuevo ID secuencial
    $numericPart = intval(substr($lastId, 3)) + 1;
    $newId = 'CLI' . str_pad($numericPart, 7, '0', STR_PAD_LEFT);

    // Insertar nuevo cliente
    $query = "INSERT INTO clientes (id_cliente, nombre, apellido, correo_electronico, contraseña, telefono, direccion, fecha_registro) 
              VALUES ($1, $2, $3, $4, $5, $6, $7, CURRENT_TIMESTAMP)";
    
    $result = pg_query_params($conn, $query, array(
        $newId,             // $1 para id_cliente
        $data->nombre,      // $2 para nombre
        $data->apellido,    // $3 para apellido
        $data->email,       // $4 para correo_electronico
        $data->password,    // $5 para contraseña
        $data->telefono,    // $6 para telefono
        $data->direccion    // $7 para direccion
    ));

    if (!$result) {
        throw new Exception(pg_last_error());
    }

    echo json_encode([
        "status" => "success",
        "message" => "Cliente registrado exitosamente",
        "id_cliente" => $newId
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