<?php
// 1. Session Start & Connection
session_start();
include 'connection.php'; // Database connection zaroori hai
include 'auth_session.php';

// --------------------------------------------------------
// CART LOGIC (HYBRID: DATABASE + SESSION)
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

// Cart Count Update (Navbar Badge ke liye)
$cart_count = count($my_cart_ids);
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>About - STYLISTA</title>

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
        }

        /* Updated Navbar CSS */
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
            white-space: nowrap;
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

        .nav-category-link:hover {
            color: #666;
        }

        .nav-icon-link {
            color: #1a1a1a;
            font-size: 1.2rem;
            margin-left: 1.2rem;
            text-decoration: none;
            position: relative;
            /* Badge positioning ke liye zaroori */
        }

        /* CART BADGE CSS START */
        .cart-badge {
            position: absolute;
            top: -5px;
            right: -10px;
            background-color: #dc3545;
            /* Red color */
            color: white;
            border-radius: 50%;
            padding: 2px 6px;
            font-size: 0.7rem;
            font-weight: bold;
            min-width: 18px;
            text-align: center;
        }

        /* CART BADGE CSS END */

        #navbarToggleExternalContent {
            background-color: white;
            border-bottom: 1px solid #eee;
        }

        .menu-heading {
            font-size: 1.5rem;
            font-weight: 500;
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

        /* About Page Image Responsive Handling */
        .about-img {
            width: 100%;
            height: 650px;
            display: block;
            object-fit: cover;
        }

        /* Responsive Media Queries */
        @media (max-width: 768px) {
            .navbar-brand {
                font-size: 1.5rem;
            }

            .nav-category-link {
                display: none;
            }

            /* Adjust Image height on mobile so it doesn't take full screen */
            .about-img {
                height: 350px;
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

    <div class="bg-dark">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-12 col-lg-9">
                    <h4 class="text-light py-4 m-0">ABOUT</h4>
                </div>
            </div>
        </div>
    </div>

    <section class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-12 col-lg-9">
                <h1 class="fw-bold mb-3">CHIC STYLE, TRENDY</h1>
                <img class="img-fluid about-img" src="https://framerusercontent.com/images/XODLPWc6PeCwC0w4NrL8ZVrzks.jpeg?scale-down-to=2048" alt="">

                <div class="mt-5">
                    <h1 class="fw-bold">Stylista's World</h1>
                </div>

                <p class="mt-3">Welcome to Minna, your trusted destination for modern, stylish, and high-quality products designed to enhance your everyday life. At Minna, we believe in creating a seamless shopping experience that combines innovation, convenience, and exceptional value. Our journey began with a simple vision: to provide customers with a curated selection of products that inspire joy, confidence, and functionality. <br> <br>

                    From our carefully chosen collections to our commitment to sustainability, Minna stands out as more than just an e-commerce platform. We strive to connect with our customers on a deeper level by understanding their needs and delivering products that fit seamlessly into their lifestyles. Every item we offer reflects our dedication to quality craftsmanship and thoughtful design, ensuring that you receive products that exceed your expectations. <br> <br>

                    Our team is passionate about creating a community where customers feel valued and supported. Through our user-friendly website, exceptional customer service, and secure shopping environment, Minna aims to make every interaction a positive and memorable one. Whether you’re exploring our catalog for the latest trends or seeking timeless classics, we are here to help you find exactly what you’re looking for. <br> <br>

                    At Minna, we also take pride in giving back. We’re committed to minimizing our environmental footprint through sustainable practices and supporting initiatives that make a difference in the world. By choosing Minna, you’re not only getting the best in design and quality but also contributing to a brand that cares deeply about its customers, communities, and the planet. <br> <br>

                    Thank you for making Minna a part of your journey. We’re thrilled to have you with us and look forward to delivering exceptional experiences every step of the way.</p>
            </div>
        </div>
    </section>

    <section class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-12 col-lg-9">
                <h1 class="fw-bold">The Next Great Chapter</h1>
                <img class="img-fluid about-img mt-3" src="https://framerusercontent.com/images/OhwUvQjUN1Lg2ByA3JQ6yMpJdA.jpeg?scale-down-to=2048" alt="">
                <p class="mt-4">Where there’s music and style, there’s sport. Which makes Syla’s new partnership with Minna such a natural fit. A lifelong fan of the brand who grew up coveting her brother’s Minna hand-me-downs, the South pop star has always embraced the intersection of sport, movement and music. Below, we hear from Syla about the rhythm that drives her, the Minna staples she swears by, and why she refuses to be put in a box. </p>
            </div>
        </div>
    </section>

    <section class="container mt-5 mb-5">
        <div class="row justify-content-center">
            <div class="col-12 col-lg-9">
                <h1 class="fw-bold">STYLISTA store</h1>
                <p class="mt-4">We specialize in creating elegant, user-friendly Framer templates tailored for e-commerce businesses. Our goal is to empower brands with high-performance designs that enhance user experiences and drive growth. <br><br>

                    At Minna Store, we believe that great design tells a story. That’s why our templates are crafted to seamlessly blend aesthetics with functionality, ensuring your online store stands out while delivering exceptional performance. <br><br>

                    Let’s build your next digital success together.</p>
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

</body>

</html>