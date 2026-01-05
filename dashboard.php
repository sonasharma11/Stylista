<?php
session_start();
include 'connection.php';
include 'auth_session.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Admin Dashboard - STYLISTA</title>

    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">

    <style>
        :root {
            --primary-pink: #ff6b9d;
            --hover-pink: #ff4785;
            --bg-light: #f5f7fa;
            --sidebar-width: 260px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--bg-light);
            padding-bottom: 80px;
        }

        /* --- Sidebar & Nav (Essential for Layout) --- */
        .sidebar {
            width: var(--sidebar-width);
            background: #fff;
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            padding: 2rem 1.5rem;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.02);
            z-index: 1000;
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .sidebar-brand {
            font-size: 1.5rem;
            font-weight: bold;
            color: #333;
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .nav-link {
            padding: 0.9rem 1rem;
            border-radius: 12px;
            color: #666;
            display: flex;
            align-items: center;
            gap: 1rem;
            font-weight: 500;
            margin-bottom: 8px;
            text-decoration: none;
            transition: all 0.2s;
        }

        .nav-link:hover,
        .nav-link.active {
            background: #fff0f5;
            color: var(--primary-pink);
        }

        .nav-link.active {
            background: var(--primary-pink);
            color: #fff;
            box-shadow: 0 4px 15px rgba(255, 107, 157, 0.4);
        }

        /* --- Main Content Layout --- */
        .main-content {
            margin-left: var(--sidebar-width);
            padding: 2rem;
            min-height: 100vh;
            transition: margin-left 0.3s ease;
        }

        .top-bar {
            position: sticky;
            top: 0;
            background: var(--bg-light);
            z-index: 90;
            padding: 1rem 0;
            margin-top: -2rem;
            margin-bottom: 1rem;
        }

        /* --- Dashboard Cards (Active) --- */
        .stat-card {
            background: #fff;
            border-radius: 20px;
            border: none;
            box-shadow: 0 5px 25px rgba(0, 0, 0, 0.04);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.08);
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            flex-shrink: 0;
        }

        .icon-pink {
            background: rgba(255, 107, 157, 0.1);
            color: var(--primary-pink);
        }

        .icon-purple {
            background: rgba(111, 66, 193, 0.1);
            color: #6f42c1;
        }

        .icon-blue {
            background: rgba(13, 110, 253, 0.1);
            color: #0d6efd;
        }

        .icon-orange {
            background: rgba(253, 126, 20, 0.1);
            color: #fd7e14;
        }

        /* --- Mobile Responsive Logic --- */
        .hamburger {
            display: none;
            font-size: 1.6rem;
            cursor: pointer;
            border: none;
            background: none;
            color: #333;
        }

        .overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
            opacity: 0;
            transition: opacity 0.3s;
        }

        .overlay.active {
            display: block;
            opacity: 1;
        }

        @media (max-width: 992px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.active {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
                padding: 1rem;
            }

            .hamburger {
                display: block;
            }
        }
    </style>
</head>

