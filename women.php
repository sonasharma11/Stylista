<?php
// 1. Session Start & Connection
session_start();
include 'connection.php';
include 'auth_session.php';

// --------------------------------------------------------
// CART LOGIC ONLY (Wishlist Removed)
// --------------------------------------------------------
$my_cart_ids = array();

// CHECK: Agar User Logged In hai -> Database se fetch karein
if (isset($_SESSION['user_id'])) {
    $uid = $_SESSION['user_id'];

    // Cart from DB
    $c_sql = "SELECT product_id FROM cart WHERE user_id = '$uid'";
    $c_result = $conn->query($c_sql);
    while ($c_row = $c_result->fetch_assoc()) {
        $my_cart_ids[] = $c_row['product_id'];
    }
}
// CHECK: Agar User Guest hai -> Session se fetch karein
else {
    // Cart Session Check
    if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
        $my_cart_ids = $_SESSION['cart'];
    }
}

// Cart Count Update (Badge ke liye - Logic Retained)
$cart_count = count($my_cart_ids);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>STYLISTA - Women's Collection</title>

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
            padding: 1.5rem 0;
            background-color: white;
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

        /* Cart Badge CSS */
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

        /* Responsive Navbar */
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

        #navbarToggleExternalContent {
            background-color: white;
            border-bottom: 1px solid #eee;
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

        /* Marquee */
        .marquee {
            width: 100%;
            background-color: #fff239ff;
            overflow: hidden;
            white-space: nowrap;
            padding: 15px 0;
            font-size: 22px;
        }

        .marquee div {
            display: inline-block;
            padding-left: 100%;
            animation: scroll 50s linear infinite;
        }

        @keyframes scroll {
            0% {
                transform: translateX(0);
            }

            100% {
                transform: translateX(-100%);
            }
        }

        /* Product Card CSS */
        .section-header {
            font-family: 'Didot', 'Times New Roman', serif;
            font-size: 2rem;
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
            transition: transform 0.6s;
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

        .product-badge {
            position: absolute;
            top: 10px;
            left: 10px;
            background-color: #fff;
            color: #000;
            padding: 4px 10px;
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            z-index: 2;
        }

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

        .action-btn {
            background: transparent;
            border: 1px solid #000;
            color: #000;
            width: 35px;
            height: 35px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
            text-decoration: none;
            font-size: 0.9rem;
        }

        .action-btn:hover {
            background-color: #000;
            color: #fff;
        }

        /* MAINTAINING THIS CLASS FOR EXACT DESIGN MATCH */
        .add-cart-btn {
            flex-grow: 1;
            border-radius: 0;
            font-size: 0.75rem;
            text-transform: uppercase;
            font-weight: 600;
            letter-spacing: 1px;
            white-space: nowrap;
            /* Styling to match previous button */
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

            .action-btn {
                width: 28px;
                height: 28px;
                font-size: 0.75rem;
            }

            .add-cart-btn {
                font-size: 9px !important;
                letter-spacing: 0.5px;
                height: 28px;
            }

            .navbar-brand {
                font-size: 1.5rem;
            }

            .nav-category-link {
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

    <div class="marquee mt-2">
        <div>
            * 20% DISCOUNT ON YOUR FIRST ORDER * NEW SEASON * 20% DISCOUNT ON YOUR FIRST ORDER * NEW SEASON * 20% DISCOUNT ON YOUR FIRST ORDER * NEW SEASON
            * 20% DISCOUNT ON YOUR FIRST ORDER * NEW SEASON * 20% DISCOUNT ON YOUR FIRST ORDER * NEW SEASON * 20% DISCOUNT ON YOUR FIRST ORDER * NEW SEASON
            * 20% DISCOUNT ON YOUR FIRST ORDER * NEW SEASON * 20% DISCOUNT ON YOUR FIRST ORDER * NEW SEASON * 20% DISCOUNT ON YOUR FIRST ORDER * NEW SEASON
            * 20% DISCOUNT ON YOUR FIRST ORDER * NEW SEASON * 20% DISCOUNT ON YOUR FIRST ORDER * NEW SEASON * 20% DISCOUNT ON YOUR FIRST ORDER * NEW SEASON
        </div>
    </div>

    <?php
    // Fetch Products (Women Category)
    $sql = "SELECT * FROM products WHERE category = 'women'";
    $result = $conn->query($sql);
    ?>

    <section class="container-fluid px-3 px-md-5">
        <div class="mt-5 mb-5 d-flex justify-content-between align-items-center">
            <h4 class="section-header">Women Collection</h4>
        </div>

        <div class="row gx-2 gx-md-4">
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="col-6 col-md-4 col-lg-3 product-item">
                    <a href="view_product.php?id=<?php echo $row['id']; ?>" class="text-decoration-none">
                        <div class="img-container">
                            <span class="product-badge">NEW</span>
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

    <section class="container-fluid px-3 px-md-5">
        <div class="row mt-5 mb-5">
            <div class="col-md-3 mt-3"><i class="fa-solid fa-cube fa-rotate-270 fs-3 mb-3 px-2 py-2" style="background-color: #e0e0e0ff;"></i>
                <h5>Ship It Free</h5>
                <p>Free delivery on all qualifying orders.</p>
            </div>
            <div class="col-md-3 mt-3"><i class="fa-solid fa-money-check-dollar fs-3 mb-3 px-2 py-2" style="background-color: #e0e0e0ff;"></i>
                <h5>Money-Back Guarantee</h5>
                <p>Return your item for a full refund.</p>
            </div>
            <div class="col-md-3 mt-3"><i class="fa-solid fa-headphones fs-3 mb-3 px-2 py-2" style="background-color: #e0e0e0ff;"></i>
                <h5>24/7 Support</h5>
                <p>Our team is available 24/7.</p>
            </div>
            <div class="col-md-3 mt-3"><i class="fa-solid fa-clipboard-check fs-3 mb-3 px-2 py-2" style="background-color: #e0e0e0ff;"></i>
                <h5>Safe Checkout</h5>
                <p>Payment details are always protected.</p>
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
                        <div class="col-md-6"><label class="form-label">Email</label><input type="email" class="form-control form-control-lg" name="email" placeholder="Enter your email" required></div>
                        <div class="col-md-6"><button id="subscribeBtn" class="btn btn-dark btn-lg w-100" type="submit">Subscribe</button></div>
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
                        <span class="input-group-text bg-transparent border-0 ps-0"><i class="bi bi-search fs-5"></i></span>
                        <input type="text" id="live_search" class="form-control border-0 fs-5 shadow-none" placeholder="Search..." autocomplete="off">
                    </div>
                    <div id="search_result_container" style="max-height: 400px; overflow-y: auto;">
                        <p class="text-muted text-center small">Start typing to see products...</p>
                    </div>
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        document.getElementById("subscribeForm").addEventListener("submit", function(e) {
            e.preventDefault();
            let btn = document.getElementById("subscribeBtn");
            btn.innerText = "Thank You";
            btn.disabled = true;
        });

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
                        }
                    });
                } else {
                    $("#search_result_container").html('<p class="text-muted text-center small">Start typing to see products...</p>');
                }
            });
        });
    </script>

</body>

</html>