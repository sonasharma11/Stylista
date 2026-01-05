<?php
include 'connection.php';
include 'auth_session.php';
$message = "";

// 1. GET Request: Fetch existing product data
if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $sql = "SELECT * FROM products WHERE id=$id";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $product = $result->fetch_assoc();
    } else {
        die("Product not found.");
    }
} else {
    header("Location: dashboard.php");
    exit();
}

// 2. POST Request: Update product data
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = (int)$_POST['id'];
    $title = $_POST['title'];
    $price = $_POST['price'];
    $description = $_POST['description'];
    $category = $_POST['category'];

    $image = $_POST['image'];
    $image1 = $_POST['image1'];
    $image2 = $_POST['image2'];
    $image3 = $_POST['image3'];

    $stmt = $conn->prepare("UPDATE products SET 
        title=?, price=?, image=?, image1=?, image2=?, image3=?, description=?, category=? 
        WHERE id=?");

    $stmt->bind_param("ssssssssi", $title, $price, $image, $image1, $image2, $image3, $description, $category, $id);

    if ($stmt->execute()) {
        $message = "Product updated successfully!";
        // Refresh data
        $sql = "SELECT * FROM products WHERE id=$id";
        $result = $conn->query($sql);
        $product = $result->fetch_assoc();
    } else {
        $message = "Error: " . $stmt->error;
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Edit Product - STYLISTA</title>

    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">

    <style>
        /* --- COPYING STYLES FROM SLIDERS.PHP --- */
        :root {
            --primary-pink: #ff6b9d;
            --hover-pink: #ff4785;
            --bg-light: #f5f7fa;
            --sidebar-width: 260px;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--bg-light);
            padding-bottom: 80px;
            margin: 0;
        }

        /* Sidebar CSS */
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
            overflow-y: auto;
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

        .nav-link:hover {
            background: #fff0f5;
            color: var(--primary-pink);
        }

        .nav-link.active {
            background: var(--primary-pink);
            color: #fff;
            box-shadow: 0 4px 15px rgba(255, 107, 157, 0.4);
        }

        .nav-link.active i {
            color: #fff !important;
        }

        /* Main Content Area */
        .main-content {
            margin-left: var(--sidebar-width);
            padding: 2rem;
            min-height: 100vh;
            transition: margin-left 0.3s ease;
        }

        .top-bar {
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .btn-theme {
            background-color: var(--primary-pink);
            color: #fff;
            border: none;
            padding: 10px 25px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-theme:hover {
            background-color: var(--hover-pink);
            color: #fff;
        }

        /* --- Form Card Styling (Matched to Table Card style) --- */
        .form-card {
            background: #fff;
            border-radius: 20px;
            padding: 2rem;
            border: none;
            box-shadow: 0 5px 25px rgba(0, 0, 0, 0.04);
        }

        .form-label {
            font-weight: 600;
            color: #444;
            margin-bottom: 0.5rem;
        }

        .form-control,
        .form-select {
            padding: 0.7rem 1rem;
            border-radius: 10px;
            border: 1px solid #eee;
            background-color: #fafafa;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: var(--primary-pink);
            background-color: #fff;
            box-shadow: 0 0 0 0.25rem rgba(255, 107, 157, 0.15);
        }

        .img-preview {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 8px;
            border: 2px solid #eee;
            margin-left: 10px;
        }

        /* Mobile Responsive */
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

    <div class="overlay" id="overlay" onclick="toggleSidebar()"></div>

    <?php include 'sidebar.php'; ?>

    <div class="main-content">

        <div class="top-bar">
            <div class="d-flex align-items-center gap-3">
                <button class="hamburger" onclick="toggleSidebar()">
                    <i class="fas fa-bars"></i>
                </button>
                <div>
                    <h2 class="h4 mb-0 fw-bold">Edit Product</h2>
                    <span class="text-muted small">ID: #<?php echo $product['id']; ?></span>
                </div>
            </div>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-success shadow-sm d-flex align-items-center rounded-3 border-0 bg-success bg-opacity-10 text-success mb-4">
                <i class="fas fa-check-circle me-2"></i> <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <div class="form-card">
            <form method="post" action="">
                <input type="hidden" name="id" value="<?php echo $product['id']; ?>">

                <div class="mb-4">
                    <label class="form-label">Product Title</label>
                    <input type="text" name="title" class="form-control"
                        value="<?php echo htmlspecialchars($product['title']); ?>" required>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-4">
                        <label class="form-label">Category</label>
                        <select name="category" class="form-select" required>
                            <option value="" disabled>Select Category</option>
                            <option value="men" <?php echo ($product['category'] == 'men') ? 'selected' : ''; ?>>Men</option>
                            <option value="women" <?php echo ($product['category'] == 'women') ? 'selected' : ''; ?>>Women</option>
                        </select>
                    </div>

                    <div class="col-md-6 mb-4">
                        <label class="form-label">Price (₹)</label>
                        <input type="text" name="price" class="form-control"
                            value="<?php echo htmlspecialchars($product['price']); ?>" required>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="3"><?php echo htmlspecialchars($product['description']); ?></textarea>
                </div>

                <div class="p-3 bg-light rounded-3 mb-4 border border-dashed">
                    <h6 class="fw-bold mb-3 text-secondary">Product Images</h6>

                    <div class="mb-3">
                        <label class="small text-muted mb-1">Main Image URL</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0"><i class="fas fa-image text-muted"></i></span>
                            <input type="text" name="image" class="form-control border-start-0"
                                value="<?php echo htmlspecialchars($product['image']); ?>">
                            <?php if (!empty($product['image'])): ?>
                                <img src="<?php echo $product['image']; ?>" class="img-preview shadow-sm" alt="Preview">
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="small text-muted mb-1">Thumbnail 1</label>
                            <div class="d-flex align-items-center">
                                <input type="text" name="image1" class="form-control form-control-sm me-2" value="<?php echo htmlspecialchars($product['image1']); ?>">
                                <?php if (!empty($product['image1'])): ?>
                                    <img src="<?php echo $product['image1']; ?>" class="img-preview shadow-sm">
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="small text-muted mb-1">Thumbnail 2</label>
                            <div class="d-flex align-items-center">
                                <input type="text" name="image2" class="form-control form-control-sm me-2" value="<?php echo htmlspecialchars($product['image2']); ?>">
                                <?php if (!empty($product['image2'])): ?>
                                    <img src="<?php echo $product['image2']; ?>" class="img-preview shadow-sm">
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="small text-muted mb-1">Thumbnail 3</label>
                            <div class="d-flex align-items-center">
                                <input type="text" name="image3" class="form-control form-control-sm me-2" value="<?php echo htmlspecialchars($product['image3']); ?>">
                                <?php if (!empty($product['image3'])): ?>
                                    <img src="<?php echo $product['image3']; ?>" class="img-preview shadow-sm">
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex action-buttons pb-4 gap-2">
                    <button type="submit" class="btn btn-theme shadow-sm">
                        <i class="fas fa-save"></i> Update
                    </button>
                    <a href="inventory.php" class="btn btn-cancel border ms-md-3">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
    <script>
        // Sidebar Toggle Script (Same as Sliders page)
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('active');
            document.getElementById('overlay').classList.toggle('active');
        }
    </script>
</body>

</html>