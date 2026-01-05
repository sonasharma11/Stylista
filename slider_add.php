<?php
// slider_add.php
include 'connection.php';
include 'auth_session.php';
$message = "";
$error = "";

// --- INSERT LOGIC ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Data Sanitization to prevent SQL Injection
    $subtitle   = mysqli_real_escape_string($conn, $_POST['subtitle']);
    $title      = mysqli_real_escape_string($conn, $_POST['title']);
    $btn_text   = mysqli_real_escape_string($conn, $_POST['btn_text']);
    $btn_link   = mysqli_real_escape_string($conn, $_POST['btn_link']);
    $image_url  = mysqli_real_escape_string($conn, $_POST['image_url']);
    $sort_order = intval($_POST['sort_order']);
    $status     = intval($_POST['status']);

    // SQL Query
    $sql = "INSERT INTO sliders (subtitle, title, btn_text, btn_link, image_url, sort_order, status) 
            VALUES ('$subtitle', '$title', '$btn_text', '$btn_link', '$image_url', '$sort_order', '$status')";

    if (mysqli_query($conn, $sql)) {
        echo "<script>alert('New slider added successfully!'); window.location='sliders.php';</script>";
    } else {
        $error = "Error: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Add Slider - STYLISTA</title>

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

        /* --- Sidebar CSS --- */
        .sidebar {
            width: var(--sidebar-width);
            background: #fff;
            height: 100vh;
            position: fixed;
            top: 0; left: 0;
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

        .nav-link:hover { background: #fff0f5; color: var(--primary-pink); }
        .nav-link.active { background: var(--primary-pink); color: #fff; box-shadow: 0 4px 15px rgba(255, 107, 157, 0.4); }
        .nav-link.active i { color: #fff !important; }

        /* --- Main Content Area --- */
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
        .btn-theme:hover { background-color: var(--hover-pink); color: #fff; transform: translateY(-1px); }

        /* --- Form Card Styling --- */
        .form-card {
            background: #fff;
            border-radius: 20px;
            padding: 2rem;
            border: none;
            box-shadow: 0 5px 25px rgba(0, 0, 0, 0.04);
        }

        .form-label { font-weight: 600; color: #555; margin-bottom: 0.5rem; font-size: 0.9rem; }
        .form-control, .form-select {
            padding: 0.7rem 1rem;
            border-radius: 10px;
            border: 1px solid #eee;
            background-color: #fafafa;
            transition: all 0.3s;
        }
        .form-control:focus, .form-select:focus {
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

        /* --- Mobile Responsive --- */
        .hamburger { display: none; font-size: 1.6rem; cursor: pointer; border: none; background: none; color: #333; }
        
        .overlay {
            display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0, 0, 0, 0.5); z-index: 999; opacity: 0; transition: opacity 0.3s;
        }
        .overlay.active { display: block; opacity: 1; }

        @media (max-width: 992px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.active { transform: translateX(0); }
            .main-content { margin-left: 0; padding: 1rem; }
            .hamburger { display: block; }
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
                    <h2 class="h4 mb-0 fw-bold">Add New Slider</h2>
                </div>
            </div>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger shadow-sm rounded-3 mb-4">
                <i class="fas fa-exclamation-circle me-2"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <div class="form-card">
            <form method="post" action="">
                
                <div class="section-title">
                    <i class="fas fa-pen-nib me-2"></i>Content Details
                </div>

                <div class="row">
                    <div class="col-md-6 mb-4">
                        <label class="form-label">Subtitle <small class="text-muted fw-normal">(Small text above title)</small></label>
                        <input type="text" name="subtitle" class="form-control" placeholder="e.g. New Collection" required>
                    </div>

                    <div class="col-md-6 mb-4">
                        <label class="form-label">Main Title <small class="text-muted fw-normal">(HTML allowed)</small></label>
                        <input type="text" name="title" class="form-control" placeholder="e.g. Summer <br> Vibes" required>
                    </div>
                </div>

                <div class="section-title mt-2">
                    <i class="fas fa-mouse-pointer me-2"></i>Button Configuration
                </div>

                <div class="row">
                    <div class="col-md-6 mb-4">
                        <label class="form-label">Button Text</label>
                        <input type="text" name="btn_text" class="form-control" placeholder="e.g. Shop Now" required>
                    </div>

                    <div class="col-md-6 mb-4">
                        <label class="form-label">Button Link</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white text-muted border-end-0"><i class="fas fa-link"></i></span>
                            <input type="text" name="btn_link" class="form-control border-start-0" placeholder="e.g. shop.php" required>
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
                        <input type="text" name="image_url" class="form-control border-start-0" placeholder="https://..." required>
                    </div>
                    <div class="form-text">Paste the direct link to the image (JPG/PNG).</div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-4">
                        <label class="form-label">Sort Order</label>
                        <input type="number" name="sort_order" class="form-control" value="0">
                        <div class="form-text">Lower numbers appear first.</div>
                    </div>

                    <div class="col-md-6 mb-4">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                </div>

                <div class="d-flex justify-content-start gap-3 mt-4 pt-3 border-top">
                    <button type="submit" class="btn btn-theme shadow-sm px-5">
                        <i class="fas fa-plus-circle"></i> Add
                    </button>
                    <a href="sliders.php" class="btn btn-light border px-4">Cancel</a>
                </div>

            </form>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
    <script>
        // Sidebar Toggle Script
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('active');
            document.getElementById('overlay').classList.toggle('active');
        }
    </script>
</body>
</html>