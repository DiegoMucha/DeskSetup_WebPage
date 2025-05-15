<?php
include 'db_connection.php';

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    $data = json_decode(file_get_contents("php://input"));
    
    if (!isset($data->usuario) || !isset($data->password)) {
        throw new Exception("Datos incompletos");
    }

    // Validate email and password
    $query = "SELECT id_cliente, nombre, apellido, correo_electronico 
              FROM clientes 
              WHERE correo_electronico = $1 
              AND contraseña = $2";
    
    $result = pg_query_params($conn, $query, array(
        $data->usuario,    // email
        $data->password    // password
    ));

    if (!$result) {
        throw new Exception(pg_last_error());
    }

    if (pg_num_rows($result) > 0) {
        $user = pg_fetch_assoc($result);
        
        // Create session data
        $sessionData = [
            'id_cliente' => $user['id_cliente'],
            'nombre' => $user['nombre'],
            'apellido' => $user['apellido'],
            'email' => $user['correo_electronico']
        ];

        echo json_encode([
            "status" => "success",
            "message" => "Login exitoso",
            "user" => $sessionData
        ]);
    } else {
        throw new Exception("Correo o contraseña incorrectos");
    }

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