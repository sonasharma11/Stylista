<?php
include 'connection.php';
include 'auth_session.php';
$sql = "SELECT * FROM products";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Our Products</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h2>Products</h2>
    <div class="row">
        <?php while($row = $result->fetch_assoc()): ?>
        <div class="col-md-4 mb-4">
            <div class="card">
                <a href="view_product.php?id=<?php echo $row['id']; ?>">
                    <img src="<?php echo $row['image']; ?>" class="card-img-top" alt="">
                </a>
                <div class="card-body">
                    <h5 class="card-title"><?php echo $row['title']; ?></h5>
                    <p class="card-text">₹ <?php echo number_format($row['price']); ?></p>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
</div>
</body>
</html>
