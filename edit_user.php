<?php
require_once 'config/connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $nombre = $_POST['nombre'];
    $usuario = $_POST['usuario'];
    $email = $_POST['email'];
    $direccion = $_POST['direccion'];
    $telefono = $_POST['telefono'];
    $codigo_postal = $_POST['codigo_postal'];

    $sql = "UPDATE usuarios SET nombre = ?, usuario = ?, email = ?, direccion = ?, telefono = ?, codigo_postal = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ssssssi', $nombre, $usuario, $email, $direccion, $telefono, $codigo_postal, $id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Usuario actualizado exitosamente.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al actualizar el usuario.']);
    }

    $stmt->close();
    $conn->close();
}
?>
