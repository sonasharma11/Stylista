<?php
session_start();
include 'connection.php';

$login_error = "";
$showLoginModal = false;

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login_submit'])) {

    // Sanitize inputs to prevent basic SQL injection issues
    $name     = mysqli_real_escape_string($conn, trim($_POST['name']));
    $email    = mysqli_real_escape_string($conn, trim($_POST['email']));

    // Do NOT escape the password, we need the raw string for hashing/verifying
    $password = trim($_POST['password']);

    if ($name != "" && $email != "" && $password != "") {

        // Check if email exists
        $check = mysqli_query($conn, "SELECT * FROM users WHERE email='$email'");

        if (mysqli_num_rows($check) > 0) {
            // ------------------------------------------------
            // Case A: Email Exists (LOGIN)
            // ------------------------------------------------
            $user = mysqli_fetch_assoc($check);

            // USE PASSWORD_VERIFY
            // Checks the raw input password against the hash stored in the DB
            if (!password_verify($password, $user['password'])) {
                $login_error = "Account exists, but the password is incorrect.";
                $showLoginModal = true;
            } else {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];

                if (isset($user['role']) && $user['role'] == 'admin') {
                    header("Location: dashboard.php");
                    exit();
                } else {
                    $_SESSION['login_success'] = "Login Successful";
                    $showLoginModal = false;
                }
            }
        } else {
            // ------------------------------------------------
            // Case B: Create New User (REGISTER)
            // ------------------------------------------------

            // HASH THE PASSWORD BEFORE INSERTING
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $role = 'user';

            // Insert the $hashed_password, NOT the raw $password
            $insert_sql = "INSERT INTO users (name, email, password, role) VALUES ('$name', '$email', '$hashed_password', '$role')";

            if (mysqli_query($conn, $insert_sql)) {
                $new_user_id = mysqli_insert_id($conn);
                $_SESSION['user_id'] = $new_user_id;
                $_SESSION['user_name'] = $name;
                $_SESSION['login_success'] = "Account Created & Login Successful";
                $showLoginModal = false;
            } else {
                $login_error = "Error creating account: " . mysqli_error($conn);
                $showLoginModal = true;
            }
        }
    } else {
        $login_error = "Please fill in all fields.";
        $showLoginModal = true;
    }
}

// Only show modal if user NOT logged in
if (!isset($_SESSION['user_id'])) {
    $showLoginModal = true;
} else {
    $showLoginModal = false;
}
?>

<?php
// --------------------------------------------------------
// CART LOGIC ONLY (Wishlist Removed)
// --------------------------------------------------------
$my_cart_ids = array();

// CHECK: Agar User Logged In hai
if (isset($_SESSION['user_id'])) {
    $uid = $_SESSION['user_id'];

    // Fetch Cart from DB
    $c_sql = "SELECT product_id FROM cart WHERE user_id = '$uid'";
    $c_result = $conn->query($c_sql);
    while ($c_row = $c_result->fetch_assoc()) {
        $my_cart_ids[] = $c_row['product_id'];
    }
}
// CHECK: Agar User Guest hai
else {
    if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
        $my_cart_ids = $_SESSION['cart'];
    }
}

