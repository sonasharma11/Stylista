<?php
// 1. Session Start & Connection
session_start();
include 'connection.php'; // Database connection zaroori hai cart fetch karne ke liye
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

    <title>Returns - STYLISTA</title>

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
            height: 550px;
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

            /* Adjust Hero Image height on mobile so it doesn't take full screen */
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
                    <h4 class="text-light py-4 m-0">RETURNS</h4>
                </div>
            </div>
        </div>
    </div>

    <section class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-12 col-lg-9">

                <img class="img-fluid hero-img" src="https://framerusercontent.com/images/woNV1r0kD0UqCenvutjxtpk7sko.png" alt="">

                <div class="mt-5">
                    <h4 class="fw-bold">What Is Stylista's Return Policy?</h4>
                    <p class="mt-3">We give you 60 days to try out your Minna purchase to make sure it works for you. </p>
                    <p class="mt-4">Here’s what you need to know:</p>

                    <div class="mt-4">
                        <p>• You can return within 60 days of an online order delivery or Minna store purchase. <br>
                            • Proof of purchase is required to return. <br>
                            • Items purchased at Stylista Clearance stores cannot be returned, and Stylista Clearance stores do not accept returns of any kind. <br>
                            • Returns are free for Stylista Members. So go ahead, shop with confidence and enjoy your 60-day trial.</p>
                    </div>

                    <div class="faq-section">
                        <h4 class="fw-bold mb-4">FAQs</h4>
                        <div class="accordion accordion-flush" id="faqAccordion">
                            <div class="accordion-item border rounded-3 mb-3 shadow-sm">
                                <h2 class="accordion-header" id="faqOne">
                                    <button class="accordion-button rounded-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                        <i class="bi bi-box-seam me-3"></i>
                                        <span>How do I return an online order?</span>
                                    </button>
                                </h2>
                                <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="faqOne" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body pt-0">
                                        <p class="mb-0 text-muted">Stylista Members and guests can return online orders at most Stylista stores or ship their return back to us.</p>
                                    </div>
                                </div>
                            </div>

                            <div class="accordion-item border rounded-3 mb-3 shadow-sm">
                                <h2 class="accordion-header" id="faqTwo">
                                    <button class="accordion-button collapsed rounded-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                        <i class="bi bi-shop me-3"></i>
                                        <span>How do I return a Stylista store purchase?</span>
                                    </button>
                                </h2>
                                <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="faqTwo" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body pt-0">
                                        <p class="mb-0 text-muted">You can take the items you want to return and your proof of purchase to any Stylista store.</p>
                                    </div>
                                </div>
                            </div>

                            <div class="accordion-item border rounded-3 mb-3 shadow-sm">
                                <h2 class="accordion-header" id="faqThree">
                                    <button class="accordion-button collapsed rounded-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                                        <i class="bi bi-exclamation-triangle me-3"></i>
                                        <span>What about defective or flawed items?</span>
                                    </button>
                                </h2>
                                <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="faqThree" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body pt-0">
                                        <p class="mb-0 text-muted">We stand behind our shoes and gear. If it's been less than 60 days since your purchase, simply return the item. If it's been more than 60 days and your item is potentially defective or flawed, please contact our support team.</p>
                                    </div>
                                </div>
                            </div>

                            <div class="accordion-item border rounded-3 mb-3 shadow-sm">
                                <h2 class="accordion-header" id="faqFour">
                                    <button class="accordion-button collapsed rounded-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
                                        <i class="bi bi-tag me-3"></i>
                                        <span>Does Stylista offer price adjustments?</span>
                                    </button>
                                </h2>
                                <div id="collapseFour" class="accordion-collapse collapse" aria-labelledby="faqFour" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body pt-0">
                                        <p class="mb-0 text-muted">Yes, we offer Stylista Members price adjustments for eligible items.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div>
                    <h4 class="fw-bold">Price Exchanges</h4>
                    <p class="mt-3 mb-5">We know how important it is to feel confident in your purchase, which is why we offer a price adjustment policy for eligible products. If the price of an item you purchased drops within seven days of your order date, you can request a price exchange to receive a refund for the difference. To take advantage of this benefit, simply reach out to our customer service team with your order details, and we will verify the eligibility of your request. Price exchanges apply only to identical items in the same size, color, and style that remain in stock at the time of the request. This policy ensures you always get the best value on your purchases without worrying about missing out on discounts or promotions. Keep in mind that items purchased during special clearance events may not qualify for price adjustments.</p>
                </div>
                <hr>
                <div class="mt-5 mb-5">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="card bg-dark text-light p-4 h-100">
                                <h4>Work days</h4>
                                <p>Monday - Friday: 9am-9pm ET <br> Saturday & Sunday: 10am-7pm ET</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-dark text-light p-4 h-100">
                                <h4>Email</h4>
                                <p>Strive to answer emails within 48 hours <br> info@mail.com</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-dark text-light p-4 h-100">
                                <h4>Phone</h4>
                                <p>Online Inquiries | 123-456-7890 <br> Store Inquiries | 123-456-7890</p>
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