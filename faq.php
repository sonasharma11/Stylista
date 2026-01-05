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

    <title>FAQ - STYLISTA</title>

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

        /* FAQ Styling */
        .faq-section {
            margin: 0 auto;
            padding: 2rem 0;
        }

        .accordion-item {
            border: 1px solid #e0e0e0 !important;
            transition: all 0.3s ease;
            overflow: hidden;
        }

        .accordion-item:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1) !important;
            transform: translateY(-2px);
        }

        .accordion-button {
            font-weight: 500;
            padding: 1.25rem 1.5rem;
            background-color: #fff;
            color: #333;
            transition: all 0.3s ease;
        }

        .accordion-button:not(.collapsed) {
            background-color: #f8f9fa;
            color: #000000ff;
            box-shadow: none;
        }

        .accordion-button:focus {
            box-shadow: none;
            border-color: #e0e0e0;
        }

        .accordion-button::after {
            transition: transform 0.3s ease;
        }

        .accordion-button:not(.collapsed)::after {
            transform: rotate(180deg);
        }

        .accordion-body {
            padding: 0 1.5rem 1.25rem 1.5rem;
        }

        .accordion-button i {
            font-size: 1.2rem;
        }

        .accordion-collapse {
            transition: height 0.35s ease;
        }

        /* Main Hero Image Responsive Handling */
        .hero-img {
            width: 100%;
            height: 500px;
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

            /* Adjust Hero Image height on mobile */
            .hero-img {
                height: 300px;
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
                    <h4 class="text-light py-4 m-0">FAQs</h4>
                </div>
            </div>
        </div>
    </div>

    <section class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-12 col-lg-9">

                <img class="img-fluid hero-img" src="https://framerusercontent.com/images/3FH10fiNMoNewyRZP1xMMZo5ub4.jpeg?scale-down-to=2048" alt="">

                <div class="faq-section">
                    <div class="accordion accordion-flush" id="faqAccordion">

                        <div class="accordion-item border rounded-3 mb-3 shadow-sm">
                            <h2 class="accordion-header" id="faqOne">
                                <button class="accordion-button rounded-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                    <span>What types of products can I purchase on this platform?</span>
                                </button>
                            </h2>
                            <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="faqOne" data-bs-parent="#faqAccordion">
                                <div class="accordion-body pt-0">
                                    <p class="mb-0 text-muted">Our platform offers a wide range of products, including clothing, electronics, home goods, beauty products, and more. Whether you’re looking for the latest tech gadgets, trendy fashion, or everyday essentials, you’ll find everything you need in one place.</p>
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item border rounded-3 mb-3 shadow-sm">
                            <h2 class="accordion-header" id="faqTwo">
                                <button class="accordion-button collapsed rounded-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                    <span>How do I create an account to start shopping?</span>
                                </button>
                            </h2>
                            <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="faqTwo" data-bs-parent="#faqAccordion">
                                <div class="accordion-body pt-0">
                                    <p class="mb-0 text-muted">Creating an account is easy! Simply click on the "Sign Up" button at the top right corner of our homepage, fill in your details, and verify your email address. Once registered, you’ll enjoy features like order tracking, personalized recommendations, and faster checkout.</p>
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item border rounded-3 mb-3 shadow-sm">
                            <h2 class="accordion-header" id="faqThree">
                                <button class="accordion-button collapsed rounded-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                                    <span>What payment methods are accepted?</span>
                                </button>
                            </h2>
                            <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="faqThree" data-bs-parent="#faqAccordion">
                                <div class="accordion-body pt-0">
                                    <p class="mb-0 text-muted">We accept a variety of payment methods to make your shopping experience convenient. These include major credit and debit cards, digital wallets like PayPal and Apple Pay, and bank transfers. In some regions, we also offer cash on delivery.</p>
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item border rounded-3 mb-3 shadow-sm">
                            <h2 class="accordion-header" id="faqFour">
                                <button class="accordion-button collapsed rounded-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
                                    <span>How can I track my order?</span>
                                </button>
                            </h2>
                            <div id="collapseFour" class="accordion-collapse collapse" aria-labelledby="faqFour" data-bs-parent="#faqAccordion">
                                <div class="accordion-body pt-0">
                                    <p class="mb-0 text-muted">After placing your order, you’ll receive a confirmation email with a tracking link. You can also log in to your account, navigate to the “Orders” section, and view the status and real-time updates of your shipment.</p>
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item border rounded-3 mb-3 shadow-sm">
                            <h2 class="accordion-header" id="faqFive">
                                <button class="accordion-button collapsed rounded-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFive" aria-expanded="false" aria-controls="collapseFive">
                                    <span>What is your return and exchange policy?</span>
                                </button>
                            </h2>
                            <div id="collapseFive" class="accordion-collapse collapse" aria-labelledby="faqFive" data-bs-parent="#faqAccordion">
                                <div class="accordion-body pt-0">
                                    <p class="mb-0 text-muted">We have a hassle-free return policy that allows you to return items within 30 days of delivery, provided they are unused and in their original condition. Exchanges are also possible for different sizes or colors of the same item. Just initiate the process via your account or contact customer support.</p>
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item border rounded-3 mb-3 shadow-sm">
                            <h2 class="accordion-header" id="faqSix">
                                <button class="accordion-button collapsed rounded-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSix" aria-expanded="false" aria-controls="collapseSix">
                                    <span>Do you offer discounts or promotional offers?</span>
                                </button>
                            </h2>
                            <div id="collapseSix" class="accordion-collapse collapse" aria-labelledby="faqSix" data-bs-parent="#faqAccordion">
                                <div class="accordion-body pt-0">
                                    <p class="mb-0 text-muted">Yes, we regularly offer discounts, seasonal sales, and exclusive deals for registered users. Sign up for our newsletter or follow us on social media to stay updated on the latest promotions.</p>
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item border rounded-3 mb-3 shadow-sm">
                            <h2 class="accordion-header" id="faqSeven">
                                <button class="accordion-button collapsed rounded-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSeven" aria-expanded="false" aria-controls="collapseSeven">
                                    <span>Are my payment details secure on this platform?</span>
                                </button>
                            </h2>
                            <div id="collapseSeven" class="accordion-collapse collapse" aria-labelledby="faqSeven" data-bs-parent="#faqAccordion">
                                <div class="accordion-body pt-0">
                                    <p class="mb-0 text-muted">Absolutely. We prioritize your security by using industry-standard encryption and secure payment gateways to protect your personal and financial information. Your privacy and safety are our top priorities.</p>
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item border rounded-3 mb-3 shadow-sm">
                            <h2 class="accordion-header" id="faqEight">
                                <button class="accordion-button collapsed rounded-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseEight" aria-expanded="false" aria-controls="collapseEight">
                                    <span>What should I do if I have an issue with my order?</span>
                                </button>
                            </h2>
                            <div id="collapseEight" class="accordion-collapse collapse" aria-labelledby="faqEight" data-bs-parent="#faqAccordion">
                                <div class="accordion-body pt-0">
                                    <p class="mb-0 text-muted">If you encounter any issues, such as missing items, incorrect orders, or delivery delays, our customer support team is here to help. You can contact us via email, live chat, or phone, and we’ll resolve the matter promptly.</p>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

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