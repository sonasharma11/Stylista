<?php
include 'connection.php';
include 'auth_session.php';
$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $title = $_POST['title'];
    $price = $_POST['price'];
    $image = $_POST['image'];
    $category = $_POST['category'];

    $image1 = $_POST['image1'];
    $image2 = $_POST['image2'];
    $image3 = $_POST['image3'];
    $image4 = $_POST['image4'];

    $description = $_POST['description'];

    $stmt = $conn->prepare("INSERT INTO products 
        (title, price, image, image1, image2, image3, image4, description, category) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $stmt->bind_param(
        "sssssssss",
        $title,
        $price,
        $image,
        $image1,
        $image2,
        $image3,
        $image4,
        $description,
        $category
    );

    if ($stmt->execute()) {
        header("Location: dashboard.php");
        exit();
    } else {
        $message = "Error: " . $stmt->error;
    }

    $stmt->close();
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Add Product - STYLISTA</title>
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
            /* Space for scrolling */
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
            margin-bottom: 1rem;
        }

        /* --- Form Styles --- */
        .form-section-title {
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #888;
            margin-bottom: 1rem;
            font-weight: 700;
            border-bottom: 2px solid #eee;
            padding-bottom: 10px;
        }

        .form-card {
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 5px 25px rgba(0, 0, 0, 0.04);
            padding: 2rem;
            border: 1px solid rgba(0, 0, 0, 0.02);
            margin-bottom: 2rem;
        }

        .form-control,
        .form-select {
            padding: 0.75rem 1rem;
            /* Larger touch target */
            border-radius: 10px;
            border: 1px solid #e0e0e0;
            font-size: 1rem;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: var(--primary-pink);
            box-shadow: 0 0 0 0.25rem rgba(255, 107, 157, 0.15);
        }

        .btn-theme {
            background-color: var(--primary-pink);
            border: none;
            color: white;
            padding: 0.8rem 2.5rem;
            font-weight: 600;
            border-radius: 12px;
            transition: all 0.3s;
        }

        .btn-theme:hover {
            background-color: var(--hover-pink);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 71, 133, 0.3);
        }

        .btn-cancel {
            padding: 0.8rem 2rem;
            border-radius: 12px;
            background: #fff;
            border: 1px solid #ddd;
            color: #666;
            text-decoration: none;
            font-weight: 600;
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

            .form-card {
                padding: 1.5rem;
                /* Less padding on mobile */
            }

            /* Full width buttons on mobile */
            .action-buttons {
                flex-direction: column;
                gap: 1rem;
            }

            .btn-theme,
            .btn-cancel {
                width: 100%;
                text-align: center;
                display: block;
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
            <h1 class="h4 mb-0 fw-bold">Create Product</h1>
        </div>

        <?php if ($message) echo "<div class='alert alert-danger shadow-sm rounded-3'>$message</div>"; ?>

        <form method="post" action="">

            <div class="form-card">
                <div class="form-section-title"><i class="fas fa-info-circle me-2"></i>Basic Details</div>

                <div class="mb-3">
                    <label class="form-label fw-bold text-muted small">Product Title</label>
                    <input type="text" name="title" class="form-control" placeholder="e.g. Urban Denim Jacket" required>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold text-muted small">Category</label>
                        <select name="category" class="form-select" required>
                            <option value="" selected disabled>Select Category</option>
                            <option value="men">Men</option>
                            <option value="women">Women</option>
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold text-muted small">Price (₹)</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0">₹</span>
                            <input type="number" name="price" class="form-control border-start-0 ps-0" placeholder="0.00" required>
                        </div>
                    </div>
                </div>

                <div class="mb-0">
                    <label class="form-label fw-bold text-muted small">Description</label>
                    <textarea name="description" class="form-control" rows="3" placeholder="Enter product details..."></textarea>
                </div>
            </div>

            <div class="form-card">
                <div class="form-section-title"><i class="fas fa-images me-2"></i>Product Gallery</div>

                <div class="mb-4">
                    <label class="form-label fw-bold text-muted small">Main Image URL</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="fas fa-star text-warning"></i></span>
                        <input type="text" name="image" class="form-control" placeholder="Main display image link..." required>
                    </div>
                </div>

                <label class="form-label fw-bold text-muted small mb-2">Additional Thumbnails</label>
                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <input type="text" name="image1" class="form-control form-control-sm" placeholder="Image URL 1">
                    </div>
                    <div class="col-12 col-md-6">
                        <input type="text" name="image2" class="form-control form-control-sm" placeholder="Image URL 2">
                    </div>
                    <div class="col-12 col-md-6">
                        <input type="text" name="image3" class="form-control form-control-sm" placeholder="Image URL 3">
                    </div>
                    <div class="col-12 col-md-6">
                        <input type="text" name="image4" class="form-control form-control-sm" placeholder="Image URL 4">
                    </div>
                </div>
            </div>

            <div class="d-flex action-buttons pb-4">
                <button type="submit" class="btn btn-theme shadow-sm">
                    <i class="fas fa-check-circle me-2"></i> Publish Product
                </button>
                <a href="inventory.php" class="btn btn-cancel ms-md-3">Cancel</a>
            </div>

        </form>

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