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
    if ($method === 'POST' && $action === 'register') {
        // CREATE cliente (de registro.php)
        $data = json_decode(file_get_contents("php://input"));
        if (!isset($data->nombre) || !isset($data->apellido) || 
            !isset($data->email) || !isset($data->password) || 
            !isset($data->telefono) || !isset($data->direccion)) {
            throw new Exception("Datos incompletos");
        }
        $checkEmail = pg_query_params($conn, 
            "SELECT correo_electronico FROM clientes WHERE correo_electronico = $1", 
            array($data->email)
        );
        if (pg_num_rows($checkEmail) > 0) {
            throw new Exception("El correo electrónico ya está registrado");
        }
        $lastIdQuery = "SELECT id_cliente FROM clientes ORDER BY id_cliente DESC LIMIT 1";
        $lastIdResult = pg_query($conn, $lastIdQuery);
        if (!$lastIdResult) throw new Exception(pg_last_error());
        $row = pg_fetch_assoc($lastIdResult);
        $lastId = $row ? $row['id_cliente'] : 'CLI0000000';
        $numericPart = intval(substr($lastId, 3)) + 1;
        $newId = 'CLI' . str_pad($numericPart, 7, '0', STR_PAD_LEFT);
        $query = "INSERT INTO clientes (id_cliente, nombre, apellido, correo_electronico, contraseña, telefono, direccion, fecha_registro) 
                  VALUES ($1, $2, $3, $4, $5, $6, $7, CURRENT_TIMESTAMP)";
        $result = pg_query_params($conn, $query, array(
            $newId, $data->nombre, $data->apellido, $data->email, $data->password, $data->telefono, $data->direccion
        ));
        if (!$result) throw new Exception(pg_last_error());
        echo json_encode([
            "status" => "success",
            "message" => "Cliente registrado exitosamente",
            "id_cliente" => $newId
        ]);
    } else if ($method === 'POST' && $action === 'login') {
        // LOGIN cliente (de login.php)
        $data = json_decode(file_get_contents("php://input"));
        if (!isset($data->usuario) || !isset($data->password)) {
            throw new Exception("Datos incompletos");
        }
        $query = "SELECT id_cliente, nombre, apellido, correo_electronico FROM clientes WHERE correo_electronico = $1 AND contraseña = $2";
        $result = pg_query_params($conn, $query, array($data->usuario, $data->password));
        if (!$result) throw new Exception(pg_last_error());
        if (pg_num_rows($result) > 0) {
            $user = pg_fetch_assoc($result);
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
    } else if ($method === 'POST' && $action === 'edit') {
        // UPDATE cliente (de edit_user.php)
        $data = json_decode(file_get_contents("php://input"));
        if (!isset($data->id) || !isset($data->nombre) || !isset($data->usuario) || !isset($data->email) || !isset($data->direccion) || !isset($data->telefono) || !isset($data->codigo_postal)) {
            throw new Exception("Datos incompletos");
        }
        $query = "UPDATE usuarios SET nombre = $1, usuario = $2, email = $3, direccion = $4, telefono = $5, codigo_postal = $6 WHERE id = $7";
        $result = pg_query_params($conn, $query, array($data->nombre, $data->usuario, $data->email, $data->direccion, $data->telefono, $data->codigo_postal, $data->id));
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Usuario actualizado exitosamente.']);
        } else {
            throw new Exception('Error al actualizar el usuario.');
        }
    } else {
        throw new Exception("Acción o método no soportado para clientes");
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
