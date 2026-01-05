<?php

session_start();
include 'connection.php';
include 'auth_session.php';

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Fetch orders
$sql = "SELECT id, order_id, product_id, quantity, price FROM order_items ORDER BY id ASC";
$result = mysqli_query($conn, $sql);

if (!$result) {
    die("Query failed: " . mysqli_error($conn));
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Orders - STYLISTA</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-pink: #ff6b9d;
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
            padding-bottom: 60px;
        }

        /* --- Sidebar --- */
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

        .sidebar h3 {
            font-size: 0.75rem;
            font-weight: 700;
            color: #999;
            margin-bottom: 1rem;
            text-transform: uppercase;
            letter-spacing: 1px;
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

        /* --- Main Content --- */
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
            /* Pull up to cover padding */
            margin-bottom: 1rem;
        }

        /* --- Table/Card Styles --- */
        .table-card {
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 5px 25px rgba(0, 0, 0, 0.04);
            padding: 1.5rem;
            border: 1px solid rgba(0, 0, 0, 0.02);
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

            /* Hide Table Header */
            .table thead {
                display: none;
            }

            /* Convert Rows to Cards */
            .table,
            .table tbody,
            .table tr,
            .table td {
                display: block;
                width: 100%;
            }

            .table tr {
                background: #fff;
                margin-bottom: 1rem;
                border-radius: 16px;
                box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
                border: 1px solid #f0f0f0;
                padding: 1.25rem;
                position: relative;
            }

            .table td {
                text-align: right;
                padding: 0.6rem 0;
                border-bottom: 1px solid #f8f9fa;
                display: flex;
                justify-content: space-between;
                align-items: center;
                font-size: 0.95rem;
            }

            .table td:last-child {
                border-bottom: none;
            }

            /* Data Labels via CSS */
            .table td::before {
                content: attr(data-label);
                float: left;
                font-weight: 600;
                color: #888;
                font-size: 0.85rem;
                text-transform: uppercase;
            }

            /* Highlight Price in Mobile */
            .price-cell {
                color: var(--primary-pink);
                font-size: 1.1rem;
                font-weight: bold;
            }
        }
    </style>
</head>

<body>

    <?php include 'sidebar.php'; ?>

    <div class="main-content">

        <div class="top-bar d-flex align-items-center">
            <button class="hamburger me-3" onclick="toggleSidebar()">
                <i class="fas fa-bars"></i>
            </button>
            <h1 class="h4 mb-0 fw-bold">Customer Orders</h1>
        </div>

        <div class="table-card">
            <div class="table-responsive-none">
                <table class="table align-middle border-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="text-muted ps-3">#</th>
                            <th class="text-muted">Order ID</th>
                            <th class="text-muted">Product ID</th>
                            <th class="text-muted">Quantity</th>
                            <th class="text-muted pe-3 text-end">Price</th>
                        </tr>
                    </thead>
                    <tbody class="border-top-0">
                        <?php
                        if ($result && mysqli_num_rows($result) > 0) {
                            while ($row = mysqli_fetch_assoc($result)) {
                        ?>
                                <tr>
                                    <td class="fw-bold text-muted ps-3" data-label="#">
                                        <?php echo $row['id']; ?>
                                    </td>

                                    <td data-label="Order ID">
                                        <span class="badge bg-light text-dark border">
                                            #<?php echo $row['order_id']; ?>
                                        </span>
                                    </td>

                                    <td data-label="Product ID">
                                        <span class="text-secondary">PID: <?php echo $row['product_id']; ?></span>
                                    </td>

                                    <td data-label="Quantity">
                                        x <?php echo $row['quantity']; ?>
                                    </td>

                                    <td class="fw-bold pe-3 price-cell text-end" data-label="Price">
                                        ₹<?php echo number_format($row['price'], 2); ?>
                                    </td>
                                </tr>
                        <?php
                            }
                        } else {
                            echo "<tr><td colspan='5' class='text-center py-5 text-muted'>No orders found yet.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('active');
            document.getElementById('overlay').classList.toggle('active');
        }
    </script>
</body>

</html>