// Cart Count Update
$cart_count = count($my_cart_ids);
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>STYLISTA - Fashion Store</title>

    <link rel="stylesheet" href="index.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.theme.default.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #1a1a1a;
            overflow-x: hidden;
        }

        /* Navbar CSS */
        .navbar {
            padding-top: 1.5rem;
            padding-bottom: 1.5rem;
            background-color: white;
            border-bottom: 1px solid transparent;
        }

        .navbar-brand {
            font-family: 'Didot', 'Times New Roman', serif;
            font-size: 2rem;
            letter-spacing: 2px;
            text-transform: uppercase;
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
            margin: 0;
            color: #000;
        }

        .navbar-toggler {
            border: none;
            padding: 0;
            margin-right: 15px;
            color: #000;
            font-size: 1.5rem;
        }

        .navbar-toggler:focus {
            box-shadow: none;
        }

        .navbar-toggler.collapsed .bi-x-lg {
            display: none;
        }

        .navbar-toggler.collapsed .bi-list {
            display: block;
        }

        .navbar-toggler:not(.collapsed) .bi-x-lg {
            display: block;
        }

        .navbar-toggler:not(.collapsed) .bi-list {
            display: none;
        }

        .nav-category-link {
            text-decoration: none;
            color: #1a1a1a;
            margin-right: 1rem;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .nav-icon-link {
            color: #1a1a1a;
            font-size: 1.2rem;
            margin-left: 1.2rem;
            text-decoration: none;
            position: relative;
        }

        /* Cart Badge Style */
        .cart-badge {
            position: absolute;
            top: -5px;
            right: -10px;
            background-color: #dc3545;
            color: white;
            border-radius: 50%;
            padding: 2px 6px;
            font-size: 0.7rem;
            font-weight: bold;
            min-width: 18px;
            text-align: center;
        }

        #navbarToggleExternalContent {
            background-color: white;
            border-bottom: 1px solid #eee;
        }

        .menu-heading {
            margin-bottom: 1.5rem;
        }

        .menu-list {
            list-style: none;
            padding: 0;
        }

        .menu-list li {
            margin-bottom: 1rem;
        }

        .menu-list a {
            text-decoration: none;
            color: #1a1a1a;
            transition: color 0.2s;
        }

        .menu-list a:hover {
            color: #666;
        }

        .store-card img {
            width: 100%;
            height: 250px;
            object-fit: cover;
            margin-bottom: 10px;
        }

        .discover-link {
            text-decoration: underline;
            color: #000;
            font-size: 0.9rem;
            font-weight: 600;
        }

        @media (max-width: 768px) {
            .navbar-brand {
                font-size: 1.5rem;
            }

            .nav-category-link {
                display: none;
            }
        }

        .marquee {
            width: 100%;
            background-color: #fff239ff;
            overflow: hidden;
            white-space: nowrap;
            padding: 15px 0;
            font-size: 20px;
        }

        .marquee div {
            display: inline-block;
            padding-left: 100%;
            animation: scroll 60s linear infinite;
        }

        @keyframes scroll {
            0% {
                transform: translateX(0);
            }

            100% {
                transform: translateX(-100%);
            }
        }

        /* ------------------------------------------- */
        /* RESPONSIVE PRODUCT CSS START                */
        /* ------------------------------------------- */

        .section-header {
            font-family: 'Didot', 'Times New Roman', serif;
            font-size: 2.5rem;
            margin: 0;
            color: #000;
        }

        .product-item {
            margin-bottom: 30px;
            transition: all 0.3s ease;
        }

        .img-container {
            width: 100%;
            aspect-ratio: 3 / 4;
            overflow: hidden;
            position: relative;
            background-color: #f5f5f5;
        }

        .img-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: top center;
            transition: transform 0.6s cubic-bezier(0.25, 0.46, 0.45, 0.94);
            display: block;
        }

        @media (hover: hover) {
            .product-item:hover .img-container img {
                transform: scale(1.08);
            }

            .product-item:hover .product-actions {
                transform: translateY(0);
            }
        }

        /* Product Action Bar */
        .product-actions {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            padding: 10px;
            background: rgba(255, 255, 255, 0.95);
            display: flex;
            justify-content: center;
            gap: 8px;
            transform: translateY(100%);
            transition: transform 0.4s ease;
            z-index: 2;
        }

        /* REUSED CLASS FOR VIEW BUTTON TO MAINTAIN DESIGN */
        .add-cart-btn {
            flex-grow: 1;
            border-radius: 0;
            font-size: 0.75rem;
            text-transform: uppercase;
            font-weight: 600;
            letter-spacing: 1px;
            white-space: nowrap;
            /* Styling for View Button to look like old Add to Cart */
            background: transparent;
            border: 1px solid #000;
            color: #000;
            height: 35px;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .add-cart-btn:hover {
            background-color: #000;
            color: #fff;
        }

        .product-details {
            text-align: center;
            padding-top: 15px;
        }

        .product-title {
            font-size: 1rem;
            font-weight: 400;
            margin-bottom: 5px;
            color: #1a1a1a;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .product-price {
            font-weight: 600;
            font-size: 1rem;
            color: #1a1a1a;
            margin: 0;
        }

        @media (max-width: 991px) {
            .product-actions {
                transform: translateY(0) !important;
            }
        }

        @media (max-width: 576px) {
            .section-header {
                font-size: 1.8rem;
            }

            .product-item {
                padding-left: 5px;
                padding-right: 5px;
                margin-bottom: 25px;
            }

            .product-actions {
                padding: 6px !important;
                gap: 5px !important;
                background: rgba(255, 255, 255, 0.92);
            }

            .add-cart-btn {
                font-size: 9px !important;
                letter-spacing: 0.5px;
                height: 28px;
            }
        }

        .gallery-img {
            width: 100%;
            aspect-ratio: 3 / 4;
            object-fit: cover;
            object-position: center;
            display: block;
        }

        /* Hero Slider */
        .hero-slider-area {
            position: relative;
            width: 100%;
            height: 82vh;
            overflow: hidden;
        }

        .hero-slide-item {
            position: relative;
            height: 82vh;
            overflow: hidden;
        }

        .slide-bg-image {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-size: cover;
            background-position: top center;
            transform: scale(1.15);
            z-index: 0;
            transition: transform 5s ease;
        }

        .owl-item.active .slide-bg-image {
            transform: scale(1.2);
        }

        .hero-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(to bottom, rgba(0, 0, 0, 0.2), rgba(0, 0, 0, 0.6));
            z-index: 1;
        }

        .hero-content {
            position: absolute;
            top: 50%;
            left: 8%;
            transform: translateY(-50%);
            z-index: 2;
            color: white;
            max-width: 600px;
        }

        .hero-subtitle {
            font-size: 1.2rem;
            text-transform: uppercase;
            letter-spacing: 4px;
            margin-bottom: 10px;
            opacity: 0;
            transform: translateY(30px);
            transition: all 0.8s ease 0.2s;
        }

        .hero-title {
            font-family: 'Playfair Display', serif;
            font-size: 4.5rem;
            font-weight: 700;
            line-height: 1.1;
            margin-bottom: 30px;
            opacity: 0;
            transform: translateY(30px);
            transition: all 0.8s ease 0.4s;
        }

        @media (max-width: 768px) {
            .hero-title {
                font-size: 2.5rem;
            }

            .hero-subtitle {
                font-size: 0.9rem;
            }
        }

        .hero-btn {
            display: inline-block;
            padding: 15px 40px;
            background-color: white;
            color: black;
            text-transform: uppercase;
            font-weight: 600;
            text-decoration: none;
            letter-spacing: 1px;
            opacity: 0;
            transform: translateY(30px);
            transition: all 0.8s ease 0.6s;
        }

        .hero-btn:hover {
            background-color: #000;
            color: #fff;
        }

        .owl-item.active .hero-subtitle,
        .owl-item.active .hero-title,
        .owl-item.active .hero-btn {
            opacity: 1;
            transform: translateY(0);
        }

        /* Container spacing */
        .category-section {
            padding-top: 60px;
            padding-bottom: 60px;
        }

        /* Card Design */
        .custom-card {
            background-color: #000000ff;
            border: none;
            border-radius: 10px;
            margin-top: 50px;
            position: relative;
            overflow: visible;
            transition: transform 0.3s ease;
            cursor: pointer;
        }

        /* Image Pop-out Logic */
        .custom-card img {
            width: 85%;
            margin-top: -40px;
            border-radius: 8px;
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.3);
            transition: transform 0.3s ease;
            display: block;
            margin-left: auto;
            margin-right: auto;
        }

        /* Height Control */
        .custom-card .card-body {
            padding: 0.5rem;
        }

        /* Text Design */
        .custom-card h6 {
            color: white;
            font-weight: bold;
            text-transform: uppercase;
            margin: 0;
            padding: 5px 0;
            font-size: 0.9rem;
        }

        /* Hover Effect */
        .custom-card:hover {
            transform: translateY(-5px);
        }

        .custom-card:hover img {
            transform: scale(1.05);
        }

        /* Arrow Icon */
        .arrow-bg {
            background: white;
            color: #920013;
            border-radius: 50%;
            padding: 2px 5px;
            font-size: 10px;
            margin-left: 3px;
            vertical-align: middle;
        }

        /* --- MOBILE OPTIMIZATION --- */
        @media (max-width: 768px) {
            .category-section {
                padding-top: 30px;
                padding-bottom: 30px;
            }

            .custom-card {
                margin-top: 35px;
            }

            .custom-card img {
                margin-top: -25px;
                width: 90%;
            }

            .custom-card h6 {
                font-size: 0.6rem;
                padding: 5px 0;
                line-height: 1.2;
            }

            .arrow-bg {
                display: none;
            }
        }
    </style>

