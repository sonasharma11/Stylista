<?php
session_start();
include 'connection.php';
include 'auth_session.php';

// 1. Security Check
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// 2. DELETE FUNCTIONALITY
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $del_sql = "DELETE FROM products WHERE id=$id";
    if ($conn->query($del_sql)) {
        // Redirect to inventory.php
        header("Location: inventory.php?msg=deleted");
    } else {
        echo "Error deleting record: " . $conn->error;
    }
}

// 3. FILTER LOGIC (Updated for Case Sensitivity)
$category_filter = isset($_GET['category']) ? $_GET['category'] : 'all';

if ($category_filter == 'men') {
    // Checks for both 'men' and 'Men'
    $sql = "SELECT * FROM products WHERE category IN ('men', 'Men') ORDER BY id ASC";
} elseif ($category_filter == 'women') {
    // Checks for both 'women' and 'Women'
    $sql = "SELECT * FROM products WHERE category IN ('women', 'Women') ORDER BY id ASC";
} else {
    $sql = "SELECT * FROM products ORDER BY id ASC";
}

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Inventory - STYLISTA</title>

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
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--bg-light);
            padding-bottom: 60px;
        }

        /* --- Sidebar CSS --- */
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

        /* --- Table & Card Styles --- */
        .table-card {
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 5px 25px rgba(0, 0, 0, 0.04);
            padding: 1.5rem;
            border: 1px solid rgba(0, 0, 0, 0.02);
        }

        .product-img {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 10px;
            border: 1px solid #eee;
        }

        .badge-men {
            background-color: #343a40;
            color: #fff;
        }

        .badge-women {
            background-color: var(--primary-pink);
            color: #fff;
        }

        .btn-theme {
            background-color: var(--primary-pink);
            color: white;
            border: none;
            border-radius: 10px;
            padding: 8px 16px;
        }

        .btn-theme:hover {
            background-color: var(--hover-pink);
            color: white;
        }

        /* --- Mobile Responsive --- */
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

            /* Mobile Table Cards */
            .table thead {
                display: none;
            }

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
                padding: 1rem;
                position: relative;
            }

            .table td {
                text-align: right;
                padding: 0.5rem 0;
                border-bottom: 1px solid #f8f9fa;
                display: flex;
                justify-content: space-between;
                align-items: center;
            }

            .table td:last-child {
                border-bottom: none;
                justify-content: flex-end;
                gap: 10px;
            }

            .table td::before {
                content: attr(data-label);
                float: left;
                font-weight: bold;
                color: #888;
                font-size: 0.85rem;
                text-transform: uppercase;
            }

            .td-product-details {
                display: flex !important;
                flex-direction: row;
                justify-content: flex-start !important;
                gap: 15px;
            }

            .td-product-details::before {
                display: none;
            }

            .product-img {
                width: 60px;
                height: 60px;
            }
        }
    </style>
</head>

<body>

    <?php include 'sidebar.php'; ?>

    <div class="main-content">

        <div class="top-bar d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-3">
                <button class="hamburger" onclick="toggleSidebar()">
                    <i class="fas fa-bars"></i>
                </button>
                <h1 class="h4 mb-0 fw-bold">Inventory</h1>
            </div>

            <a href="create.php" class="btn btn-theme shadow-sm btn-sm">
                <i class="fas fa-plus"></i> <span class="d-none d-sm-inline">New Product</span>
            </a>
        </div>

        <?php if (isset($_GET['msg']) && $_GET['msg'] == 'deleted'): ?>
            <div class="alert alert-success alert-dismissible fade show shadow-sm border-0" role="alert">
                <i class="fas fa-check-circle me-2"></i> Product deleted successfully.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="table-card">

            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
                <h5 class="text-muted mb-0 d-none d-md-block">Product List</h5>

                <div class="btn-group w-100 w-md-auto" role="group">
                    <a href="inventory.php?category=all"
                        class="btn btn-sm <?php echo ($category_filter == 'all') ? 'btn-dark' : 'btn-outline-dark'; ?>">All</a>
                    <a href="inventory.php?category=men"
                        class="btn btn-sm <?php echo ($category_filter == 'men') ? 'btn-dark' : 'btn-outline-dark'; ?>">Men</a>
                    <a href="inventory.php?category=women"
                        class="btn btn-sm <?php echo ($category_filter == 'women') ? 'btn-dark' : 'btn-outline-dark'; ?>">Women</a>
                </div>
            </div>

            <div class="table-responsive-none">
                <table class="table align-middle border-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="text-muted ps-3" style="width: 50%;">Product Details</th>
                            <th class="text-muted">Category</th>
                            <th class="text-muted">Price</th>
                            <th class="text-muted text-end pe-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="border-top-0">
                        <?php
                        if ($result && $result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                // FIXED: Case insensitive check for badge color
                                $badgeClass = (strtolower(trim($row['category'])) == 'men') ? 'badge-men' : 'badge-women';
                        ?>
                                <tr>
                                    <td class="td-product-details ps-3" data-label="Product">
                                        <?php if (!empty($row['image'])): ?>
                                            <img src="<?php echo htmlspecialchars($row['image']); ?>" class="product-img shadow-sm" alt="img">
                                        <?php else: ?>
                                            <div class="product-img d-flex align-items-center justify-content-center bg-light text-muted">
                                                <i class="fas fa-image"></i>
                                            </div>
                                        <?php endif; ?>
                                        <div>
                                            <div class="fw-bold text-dark"><?php echo htmlspecialchars($row['title']); ?></div>
                                            <small class="text-muted">ID: #<?php echo $row['id']; ?></small>
                                        </div>
                                    </td>

                                    <td data-label="Category">
                                        <span class="badge <?php echo $badgeClass; ?> rounded-pill px-3 py-2">
                                            <?php echo ucfirst(htmlspecialchars($row['category'])); ?>
                                        </span>
                                    </td>

                                    <td data-label="Price" class="fw-bold text-dark">
                                        ₹<?php echo number_format($row['price']); ?>
                                    </td>

                                    <td class="text-end pe-3">
                                        <a href="edit.php?id=<?php echo $row['id']; ?>" class="btn btn-light text-primary border me-2 shadow-sm"
                                            style="width:38px; height:38px; border-radius:50%; display:inline-flex; align-items:center; justify-content:center;">
                                            <i class="fas fa-pen"></i>
                                        </a>
                                        <a href="inventory.php?delete=<?php echo $row['id']; ?>"
                                            class="btn btn-light text-danger border shadow-sm"
                                            style="width:38px; height:38px; border-radius:50%; display:inline-flex; align-items:center; justify-content:center;"
                                            onclick="return confirm('Are you sure you want to delete this product?');">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                        <?php
                            }
                        } else {
                            echo "<tr><td colspan='4' class='text-center py-5 text-muted'>No products found. Click 'New Product' to add one.</td></tr>";
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
<?php $conn->close(); ?>