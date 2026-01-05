<?php
session_start();
if (!isset($_GET['orderid'])) {
    header("Location: index.php");
    exit();
}
$order_id = $_GET['orderid'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmed - STYLISTA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body { background-color: #f8f9fa; font-family: 'Helvetica Neue', sans-serif; }
        .success-card {
            background: #fff;
            border-radius: 15px;
            padding: 40px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            text-align: center;
            margin-top: 80px;
        }
        .check-icon {
            font-size: 5rem;
            color: #198754; /* Bootstrap Success Green */
            margin-bottom: 20px;
        }
        .order-id {
            background-color: #f0f0f0;
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: bold;
            color: #555;
            margin: 15px 0;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="success-card">
                <i class="bi bi-check-circle-fill check-icon"></i>
                
                <h2 class="mb-3">Order Confirmed!</h2>
                <p class="text-muted">Thank you for your purchase. Your order has been placed successfully.</p>
                
                <div class="order-id">
                    Order ID: #<?php echo $order_id; ?>
                </div>

                <p class="mb-4">We will send you a confirmation email shortly.</p>

                <div class="d-grid gap-2">
                    <a href="index.php" class="btn btn-dark btn-lg">Continue Shopping</a>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>