</head>

<body>

    <div class="sticky-top bg-white" style="z-index: 1030;">

        <nav class="navbar navbar-light">
            <div class="container-fluid px-3 px-md-4">

                <div class="d-flex align-items-center">
                    <button class="navbar-toggler collapsed" type="button" data-bs-toggle="collapse"
                        data-bs-target="#navbarToggleExternalContent" aria-controls="navbarToggleExternalContent"
                        aria-expanded="false" aria-label="Toggle navigation">

                        <i class="bi bi-list"></i>
                        <i class="bi bi-x-lg"></i>
                    </button>

                    <div class="d-none d-md-block ms-3">
                        <a href="women.php" class="nav-category-link">Women</a>
                        <a href="men.php" class="nav-category-link">Men</a>
                    </div>
                </div>

                <a class="navbar-brand" href="index.php">STYLISTA</a>

                <div class="d-flex align-items-center">
                    <a href="#" class="nav-icon-link" data-bs-toggle="modal" data-bs-target="#searchModal">
                        <i class="bi bi-search"></i>
                    </a>

                    <a href="cart.php" class="nav-icon-link">
                        <i class="bi bi-bag"></i>
                        <span id="cart-badge" class="cart-badge" style="<?php if ($cart_count == 0) echo 'display:none;'; ?>">
                            <?php echo $cart_count; ?>
                        </span>
                    </a>

                    <a href="profile.php" class="nav-icon-link fs-4 d-none d-md-block"><i class="bi bi-person"></i></a>
                </div>
            </div>
        </nav>

        <div class="collapse" style="background-color: #ffffffff;" id="navbarToggleExternalContent">
            <div class="container-fluid px-3 px-md-4 py-3">
                <div class="row">
                    <div class="col-12 d-md-none">
                        <ul class="menu-list">
                            <li>
                                <a href="profile.php" class="btn btn-dark rounded-pill text-light w-10">
                                    <i class="bi bi-person-circle fs-3"></i>
                                </a>
                            </li>
                        </ul>
                    </div>

                    <div class="col-md-6 mb-md-0">
                        <h3 class="menu-heading text-decoration-underline">Info</h3>
                        <ul class="menu-list fs-5">
                            <li><a href="returns.php">Returns</a></li>
                            <li><a href="shipping.php">Shipping</a></li>
                            <li><a href="cancel_order.php">Order cancellation</a></li>
                            <li><a href="payment.php">Payment options</a></li>
                            <li><a href="about.php">About</a></li>
                            <li><a href="contact.php">Contact</a></li>
                            <li><a href="faq.php">FAQ</a></li>
                        </ul>
                    </div>

                    <div class="col-md-6 d-none d-md-flex flex-column align-items-end justify-content-end">
                        <p class="fw-bold mx-5">STYLISTA FASHION REDEFINED</p>
                        <img class="img-fluid rounded" style="max-width: 50%; height: auto;" src="https://rukminim2.flixcart.com/image/832/832/xif0q/sunglass/e/d/m/free-size-designer-square-transparent-velora-original-imahgmyemhwtk5kh.jpeg?q=70&crop=false" alt="">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="hero-slider-area">
        <div class="owl-carousel owl-theme" id="heroSlider">

            <?php
            // 1. Write the query to fetch active slides ordered by your sort preference
            $slider_sql = "SELECT * FROM sliders WHERE status = 1 ORDER BY sort_order ASC";
            $slider_result = mysqli_query($conn, $slider_sql);

            // 2. Check if slides exist
            if (mysqli_num_rows($slider_result) > 0) {
                // 3. Loop through each row in the database
                while ($slide = mysqli_fetch_assoc($slider_result)) {
            ?>
                    <div class="item">
                        <div class="hero-slide-item">
                            <div class="slide-bg-image" style="background-image: url('<?php echo $slide['image_url']; ?>');"></div>

                            <div class="hero-overlay"></div>

                            <div class="hero-content">
                                <p class="hero-subtitle"><?php echo $slide['subtitle']; ?></p>

                                <h1 class="hero-title"><?php echo $slide['title']; ?></h1>

                                <a href="<?php echo $slide['btn_link']; ?>" class="hero-btn">
                                    <?php echo $slide['btn_text']; ?>
                                </a>
                            </div>
                        </div>
                    </div>
            <?php
                } // End While Loop
            } else {
                // Optional: Fallback if no slides exist in DB
                echo "<p>No slides found.</p>";
            }
            ?>

        </div>
    </div>

    <div class="marquee mt-2">
        <div>
            * 20% DISCOUNT ON YOUR FIRST ORDER * NEW SEASON * 20% DISCOUNT ON YOUR FIRST ORDER * NEW SEASON * 20% DISCOUNT ON YOUR FIRST ORDER * NEW SEASON
            * 20% DISCOUNT ON YOUR FIRST ORDER * NEW SEASON * 20% DISCOUNT ON YOUR FIRST ORDER * NEW SEASON * 20% DISCOUNT ON YOUR FIRST ORDER * NEW SEASON
            * 20% DISCOUNT ON YOUR FIRST ORDER * NEW SEASON * 20% DISCOUNT ON YOUR FIRST ORDER * NEW SEASON * 20% DISCOUNT ON YOUR FIRST ORDER * NEW SEASON
            * 20% DISCOUNT ON YOUR FIRST ORDER * NEW SEASON * 20% DISCOUNT ON YOUR FIRST ORDER * NEW SEASON * 20% DISCOUNT ON YOUR FIRST ORDER * NEW SEASON
        </div>
    </div>

    <div class="container category-section">
        <div class="row g-2 g-lg-5 justify-content-center text-center">

            <div class="col-4 col-lg-3">
                <a class="text-decoration-none" href="see_all.php">
                    <div class="card custom-card">
                        <img src="image/friends-with-skateboard.jpg" class="img-fluid" alt="Westernwear">
                        <div class="card-body">
                            <h6 class="">All <br> Collection <i class="fas fa-chevron-right arrow-bg"></i></h6>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-4 col-lg-3">
                <a class="text-decoration-none" href="men.php">
                    <div class="card custom-card">
                        <img src="image/vertical-shot-young-caucasian-man-sitting-outdoors copy.jpg" class="img-fluid" alt="Men's Apparel">
                        <div class="card-body">
                            <h6>Men <br> Collection <i class="fas fa-chevron-right arrow-bg"></i></h6>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-4 col-lg-3">
                <a class="text-decoration-none" href="women.php">
                    <div class="card custom-card">
                        <img src="image/outdoor-fashion-portrait-glamour-sensual-young-stylish-lady-wearing-trendy-fall-outfit-black-hat-street.jpg" class="img-fluid" alt="Footwear">
                        <div class="card-body">
                            <h6>Women <br> Collection <i class="fas fa-chevron-right arrow-bg"></i></h6>
                        </div>
                    </div>
                </a>
            </div>

        </div>
    </div>

    <?php
    $sql = "SELECT * FROM products";
    $result = $conn->query($sql);
    ?>

    <section class="container-fluid px-3 px-md-5">
        <div class="mb-5 d-flex justify-content-between align-items-center">
            <h2 class="section-header">Products</h2>
            <a href="see_all.php" class="text-decoration-underline text-dark fw-bold">SEE ALL</a>
        </div>

        <div class="row gx-2 gx-md-4">
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="col-6 col-md-4 col-lg-3 product-item">

                    <a href="view_product.php?id=<?php echo $row['id']; ?>" class="text-decoration-none">
                        <div class="img-container">

                            <img src="<?php echo $row['image']; ?>" alt="<?php echo htmlspecialchars($row['title']); ?>">

                            <div class="product-actions">

                                <a href="view_product.php?id=<?php echo $row['id']; ?>" class="action-btn add-cart-btn text-decoration-none">
                                    <i class="fa fa-eye mx-1"></i>VIEW</a>
                            </div>
                        </div>
                    </a>

                    <div class="product-details">
                        <p class="product-title"><?php echo htmlspecialchars($row['title']); ?></p>
                        <p class="product-price">₹ <?php echo number_format($row['price']); ?></p>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </section>

    <section class="mt-5">
        <div class="container-fluid px-3 px-md-5 mb-4 d-flex justify-content-between align-items-center">
            <h2 class="section-header">Latest Collection</h2>
            <p class="text-muted">Soon</p>
        </div>

        <div class="row g-0">
            <div class="col-6 col-md-6 col-lg-3">
                <img class="img-fluid w-100 gallery-img" src="https://framerusercontent.com/images/xthDdix6kJnC4vMF3eB2go5qfeU.jpeg?scale-down-to=1024" alt="Fashion Image 1" loading="lazy">
            </div>

            <div class="col-6 col-md-6 col-lg-3">
                <img class="img-fluid w-100 gallery-img" src="https://framerusercontent.com/images/48kMFMHdX9NPGSLT4KGYFTmQOk.jpeg?scale-down-to=1024" alt="Fashion Image 2" loading="lazy">
            </div>

            <div class="col-6 col-md-6 col-lg-3">
                <img class="img-fluid w-100 gallery-img" src="https://framerusercontent.com/images/FwbPRtwwVmuQePc8R9KR5NNFZA.jpeg?scale-down-to=1024" alt="Fashion Image 3" loading="lazy">
            </div>

            <div class="col-6 col-md-6 col-lg-3">
                <img class="img-fluid w-100 gallery-img" src="https://framerusercontent.com/images/Kwtncs2Fsn6CKBdnZdzMLes4Sqg.jpeg?scale-down-to=1024" alt="Fashion Image 4" loading="lazy">
            </div>
        </div>
    </section>

    <section class="container-fluid px-3 px-md-5">
        <div class="row mt-5 mb-5">
            <div class="col-md-3 mt-3">
                <i class="fa-solid fa-cube fa-rotate-270 fs-3 mb-3 px-2 py-2" style="background-color: #e0e0e0ff;"></i>
                <h5>Ship It Free</h5>
                <p>Free delivery on all qualifying orders, straight to your door.</p>
            </div>
            <div class="col-md-3 mt-3">
                <i class="fa-solid fa-money-check-dollar fs-3 mb-3 px-2 py-2" style="background-color: #e0e0e0ff;"></i>
                <h5>Money-Back Guarantee</h5>
                <p>Return your item for a full refund if it doesn’t meet your expectations.</p>
            </div>
            <div class="col-md-3 mt-3">
                <i class="fa-solid fa-headphones fs-3 mb-3 px-2 py-2" style="background-color: #e0e0e0ff;"></i>
                <h5>24/7 Customer Support</h5>
                <p>Our team is available 24/7 to address your inquiries.</p>
            </div>
            <div class="col-md-3 mt-3">
                <i class="fa-solid fa-clipboard-check fs-3 mb-3 px-2 py-2" style="background-color: #e0e0e0ff;"></i>
                <h5>Safe Checkout</h5>
                <p>Your payment details are always protected with advanced security.</p>
            </div>
        </div>
    </section>

    <section style="background-color: #e0e0e0ff;" class="py-5">
        <div class="container-fluid px-3 px-md-5">
            <div class="row align-items-end">
                <div class="mb-3">
                    <h3>Save 20% on Your Purchase Today.</h3>
                </div>

                <form method="POST" action="" id="subscribeForm">
                    <div class="row align-items-end g-3">
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control form-control-lg" name="email" placeholder="Enter your email" required>
                        </div>

                        <div class="col-md-6">
                            <button id="subscribeBtn" class="btn btn-dark btn-lg w-100" type="submit">
                                Subscribe
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <div class="modal fade" id="searchModal" tabindex="-1" aria-labelledby="searchModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header border-0 pb-0">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="input-group mb-4 border-bottom">
                        <span class="input-group-text bg-transparent border-0 ps-0">
                            <i class="bi bi-search fs-5"></i>
                        </span>
                        <input type="text" id="live_search" class="form-control border-0 fs-5 shadow-none" placeholder="Search for clothes, accessories..." autocomplete="off">
                    </div>

                    <div id="search_result_container" style="max-height: 400px; overflow-y: auto;">
                        <p class="text-muted text-center small">Start typing to see products...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="loginModal" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">

                <div class="modal-header bg-dark text-white text-center justify-content-center">
                    <h5 class="modal-title">Welcome to Stylista</h5>
                </div>

                <div class="modal-body">

                    <?php if (!empty($login_error)) { ?>
                        <div class="alert alert-danger"><?php echo $login_error; ?></div>
                    <?php } ?>

                    <form method="POST">
                        <input type="hidden" name="login_submit" value="1">

                        <div class="mb-3">
                            <label class="form-label">Full Name</label>
                            <input type="text" name="name" class="form-control" placeholder="Enter your name"
                                value="<?php echo isset($_POST['name']) ? $_POST['name'] : '' ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Email Address</label>
                            <input type="email" name="email" class="form-control" placeholder="name@gmail.com"
                                value="<?php echo isset($_POST['email']) ? $_POST['email'] : '' ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" placeholder="Enter password" required>
                        </div>

                        <button type="submit" class="btn btn-dark w-100">Continue</button>
                    </form>
                </div>

            </div>
        </div>
    </div>

    <footer class="bg-dark py-5">
        <div class="text-center">
            <h1
                style="font-family: 'Didot', 'Times New Roman', serif;
            font-size: 2rem;
            letter-spacing: 2px;"
                class="text-light">STYLISTA</h1>
            <div class="justify-content-center d-flex gap-4 fs-3 mt-4 mb-2">
                <i class="fa-brands fa-instagram fa-beat-fade" style="color: #ffffff;"></i>
                <i class="fa-brands fa-facebook fa-beat-fade" style="color: #ffffff;"></i>
                <i class="fa-brands fa-x-twitter fa-beat-fade" style="color: #ffffff;"></i>
                <i class="fa-brands fa-youtube fa-beat-fade" style="color: #ffffff;"></i>
                <i class="fa-brands fa-pinterest-p fa-beat-fade" style="color: #ffffff;"></i>
            </div>
            <p class="text-secondary mt-3">ⓒ 2025 STYLISTA. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        $(document).ready(function() {
            $(".owl-carousel").owlCarousel({
                loop: true,
                autoplay: true,
                autoplayTimeout: 4000,
                margin: 0,
                nav: false,
                dots: false,
                items: 1
            });
        });
    </script>

    <script>
        document.getElementById("subscribeForm").addEventListener("submit", function(e) {
            e.preventDefault();

            let btn = document.getElementById("subscribeBtn");
            btn.innerText = "Thank You";
            btn.classList.remove("btn-dark");
            btn.classList.add("btn-success");
            btn.disabled = true;
        });
    </script>

    <script>
        $(document).ready(function() {
            $("#live_search").on("keyup", function() {
                var input = $(this).val();

                if (input.length > 1) {
                    $.ajax({
                        url: "search_backend.php",
                        method: "POST",
                        data: {
                            query: input
                        },
                        success: function(data) {
                            $("#search_result_container").html(data);
                        },
                        error: function(xhr, status, error) {
                            console.error(xhr);
                        }
                    });
                } else {
                    $("#search_result_container").html('<p class="text-muted text-center small">Start typing to see products...</p>');
                }
            });
        });
    </script>

    <script>
        <?php if ($showLoginModal) { ?>
            var showModal = new bootstrap.Modal(document.getElementById('loginModal'));
            showModal.show();
        <?php } ?>
    </script>

</body>

</html>