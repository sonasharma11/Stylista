<?php
// slider_edit.php
include 'connection.php';
include 'auth_session.php';
$message = "";
$error = "";

// 1. GET LOGIC: ID चेक करें और डेटा लाएं
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $sql = "SELECT * FROM sliders WHERE id = $id";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
    } else {
        echo "<script>alert('Slider not found!'); window.location='sliders.php';</script>";
        exit();
    }
} else {
    header("Location: sliders.php");
    exit();
}

// 2. POST LOGIC: डेटा अपडेट करें
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $id = intval($_POST['id']);
    $subtitle   = mysqli_real_escape_string($conn, $_POST['subtitle']);
    $title      = mysqli_real_escape_string($conn, $_POST['title']);
    $btn_text   = mysqli_real_escape_string($conn, $_POST['btn_text']);
    $btn_link   = mysqli_real_escape_string($conn, $_POST['btn_link']);
    $image_url  = mysqli_real_escape_string($conn, $_POST['image_url']);
    $sort_order = intval($_POST['sort_order']);
    $status     = intval($_POST['status']);

    // Update Query
    $update_sql = "UPDATE sliders SET 
                   subtitle='$subtitle', 
                   title='$title', 
                   btn_text='$btn_text', 
                   btn_link='$btn_link', 
                   image_url='$image_url', 
                   sort_order='$sort_order', 
                   status='$status' 
                   WHERE id=$id";

    if (mysqli_query($conn, $update_sql)) {
        $message = "Slider updated successfully!";
        // रिफ्रेश डेटा ताकि फॉर्म में नया डेटा दिखे
        $result = mysqli_query($conn, "SELECT * FROM sliders WHERE id = $id");
        $row = mysqli_fetch_assoc($result);
    } else {
        $error = "Error updating record: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Edit Slider - STYLISTA</title>

    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">

    <style>
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

        /* --- Sidebar CSS (Consistent with other pages) --- */
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

        /* --- Main Content --- */
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
            transform: translateY(-1px);
        }

        /* --- Form Styling --- */
        .form-card {
            background: #fff;
            border-radius: 20px;
            padding: 2rem;
            border: none;
            box-shadow: 0 5px 25px rgba(0, 0, 0, 0.04);
        }

        .form-label {
            font-weight: 600;
            color: #555;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
        }

        .form-control,
        .form-select {
            padding: 0.7rem 1rem;
            border-radius: 10px;
            border: 1px solid #eee;
            background-color: #fafafa;
            transition: all 0.3s;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: var(--primary-pink);
            background-color: #fff;
            box-shadow: 0 0 0 0.25rem rgba(255, 107, 157, 0.15);
        }

        .section-title {
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #999;
            font-weight: 700;
            margin-bottom: 1.5rem;
            border-bottom: 1px solid #f0f0f0;
            padding-bottom: 10px;
        }

        /* Image Preview Style */
        .img-preview {
            width: 100%;
            max-width: 300px;
            height: 150px;
            object-fit: cover;
            border-radius: 10px;
            margin-top: 10px;
            border: 1px solid #eee;
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
                    <h2 class="h4 mb-0 fw-bold">Edit Slider</h2>
                    <span class="text-muted small">Update ID: #<?php echo $row['id']; ?></span>
                </div>
            </div>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-success shadow-sm rounded-3 mb-4 bg-success bg-opacity-10 text-success border-0">
                <i class="fas fa-check-circle me-2"></i> <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger shadow-sm rounded-3 mb-4">
                <i class="fas fa-exclamation-circle me-2"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <div class="form-card">
            <form method="post" action="">
                <input type="hidden" name="id" value="<?php echo $row['id']; ?>">

                <div class="section-title">
                    <i class="fas fa-pen-nib me-2"></i>Content Details
                </div>

                <div class="row">
                    <div class="col-md-6 mb-4">
                        <label class="form-label">Subtitle</label>
                        <input type="text" name="subtitle" class="form-control"
                            value="<?php echo htmlspecialchars($row['subtitle']); ?>" required>
                    </div>

                    <div class="col-md-6 mb-4">
                        <label class="form-label">Main Title</label>
                        <input type="text" name="title" class="form-control"
                            value="<?php echo htmlspecialchars($row['title']); ?>" required>
                    </div>
                </div>

                <div class="section-title mt-2">
                    <i class="fas fa-mouse-pointer me-2"></i>Button Configuration
                </div>

                <div class="row">
                    <div class="col-md-6 mb-4">
                        <label class="form-label">Button Text</label>
                        <input type="text" name="btn_text" class="form-control"
                            value="<?php echo htmlspecialchars($row['btn_text']); ?>" required>
                    </div>

                    <div class="col-md-6 mb-4">
                        <label class="form-label">Button Link</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white text-muted border-end-0"><i class="fas fa-link"></i></span>
                            <input type="text" name="btn_link" class="form-control border-start-0"
                                value="<?php echo htmlspecialchars($row['btn_link']); ?>" required>
                        </div>
                    </div>
                </div>

                <div class="section-title mt-2">
                    <i class="fas fa-image me-2"></i>Media & Settings
                </div>

                <div class="mb-4">
                    <label class="form-label">Slider Image URL</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white text-muted border-end-0"><i class="fas fa-globe"></i></span>
                        <input type="text" name="image_url" class="form-control border-start-0"
                            value="<?php echo htmlspecialchars($row['image_url']); ?>" required>
                    </div>
                    <?php if (!empty($row['image_url'])): ?>
                        <div class="mt-2">
                            <small class="text-muted d-block mb-1">Current Image:</small>
                            <img src="<?php echo $row['image_url']; ?>" class="img-preview shadow-sm" alt="Current Slider">
                        </div>
                    <?php endif; ?>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-4">
                        <label class="form-label">Sort Order</label>
                        <input type="number" name="sort_order" class="form-control"
                            value="<?php echo $row['sort_order']; ?>">
                    </div>

                    <div class="col-md-6 mb-4">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="1" <?php echo ($row['status'] == 1) ? 'selected' : ''; ?>>Active</option>
                            <option value="0" <?php echo ($row['status'] == 0) ? 'selected' : ''; ?>>Inactive</option>
                        </select>
                    </div>
                </div>

                <div class="d-flex justify-content-start gap-3 mt-4 pt-3 border-top">
                    <button type="submit" class="btn btn-theme shadow-sm px-5">
                        <i class="fas fa-save"></i> save
                    </button>
                    <a href="sliders.php" class="btn btn-light border px-4">Cancel</a>
                </div>

            </form>
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