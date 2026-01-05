<?php
// sliders.php
include 'connection.php';
include 'auth_session.php';

// --- DELETE LOGIC ---
if (isset($_GET['delete_id'])) {
    $id = intval($_GET['delete_id']);
    $delete_sql = "DELETE FROM sliders WHERE id = $id";
    if (mysqli_query($conn, $delete_sql)) {
        echo "<script>alert('Slider deleted successfully!'); window.location='sliders.php';</script>";
    } else {
        echo "<script>alert('Error deleting record.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Manage Sliders - STYLISTA</title>

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

        /* --- Sidebar CSS (Matches sidebar.php structure) --- */
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
            padding: 8px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
        }

        .btn-theme:hover {
            background-color: var(--hover-pink);
            color: #fff;
        }

        /* --- Table Card --- */
        .table-card {
            background: #fff;
            border-radius: 20px;
            padding: 1.5rem;
            border: none;
            box-shadow: 0 5px 25px rgba(0, 0, 0, 0.04);
        }

        .table thead th {
            background-color: #333;
            color: #fff;
            border: none;
            font-weight: 500;
            padding: 12px;
        }

        .table tbody td {
            vertical-align: middle;
            padding: 12px;
            border-bottom: 1px solid #f0f0f0;
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

            .btn-text-mobile {
                display: none;
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
                margin-bottom: 1rem;
                background: #fff;
                border-radius: 16px;
                box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
                border: 1px solid #f0f0f0;
                padding: 1rem;
            }

            .table td {
                text-align: right;
                padding: 0.6rem 0;
                display: flex;
                justify-content: space-between;
                align-items: center;
                border-bottom: 1px solid #f8f9fa;
            }

            .table td:last-child {
                border-bottom: none;
            }

            .table td::before {
                content: attr(data-label);
                font-weight: 700;
                color: #999;
                font-size: 0.75rem;
                text-transform: uppercase;
                text-align: left;
            }

            .mobile-img {
                width: 60px !important;
                height: 40px !important;
            }
        }
    </style>
</head>

<body>

    <?php include 'sidebar.php'; ?>

    <div class="main-content">

        <div class="top-bar">
            <div class="d-flex align-items-center gap-3">
                <button class="hamburger" onclick="toggleSidebar()">
                    <i class="fas fa-bars"></i>
                </button>
                <h2 class="h4 mb-0 fw-bold">Slider Management</h2>
            </div>

            <a href="slider_add.php" class="btn-theme shadow-sm">
                <i class="fas fa-plus"></i> <span class="btn-text-mobile ps-1">New Slider</span>
            </a>
        </div>

        <div class="table-card">
            <table class="table table-hover mb-0">
                <thead class="table-dark rounded-3">
                    <tr>
                        <th class="text-center" width="80">Order</th>
                        <th width="150">Image</th>
                        <th>Content Details</th>
                        <th>Button Config</th>
                        <th>Status</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql = "SELECT * FROM sliders ORDER BY sort_order ASC";
                    $result = mysqli_query($conn, $sql);

                    if (mysqli_num_rows($result) > 0) {
                        while ($row = mysqli_fetch_assoc($result)) {
                    ?>
                            <tr>
                                <td data-label="Order" class="text-center fw-bold text-muted">
                                    #<?php echo $row['sort_order']; ?>
                                </td>

                                <td data-label="Image">
                                    <img src="<?php echo $row['image_url']; ?>" alt="Slide"
                                        class="shadow-sm rounded mobile-img"
                                        style="width: 100px; height: 60px; object-fit: cover;">
                                </td>

                                <td data-label="Content">
                                    <div class="text-end text-md-start">
                                        <small class="text-uppercase text-secondary fw-bold" style="font-size: 0.7rem;">
                                            <?php echo $row['subtitle']; ?>
                                        </small>
                                        <div class="fw-bold text-dark mt-1">
                                            <?php echo strip_tags($row['title']); ?>
                                        </div>
                                    </div>
                                </td>

                                <td data-label="Button">
                                    <div class="text-end text-md-start">
                                        <span class="badge bg-light text-dark border">
                                            <?php echo $row['btn_text']; ?>
                                        </span>
                                        <div class="small text-muted mt-1">
                                            <i class="fas fa-link me-1"></i><?php echo $row['btn_link']; ?>
                                        </div>
                                    </div>
                                </td>

                                <td data-label="Status">
                                    <?php if ($row['status'] == 1): ?>
                                        <span class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill">Active</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger bg-opacity-10 text-danger px-3 py-2 rounded-pill">Inactive</span>
                                    <?php endif; ?>
                                </td>

                                <td data-label="Actions" class="text-end">
                                    <a href="slider_edit.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-outline-warning px-4">
                                        <i class="fas fa-edit"></i> 
                                    </a>

                                    <a href="sliders.php?delete_id=<?php echo $row['id']; ?>"
                                        class="btn btn-sm btn-outline-danger px-4"
                                        onclick="return confirm('Are you sure you want to delete this slider?');">
                                        <i class="fas fa-trash"></i> 
                                    </a>
                                </td>
                            </tr>
                    <?php
                        }
                    } else {
                        echo "<tr><td colspan='6' class='text-center py-5 text-muted'>No sliders found.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
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