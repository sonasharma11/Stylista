<?php
session_start();
include 'connection.php';
include 'auth_session.php';

// --- 1. USER AUTHENTICATION & SIDEBAR DATA ---
$user_data = [
    'name' => 'Guest User',
    'profile_image' => ''
];

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($u = $res->fetch_assoc()) {
        $user_data = $u;
    }
    $stmt->close();
}

// Profile Image Logic
$img_src = !empty($user_data['profile_image']) ? $user_data['profile_image'] : "https://ui-avatars.com/api/?name=" . urlencode($user_data['name']) . "&background=212529&color=fff";


// --- 2. WISHLIST LOGIC (Provided by You) ---
$wishlist_products = [];

// REMOVE LOGIC
if (isset($_GET['action']) && $_GET['action'] == 'remove' && isset($_GET['id'])) {
    $remove_id = intval($_GET['id']);

    if (isset($_SESSION['user_id'])) {
        // Logged In: Database se delete karo
        $user_id = $_SESSION['user_id'];
        $stmt = $conn->prepare("DELETE FROM wishlist WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $remove_id, $user_id);
        $stmt->execute();
        $stmt->close();
    } else {
        // Guest: Session se remove karo
        if (isset($_SESSION['wishlist_items'])) {
            $key = array_search($remove_id, $_SESSION['wishlist_items']);
            if ($key !== false) {
                unset($_SESSION['wishlist_items'][$key]);
                $_SESSION['wishlist_items'] = array_values($_SESSION['wishlist_items']);
            }
        }
    }
    header("Location: wishlist.php");
    exit();
}

// FETCH LOGIC
if (isset($_SESSION['user_id'])) {
    // LOGGED IN
    $user_id = $_SESSION['user_id'];
    $sql = "SELECT w.id as remove_id, p.id as pid, p.title, p.price, p.image 
            FROM wishlist w 
            JOIN products p ON w.product_id = p.id 
            WHERE w.user_id = ? ORDER BY w.created_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $wishlist_products[] = $row;
    }
    $stmt->close();
} else {
    // GUEST
    if (isset($_SESSION['wishlist_items']) && count($_SESSION['wishlist_items']) > 0) {
        $ids = implode(',', $_SESSION['wishlist_items']);
        $sql = "SELECT id as pid, title, price, image FROM products WHERE id IN ($ids)";
        $result = $conn->query($sql);

        while ($row = $result->fetch_assoc()) {
            $row['remove_id'] = $row['pid']; // Guest mein PID hi remove ID hai
            $wishlist_products[] = $row;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Wishlist | STYLISTA</title>

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

        /* --- WISHLIST SPECIFIC STYLES --- */
        .wishlist-card {
            background: white;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .table-custom th {
            background-color: #fcfcfc;
            font-weight: 600;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 15px;
            border-bottom: 2px solid var(--border-color);
        }

        .table-custom td {
            vertical-align: middle;
            padding: 13px;
            border-bottom: 1px solid #eee;
        }

        .table-custom tr:last-child td {
            border-bottom: none;
        }

        .product-img {
            width: 70px;
            height: 90px;
            object-fit: cover;
            border-radius: 4px;
            border: 1px solid #eee;
        }

        .btn-action {
            width: 35px;
            height: 35px;
            display: inline-flex;
            justify-content: center;
            align-items: center;
            border-radius: 50%;
            transition: 0.2s;
            border: 1px solid #eee;
            background: white;
            color: #555;
        }

        .btn-action:hover {
            background-color: var(--primary-black);
            color: white;
            border-color: var(--primary-black);
        }

        .btn-action-delete:hover {
            background-color: #dc3545;
            color: white;
            border-color: #dc3545;
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
                        <a href="my_orders.php" class="nav-link-custom"><i class="bi bi-box-seam"></i> Orders</a>
                        <a href="wishlist.php" class="nav-link-custom active"><i class="bi bi-heart"></i> Wishlist</a>
                        <div class="border-top my-2"></div>
                        <a href="logout.php" class="nav-link-custom text-danger"><i class="bi bi-box-arrow-right"></i> Log Out</a>
                    </div>
                </div>
            </div>

            <div class="col-lg-9">
                <h4 class="mb-4 fw-bold" style="font-family: var(--font-heading);">My Wishlist</h4>

                <div class="wishlist-card">
                    <div class="card-body p-0">
                        <?php if (!empty($wishlist_products)): ?>
                            <div class="table-responsive">
                                <table class="table table-custom mb-0">
                                    <thead>
                                        <tr>
                                            <th style="width: 15%;">Image</th>
                                            <th style="width: 44%;">Details</th>
                                            <th style="width: 20%;">Price</th>
                                            <th style="width: 20%;">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($wishlist_products as $item): ?>
                                            <tr>
                                                <td>
                                                    <a href="view_product.php?id=<?php echo $item['pid']; ?>">
                                                        <img src="<?php echo $item['image']; ?>" class="product-img">
                                                    </a>
                                                </td>

                                                <td>
                                                    <h6 class="mb-1 text-dark fw-bold">
                                                        <a href="view_product.php?id=<?php echo $item['pid']; ?>" class="text-decoration-none text-dark">
                                                            <?php echo htmlspecialchars($item['title']); ?>
                                                        </a>
                                                    </h6>
                                                    <small class="text-muted">In Stock</small>
                                                </td>

                                                <td>
                                                    <span class="fw-bold text-dark">₹<?php echo number_format($item['price']); ?></span>
                                                </td>

                                                <td class="text-end">
                                                    <a href="view_product.php?id=<?php echo $item['pid']; ?>" class="btn-action" title="View">
                                                        <i class="bi bi-eye"></i>
                                                    </a>

                                                    <a href="wishlist.php?action=remove&id=<?php echo $item['remove_id']; ?>" class="btn-action btn-action-delete mt-2" title="Remove" onclick="return confirm('Remove this item?');">
                                                        <i class="bi bi-trash"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-5">
                                <div class="mb-3 text-muted" style="font-size: 3rem;">
                                    <i class="bi bi-heart"></i>
                                </div>
                                <h5 class="fw-bold">Your wishlist is empty!</h5>
                                <p class="text-muted mb-4">Start adding items you love to see them here.</p>
                                <a href="index.php" class="btn btn-dark px-4 py-2">Start Shopping</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>