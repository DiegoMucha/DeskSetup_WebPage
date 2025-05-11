<?php
// Include database connection
include 'db_connection.php';

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

// Query to fetch products
$query = "SELECT * FROM productos"; // Adjusted table name
$result = pg_query($conn, $query);

if (!$result) {
    die("Error in query: " . pg_last_error());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Product Listing</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-4">
    <h2 class="text-center">Our Products</h2>
    <div class="row">
        <?php while ($row = pg_fetch_assoc($result)): ?>
            <div class="col-md-3 mb-4">
                <div class="card">
                    <img src="<?php echo $row['imagen_url']; ?>" class="card-img-top" alt="Product Image">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo $row['nombre']; ?></h5>
                        <p class="card-text">Brand: <?php echo $row['marca']; ?></p>
                        <p class="card-text">Description: <?php echo $row['description']; ?></p>
                        <p class="card-text">Price: $<?php echo $row['precio']; ?></p>
                        <p class="card-text">Stock: <?php echo $row['stock']; ?></p>
                        <a href="#" class="btn btn-primary">Buy Now</a>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</div>

<?php
if ($conn) {
    echo "Database connection successful.";
} else {
    echo "Database connection failed.";
}
?>

<!-- Close the connection -->
<?php pg_close($conn); ?>
</body>
</html>

