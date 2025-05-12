<?php
require_once 'config/connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'] ?? '';
    $usuario = $_POST['usuario'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $direccion = $_POST['direccion'] ?? '';
    $telefono = $_POST['telefono'] ?? '';
    $codigo_postal = $_POST['codigo_postal'] ?? '';

    if (empty($nombre) || empty($usuario) || empty($email) || empty($password) || empty($direccion) || empty($telefono) || empty($codigo_postal)) {
        echo json_encode(['success' => false, 'message' => 'Todos los campos son obligatorios.']);
        exit;
    }

    $password_hashed = password_hash($password, PASSWORD_BCRYPT);
    $saldo_inicial = 200;

    $sql = "INSERT INTO usuarios (nombre, usuario, email, password, direccion, telefono, codigo_postal, saldo) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param('sssssssi', $nombre, $usuario, $email, $password_hashed, $direccion, $telefono, $codigo_postal, $saldo_inicial);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Usuario registrado exitosamente con $200 de saldo inicial.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al registrar el usuario. Verifica los datos ingresados.']);
        }

        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Error en la preparación de la consulta.']);
    }

    $conn->close();
}
?>
