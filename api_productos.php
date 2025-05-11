<?php
// api_productos.php
include 'db_connection.php';

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

$query = "SELECT * FROM productos";
$result = pg_query($conn, $query);

$productos = [];

while ($row = pg_fetch_assoc($result)) {
    $productos[] = $row;
}

echo json_encode($productos);

pg_close($conn);
?>
