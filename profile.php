<?php
session_start();
include 'connection.php';
include 'auth_session.php';

// --- 1. AUTHORIZATION ---
if (isset($_SESSION['user_id'])) {
    $uid = $_SESSION['user_id'];
}

$user_id = $_SESSION['user_id'];

// --- 2. FETCH USER DATA ---
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_data = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Profile Image Logic
$img_src = !empty($user_data['profile_image']) ? $user_data['profile_image'] : "https://ui-avatars.com/api/?name=" . urlencode($user_data['name']) . "&background=212529&color=fff";
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile | STYLISTA</title>

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

        /* --- PROFILE PAGE SPECIFIC --- */
        .info-card {
            background: white;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 30px;
            margin-bottom: 20px;
        }

        .info-label {
            color: #6c757d;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 5px;
            font-weight: 600;
        }

        .info-value {
            font-size: 1.1rem;
            font-weight: 500;
            color: var(--primary-black);
            margin-bottom: 20px;
        }

        .welcome-banner {
            background-color: #fff;
            border-left: 4px solid var(--primary-gold);
            padding: 20px;
            border-radius: 4px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.03);
            margin-bottom: 25px;
        }

        .btn-edit {
            background-color: var(--primary-black);
            color: white;
            border: none;
            padding: 10px 25px;
            border-radius: 4px;
            font-weight: 500;
            transition: 0.3s;
        }

        .btn-edit:hover {
            background-color: var(--primary-gold);
            color: white;
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
                        <a href="profile.php" class="nav-link-custom active"><i class="bi bi-person"></i> My Profile</a>
                        <a href="cart.php" class="nav-link-custom"><i class="bi bi-bag"></i> Cart</a>
                        <a href="my_orders.php" class="nav-link-custom"><i class="bi bi-box-seam"></i> Orders</a>
                        <a href="wishlist.php" class="nav-link-custom"><i class="bi bi-heart"></i> Wishlist</a>
                        <div class="border-top my-2"></div>
                        <a href="logout.php" class="nav-link-custom text-danger"><i class="bi bi-box-arrow-right"></i> Log Out</a>
                    </div>
                </div>
            </div>

            <div class="col-lg-9">
                <h4 class="mb-4 fw-bold" style="font-family: var(--font-heading);">Account Overview</h4>

                <div class="welcome-banner">
                    <h4 class="fw-bold mb-2">Welcome back, <?php echo explode(' ', $user_data['name'])[0]; ?>!</h4>
                    <p class="text-muted mb-0">
                        From your account dashboard you can view your <a href="my_orders.php" class="text-dark fw-bold">recent orders</a>,
                        manage your <a href="addresses.php" class="text-dark fw-bold">shipping addresses</a>,
                        and edit your login details.
                    </p>
                </div>

                <div class="info-card">
                    <div class="d-flex justify-content-between align-items-center mb-4 border-bottom">
                        <h5 class="fw-bold mb-0">Personal Information</h5>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-2">
                                <label class="info-label"><i class="bi bi-person me-2"></i>Full Name</label>
                                <div class="info-value"><?php echo htmlspecialchars($user_data['name']); ?></div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-2">
                                <label class="info-label"><i class="bi bi-envelope me-2"></i>Email Address</label>
                                <div class="info-value"><?php echo htmlspecialchars($user_data['email']); ?></div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-2">
                                <label class="info-label"><i class="bi bi-shield-lock me-2"></i>Password</label>
                                <div class="info-value" style="letter-spacing: 3px;">••••••••</div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-2">
                                <label class="info-label"><i class="bi bi-phone me-2"></i>Phone Number</label>
                                <div class="info-value">
                                    <?php echo !empty($user_data['mobile']) ? $user_data['mobile'] : '<span class="text-muted small">Not Added</span>'; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>