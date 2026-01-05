<?php
session_start();
include 'connection.php';
include 'auth_session.php';

// --- 1. AUTHORIZATION ---
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// --- 2. FETCH USER DATA (For Sidebar Profile) ---
// यह कोड साइडबार में नाम और फोटो दिखाने के लिए है
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_data = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user_data) {
    session_destroy();
    header("Location: index.php");
    exit();
}

// --- 3. FETCH ORDERS (Your Logic) ---
// यहाँ हमने आपका वाला Query लगाया है जो Orders लाएगा
$sql_orders = "SELECT * FROM orders WHERE user_id = '$user_id' ORDER BY order_date DESC";
$result_orders = mysqli_query($conn, $sql_orders);

// Profile Image Helper
$img_src = !empty($user_data['profile_image']) ? $user_data['profile_image'] : "https://ui-avatars.com/api/?name=" . urlencode($user_data['name']) . "&background=212529&color=fff";
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders | STYLISTA</title>

    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Plus+Jakarta+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <style>
        :root {
            --primary-black: #1a1a1a;
            --primary-gold: #d4af37;
            --secondary-gray: #f8f9fa;
            --border-color: #e5e5e5;
            --font-heading: 'Playfair Display', serif;
            --font-body: 'Plus Jakarta Sans', sans-serif;
        }

        body {
            font-family: var(--font-body);
            background-color: var(--secondary-gray);
            color: var(--primary-black);
        }

        /* --- SIDEBAR STYLES --- */
        .sidebar-card {
            background: white;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            overflow: hidden;
            position: sticky;
            top: 90px;
        }

        .user-mini-profile {
            padding: 20px 15px;
            text-align: center;
            border-bottom: 1px solid var(--border-color);
            background: linear-gradient(to bottom, #ffffff 0%, #fcfcfc 100%);
        }

        .user-mini-img {
            width: 70px;
            height: 70px;
            object-fit: cover;
            border-radius: 50%;
            border: 2px solid #fff;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
            margin-bottom: 10px;
        }

        .nav-link-custom {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            color: #555;
            text-decoration: none;
            font-weight: 500;
            font-size: 0.95rem;
            transition: all 0.2s;
            border-left: 3px solid transparent;
        }

        .nav-link-custom:hover {
            background-color: #fcfcfc;
            color: var(--primary-black);
        }

        .nav-link-custom.active {
            background-color: #fff9f9;
            color: var(--primary-black);
            border-left-color: var(--primary-gold);
            font-weight: 600;
        }

        .nav-link-custom i {
            width: 25px;
            font-size: 1rem;
            margin-right: 8px;
        }

        /* --- YOUR ORDER CARD STYLES --- */
        .order-card {
            background: white;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            overflow: hidden;
            margin-bottom: 25px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .order-header {
            background-color: #fff;
            border-bottom: 1px solid #eee;
            padding: 15px 20px;
        }

        .order-body {
            background-color: #fff;
            padding: 20px;
        }

        .product-img {
            width: 80px;
            height: 100px;
            object-fit: cover;
            border-radius: 4px;
            border: 1px solid #eee;
        }

        /* Status Colors */
        .status-badge {
            font-size: 0.75rem;
            padding: 5px 12px;
            border-radius: 20px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .bg-pending {
            background-color: #fff3cd;
            color: #856404;
        }

        .bg-shipped {
            background-color: #cff4fc;
            color: #055160;
        }

        .bg-delivered {
            background-color: #d1e7dd;
            color: #0f5132;
        }

        .bg-cancelled {
            background-color: #f8d7da;
            color: #842029;
        }

        .last-no-border:last-child {
            border-bottom: none !important;
            padding-bottom: 0 !important;
        }
    </style>
</head>

<body>

     <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom sticky-top py-3">
        <div class="container justify-content-center">
            <a class="navbar-brand fw-bold fs-4" href="index.php" style="font-family: var(--font-heading); letter-spacing: 2px;">STYLISTA</a>
        </div>
    </nav>

     <div class="container-fluid px-5 py-3 d-flex mt-3" style="background-color: #f0f0f0ff;">
        <a class="text-muted text-decoration-none" href="index.php">Home /</a>
        <span class="mx-1">Profile</span>
    </div>

    <div class="container pb-5 mt-4 mt-lg-4">
        <div class="row g-4">

            <div class="col-lg-3">
                <div class="sidebar-card">
                    <div class="user-mini-profile">
                        <div class="position-relative d-inline-block">
                            <img src="<?php echo htmlspecialchars($img_src); ?>" class="user-mini-img">
                        </div>
                        <div class="mt-1">
                            <small class="text-muted text-uppercase fw-bold d-block mb-0" style="font-size: 0.65rem;">Hello,</small>
                            <h6 class="mb-1 fw-bold fs-6 text-dark"><?php echo htmlspecialchars($user_data['name']); ?></h6>
                            <span class="badge bg-light text-dark border rounded-pill mt-1" style="font-size: 0.6rem;">Verified Member</span>
                        </div>
                    </div>
                    <div class="nav flex-column py-2">
                        <a href="profile.php" class="nav-link-custom"><i class="bi bi-person"></i> My Profile</a>
                        <a href="cart.php" class="nav-link-custom"><i class="bi bi-bag"></i> Cart</a>
                        <a href="my_orders.php" class="nav-link-custom active"><i class="bi bi-box-seam"></i> Orders</a>
                        <a href="wishlist.php" class="nav-link-custom"><i class="bi bi-heart"></i> Wishlist</a>
                        <div class="border-top my-2"></div>
                        <a href="logout.php" class="nav-link-custom text-danger"><i class="bi bi-box-arrow-right"></i> Log Out</a>
                    </div>
                </div>
            </div>

            <div class="col-lg-9">
                <h4 class="mb-4 fw-bold" style="font-family: var(--font-heading);">My Orders</h4>

                <?php if (mysqli_num_rows($result_orders) > 0): ?>

                    <?php while ($order = mysqli_fetch_assoc($result_orders)):
                        // Determine Status Color
                        $status_class = 'bg-pending';
                        if ($order['status'] == 'Shipped') $status_class = 'bg-shipped';
                        if ($order['status'] == 'Delivered') $status_class = 'bg-delivered';
                        if ($order['status'] == 'Cancelled') $status_class = 'bg-cancelled';
                    ?>

                        <div class="card order-card">
                            <div class="order-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                                <div>
                                    <span class="text-muted small text-uppercase fw-bold d-block">ORDER ID</span>
                                    <strong>#<?php echo $order['id']; ?></strong>
                                </div>
                                <div>
                                    <span class="text-muted small text-uppercase fw-bold d-block">DATE</span>
                                    <span><?php echo date('d M Y', strtotime($order['order_date'])); ?></span>
                                </div>
                                <div>
                                    <span class="text-muted small text-uppercase fw-bold d-block">TOTAL</span>
                                    <span>₹<?php echo number_format($order['total_amount']); ?></span>
                                </div>
                                <div class="d-none d-md-block">
                                    <span class="text-muted small text-uppercase fw-bold d-block">PAYMENT</span>
                                    <span><?php echo $order['payment_method']; ?></span>
                                </div>
                                <div>
                                    <span class="badge status-badge <?php echo $status_class; ?>">
                                        <?php echo $order['status']; ?>
                                    </span>
                                </div>
                            </div>

                            <div class="order-body">
                                <?php
                                $order_id = $order['id'];
                                // Fetch Items for this specific Order (Inner Loop)
                                $sql_items = "SELECT oi.*, p.title, p.image 
                                              FROM order_items oi 
                                              JOIN products p ON oi.product_id = p.id 
                                              WHERE oi.order_id = '$order_id'";
                                $result_items = mysqli_query($conn, $sql_items);

                                while ($item = mysqli_fetch_assoc($result_items)):
                                ?>
                                    <div class="d-flex align-items-center mb-3 border-bottom pb-3 last-no-border">
                                        <img src="<?php echo $item['image']; ?>" alt="Product" class="product-img me-3">
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1 text-dark fw-bold"><?php echo $item['title']; ?></h6>
                                            <span class="text-dark fw-bold">₹ <?php echo number_format($item['price']); ?></span>
                                            <br>
                                            <small class="text-muted">Quantity: <?php echo $item['quantity']; ?></small>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        </div>

                    <?php endwhile; ?>

                <?php else: ?>

                    <div class="text-center py-5 border rounded bg-white">
                        <div class="mb-3 text-muted" style="font-size: 3rem;">
                            <i class="bi bi-bag-x"></i>
                        </div>
                        <h5 class="fw-bold" style="font-family: var(--font-heading);">No orders found</h5>
                        <p class="text-muted mb-4">You haven't placed any orders yet.</p>
                        <a href="index.php" class="btn btn-dark px-4 py-2">Start Shopping</a>
                    </div>

                <?php endif; ?>

            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>