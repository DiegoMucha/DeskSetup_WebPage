<?php
$host = "172.171.240.171"; // Por ejemplo: "192.168.1.10"
$dbname = "desksetup";
$user = "onworldadmin";
$password = "admin123";

$conn = pg_connect("host=$host dbname=$dbname user=$user password=$password");

// Obtener las tablas de la base de datos
$query = "SELECT table_name FROM information_schema.tables WHERE table_schema = 'public'";
$result = pg_query($conn, $query);

if (!$result) {
    die("❌ Error al obtener las tablas.");
}

echo "<h2>Tablas de la base de datos '$dbname':</h2>";
echo "<ul>";

// Iterar sobre las tablas
while ($row = pg_fetch_assoc($result)) {
    $table_name = $row['table_name'];
    echo "<li><strong>$table_name</strong>";

    // Obtener las filas (valores) de cada tabla
    $data_query = "SELECT * FROM $table_name";
    $data_result = pg_query($conn, $data_query);

    if ($data_result) {
        // Mostrar los datos en una tabla HTML
        echo "<table border='1'><tr>";

        // Obtener los nombres de las columnas para mostrarlos como encabezados
        $columns = pg_num_fields($data_result);
        for ($i = 0; $i < $columns; $i++) {
            $field_name = pg_field_name($data_result, $i);
            echo "<th>$field_name</th>";
        }

        echo "</tr>";

        // Mostrar las filas de datos
        while ($row_data = pg_fetch_assoc($data_result)) {
            echo "<tr>";
            foreach ($row_data as $value) {
                echo "<td>$value</td>";
            }            
            echo "</tr>";
        }

        echo "</table>";
    } else {
        echo "<p>❌ No se pudieron obtener los datos de la tabla $table_name.</p>";
    }

    echo "</li>";
    echo "<br>";
}

echo "</ul>";

// Cerrar la conexión
pg_close($conn);
?>