<body>

    <?php include 'sidebar.php'; ?>

    <?php
    // 1. Total Orders
    $orderQuery = "SELECT COUNT(*) as total_count FROM orders";
    $orderResult = $conn->query($orderQuery);
    $orderRow = $orderResult->fetch_assoc();
    $total_orders = $orderRow['total_count'] ?? 0;

    // 2. Total Revenue
    $revenueQuery = "SELECT SUM(total_amount) as revenue FROM orders";
    $revenueResult = $conn->query($revenueQuery);
    $revenueRow = $revenueResult->fetch_assoc();
    $total_sales = number_format($revenueRow['revenue'] ?? 0);

    // 3. Total Products
    $productsQuery = "SELECT COUNT(*) as total_count FROM products";
    $productResult = $conn->query($productsQuery);
    $productRow = $productResult->fetch_assoc();
    $total_products = $productRow['total_count'] ?? 0;

    // 4. Total Customers
    $customersQuery = "SELECT COUNT(*) as total_count FROM users";
    $customerResult = $conn->query($customersQuery);
    $customerRow = $customerResult->fetch_assoc();
    $total_customers = $customerRow['total_count'] ?? 0;

    // 5. Total Stocks
    $stockQuery = "SELECT 
                    (COUNT(NULLIF(image, '')) + 
                     COUNT(NULLIF(image1, '')) + 
                     COUNT(NULLIF(image2, '')) + 
                     COUNT(NULLIF(image3, ''))) as total_stock_count 
                   FROM products";

    $stockResult = $conn->query($stockQuery);
    if ($stockResult) {
        $stockRow = $stockResult->fetch_assoc();
        $total_stock = $stockRow['total_stock_count'] ?? 0;
    } else {
        $total_stock = 0;
    }

    // 6. Calculate Current Stock
    $current_stock = $total_stock - $total_orders;
    if ($current_stock < 0) {
        $current_stock = 0;
    }

    // 7. Total Delivered Orders
    $deliveredQuery = "SELECT COUNT(*) as total_count FROM orders WHERE status = 'Delivered'";
    $deliveredResult = $conn->query($deliveredQuery);
    $deliveredRow = $deliveredResult->fetch_assoc();
    $total_delivered = $deliveredRow['total_count'] ?? 0;

    // 8. Women Products Count (Assuming column is 'category')
    $womenQuery = "SELECT COUNT(*) as total_count FROM products WHERE category = 'Women'"; 
    $womenResult = $conn->query($womenQuery);
    $womenRow = $womenResult->fetch_assoc();
    $total_women = $womenRow['total_count'] ?? 0;

    // 9. Men Products Count (Assuming column is 'category')
    $menQuery = "SELECT COUNT(*) as total_count FROM products WHERE category = 'Men'"; 
    $menResult = $conn->query($menQuery);
    $menRow = $menResult->fetch_assoc();
    $total_men = $menRow['total_count'] ?? 0;
    ?>

    <div class="main-content">
        <div class="top-bar d-flex align-items-center justify-content-between mb-4">
            <div class="d-flex align-items-center gap-3">
                <button class="hamburger" onclick="toggleSidebar()">
                    <i class="fas fa-bars"></i>
                </button>
                <h1 class="h4 mb-0 fw-bold">Overview</h1>
            </div>
        </div>

        <div class="row g-4 mb-5">
            <div class="col-12 col-sm-6 col-xl-4">
                <div class="stat-card p-4 d-flex align-items-center justify-content-between">
                    <div>
                        <p class="text-muted mb-1 text-uppercase fw-bold small">Total Revenue</p>
                        <h3 class="fw-bold mb-0">₹<?= $total_sales ?></h3>
                    </div>
                    <div class="stat-icon icon-pink">
                        <i class="fas fa-wallet"></i>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-4">
                <div class="stat-card p-4 d-flex align-items-center justify-content-between">
                    <div>
                        <p class="text-muted mb-1 text-uppercase fw-bold small">Total Stocks</p>
                        <h3 class="fw-bold mb-0"><?= $total_stock ?></h3>
                    </div>
                    <div class="stat-icon icon-orange"> <i class="fas fa-boxes-stacked"></i>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-4">
                <div class="stat-card p-4 d-flex align-items-center justify-content-between">
                    <div>
                        <p class="text-muted mb-1 text-uppercase fw-bold small">Current Stocks</p>
                        <h3 class="fw-bold mb-0"><?= $current_stock ?></h3>
                    </div>
                    <div class="stat-icon icon-orange"> 
                        <i class="fas fa-cubes"></i>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-4">
                <div class="stat-card p-4 d-flex align-items-center justify-content-between">
                    <div>
                        <p class="text-muted mb-1 text-uppercase fw-bold small">Products</p>
                        <h3 class="fw-bold mb-0"><?= $total_products ?></h3>
                    </div>
                    <div class="stat-icon icon-orange">
                        <i class="fas fa-tags"></i>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-4">
                <div class="stat-card p-4 d-flex align-items-center justify-content-between">
                    <div>
                        <p class="text-muted mb-1 text-uppercase fw-bold small">Women</p>
                        <h3 class="fw-bold mb-0"><?= $total_women ?></h3>
                    </div>
                    <div class="stat-icon icon-pink">
                        <i class="fas fa-person-dress"></i>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-4">
                <div class="stat-card p-4 d-flex align-items-center justify-content-between">
                    <div>
                        <p class="text-muted mb-1 text-uppercase fw-bold small">Men</p>
                         <h3 class="fw-bold mb-0"><?= $total_men ?></h3>
                    </div>
                    <div class="stat-icon icon-blue">
                        <i class="fas fa-person"></i>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-4">
                <div class="stat-card p-4 d-flex align-items-center justify-content-between">
                    <div>
                        <p class="text-muted mb-1 text-uppercase fw-bold small">Customers</p>
                        <h3 class="fw-bold mb-0"><?= $total_customers ?></h3>
                    </div>
                    <div class="stat-icon icon-blue">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-4">
                <div class="stat-card p-4 d-flex align-items-center justify-content-between">
                    <div>
                        <p class="text-muted mb-1 text-uppercase fw-bold small">Total Orders</p>
                        <h3 class="fw-bold mb-0"><?= $total_orders ?></h3>
                    </div>
                    <div class="stat-icon icon-purple">
                        <i class="fas fa-shopping-bag"></i>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-4">
                <div class="stat-card p-4 d-flex align-items-center justify-content-between">
                    <div>
                        <p class="text-muted mb-1 text-uppercase fw-bold small">Order Delivered</p>
                        <h3 class="fw-bold mb-0"><?= $total_delivered ?></h3>
                    </div>
                    <div class="stat-icon icon-purple">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="overlay" class="overlay" onclick="toggleSidebar()"></div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('active');
            document.getElementById('overlay').classList.toggle('active');
        }
    </script>
</body>

</html>
<?php $conn->close(); ?>