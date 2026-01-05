<?php
session_start();
include 'connection.php';
include 'auth_session.php';

// 1. Check Product ID
if (!isset($_GET['id'])) die("Product not specified.");
$id = (int)$_GET['id'];

// 2. Fetch Product Details
$sql = "SELECT * FROM products WHERE id=$id";
$result = $conn->query($sql);
if ($result->num_rows == 0) die("Product not found.");
$product = $result->fetch_assoc();

// --------------------------------------------------------
// 3. CHECK IF PRODUCT IS ALREADY IN CART OR WISHLIST
// --------------------------------------------------------
$is_in_cart = false;
$is_in_wishlist = false;

if (isset($_SESSION['user_id'])) {
    $uid = $_SESSION['user_id'];

    // --- CHECK CART TABLE ---
    // NOTE: Make sure your column names are 'user_id' and 'product_id'
    $check_cart = $conn->query("SELECT * FROM cart WHERE user_id='$uid' AND product_id='$id'");
    if ($check_cart->num_rows > 0) {
        $is_in_cart = true;
    }

    // --- CHECK WISHLIST TABLE ---
    $check_wish = $conn->query("SELECT * FROM wishlist WHERE user_id='$uid' AND product_id='$id'");
    if ($check_wish->num_rows > 0) {
        $is_in_wishlist = true;
    }
} else {
    // --- GUEST USER CHECK (SESSION BASED) ---
    // If you store cart in session like $_SESSION['cart'] = array(product_id1, product_id2)
    if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
        // If session cart is simple array of IDs
        if (in_array($id, $_SESSION['cart'])) {
            $is_in_cart = true;
        }
        // If session cart is associative array (ID => Quantity)
        elseif (array_key_exists($id, $_SESSION['cart'])) {
            $is_in_cart = true;
        }
    }
}

// --------------------------------------------------------
// 4. CART COUNT LOGIC (For Badge)
// --------------------------------------------------------
$cart_count = 0;
if (isset($_SESSION['user_id'])) {
    $uid = $_SESSION['user_id'];
    $c_sql = "SELECT * FROM cart WHERE user_id = '$uid'";
    $c_res = $conn->query($c_sql);
    $cart_count = $c_res->num_rows;
} else {
    if (isset($_SESSION['cart'])) {
        $cart_count = count($_SESSION['cart']);
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($product['title']); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #1a1a1a;
            overflow-x: hidden;
        }

        /* NAVBAR CSS */
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
        }

        @media (max-width: 768px) {
            .navbar-brand {
                font-size: 1.5rem;
            }

            .nav-category-link {
                display: none;
            }
        }

        /* PRODUCT IMAGE CSS */
        .main-image-wrapper {
            position: relative;
            border-radius: 12px;
            overflow: hidden;
        }

        #mainImage {
            width: 100%;
            object-fit: cover;
            border-radius: 12px;
            height: 520px;
            transition: transform 0.4s ease-in-out;
            cursor: zoom-in;
        }

        #mainImage:hover {
            transform: scale(1.1);
        }

        .thumbnail-img {
            cursor: pointer;
            border: 2px solid transparent;
            transition: all 0.2s ease;
            object-fit: cover;
            border-radius: 8px;
        }

        .thumbnail-img:hover,
        .thumbnail-img.active {
            border-color: #ff6b9d;
            opacity: 1;
        }

        @media (max-width: 767px) {
            #mainImage {
                height: 400px;
            }

            .thumbnail-img {
                width: 80px;
                height: 80px;
            }
        }

        @media (min-width: 768px) {
            .thumb-container {
                height: 520px;
                overflow: hidden;
            }

            .thumbnail-img {
                width: 100%;
                height: 122px;
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

    <div class="container-fluid px-5 py-3 d-flex mt-2" style="background-color: #e1e1e1ff;">
        <a class="text-muted text-decoration-none" href="index.php">Home /</a>
        <span class="mx-1">View</span>
    </div>

    <div class="container-fluid mt-4 mb-4 px-lg-5 product-container">
        <div class="row g-4">
            <div class="col-lg-7 col-xl-8">
                <div class="row g-2">
                    <div class="col-12 col-md-2 order-2 order-md-1">
                        <div class="d-flex flex-row flex-md-column gap-2 thumb-container justify-content-center justify-content-md-between">
                            <img class="img-fluid thumbnail-img active" src="<?php echo $product['image']; ?>" onclick="changeImage(this)">
                            <?php
                            $thumbs = ['image1', 'image2', 'image3', 'image4'];
                            $count = 1;
                            foreach ($thumbs as $imgCol) {
                                if ($count >= 4) break;
                                if (!empty($product[$imgCol])) {
                                    echo '<img class="img-fluid thumbnail-img" src="' . $product[$imgCol] . '" onclick="changeImage(this)">';
                                    $count++;
                                }
                            }
                            ?>
                        </div>
                    </div>
                    <div class="col-12 col-md-10 order-1 order-md-2">
                        <div class="main-image-wrapper">
                            <img id="mainImage" src="<?php echo $product['image']; ?>" alt="Main Product">
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-5 col-xl-4 product-details-col">
                <div class="ps-lg-4">
                    <h3 class="fw-bold display-6 mb-3"><?php echo htmlspecialchars($product['title']); ?></h3>

                    <div class="d-flex align-items-center gap-3 mb-4">
                        <h3 class="text-danger fw-bold mb-0">₹<?php echo number_format($product['price']); ?></h3>
                        <h5 class="text-muted text-decoration-line-through mb-0">₹<?php echo number_format($product['price'] * 1.3); ?></h5>
                    </div>

                    <div class="description-box">
                        <p class="badge bg-danger bg-opacity-10 text-danger px-2 py-1 lh-lg">
                            <?php echo htmlspecialchars($product['description']); ?>
                        </p>
                    </div>
                    <hr>

                    <div class="mb-4">
                        <div class="row g-3 align-items-end">
                            <div class="col-8">
                                <p class="fw-bold mb-2">Select Size</p>
                                <div class="btn-group w-100" role="group">
                                    <input type="radio" class="btn-check" name="size" id="sizeS" value="S" autocomplete="off">
                                    <label class="btn btn-outline-dark" for="sizeS">S</label>
                                    <input type="radio" class="btn-check" name="size" id="sizeM" value="M" autocomplete="off">
                                    <label class="btn btn-outline-dark" for="sizeM">M</label>
                                    <input type="radio" class="btn-check" name="size" id="sizeL" value="L" autocomplete="off">
                                    <label class="btn btn-outline-dark" for="sizeL">L</label>
                                    <input type="radio" class="btn-check" name="size" id="sizeXL" value="XL" autocomplete="off">
                                    <label class="btn btn-outline-dark" for="sizeXL">XL</label>
                                </div>
                            </div>

                            <div class="col-4">
                                <p class="fw-bold mb-2">Quantity</p>
                                <div class="input-group">
                                    <button class="btn btn-outline-dark" type="button" onclick="updateQuantity(-1)">-</button>
                                    <input type="text" id="quantityInput" class="form-control text-center border-dark" value="1" style="max-width: 50px;" readonly>
                                    <button class="btn btn-outline-dark" type="button" onclick="updateQuantity(1)">+</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row g-2 mt-4 mb-4">
                        <div class="d-flex gap-3">
                            <button id="addToCartBtn"
                                class="btn <?php echo $is_in_cart ? 'btn-dark' : 'btn-danger'; ?> w-100 py-2 fw-bold"
                                <?php echo $is_in_cart ? 'disabled' : ''; ?>>
                                <?php echo $is_in_cart ? 'Added' : 'Add to Cart'; ?>
                            </button>

                            <button id="addToWishlistBtn"
                                class="btn <?php echo $is_in_wishlist ? 'btn-danger text-white' : 'btn-outline-danger'; ?> w-100 py-2 fw-bold"
                                <?php echo $is_in_wishlist ? 'disabled' : ''; ?>>
                                <?php echo $is_in_wishlist ? 'Added to Wishlist' : 'Wishlist'; ?>
                            </button>
                        </div>
                    </div>
                    <hr class="mt-3">
                    <img class="img-fluid mt-3 rounded" src="https://framerusercontent.com/images/iHezVmmtx1A4VNakbQoH2GQXvA.png?scale-down-to=2048" alt="">
                </div>
            </div>
        </div>
    </div>

    <div class="marquee">
        <div>
            * 20% DISCOUNT ON YOUR FIRST ORDER * NEW SEASON * 20% DISCOUNT ON YOUR FIRST ORDER * NEW SEASON * 20% DISCOUNT ON YOUR FIRST ORDER * NEW SEASON
            * 20% DISCOUNT ON YOUR FIRST ORDER * NEW SEASON * 20% DISCOUNT ON YOUR FIRST ORDER * NEW SEASON * 20% DISCOUNT ON YOUR FIRST ORDER * NEW SEASON
            * 20% DISCOUNT ON YOUR FIRST ORDER * NEW SEASON * 20% DISCOUNT ON YOUR FIRST ORDER * NEW SEASON * 20% DISCOUNT ON YOUR FIRST ORDER * NEW SEASON
            * 20% DISCOUNT ON YOUR FIRST ORDER * NEW SEASON * 20% DISCOUNT ON YOUR FIRST ORDER * NEW SEASON * 20% DISCOUNT ON YOUR FIRST ORDER * NEW SEASON
        </div>
    </div>

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
            btn.classList.remove("btn-dark");
            btn.classList.add("btn-dark");
            btn.disabled = true;
        });
    </script>

    <script>
        // --- 1. IMAGE SWAP ---
        function changeImage(element) {
            var newSrc = element.src;
            document.getElementById('mainImage').src = newSrc;
            var thumbnails = document.querySelectorAll('.thumbnail-img');
            thumbnails.forEach(function(img) {
                img.classList.remove('active');
            });
            element.classList.add('active');
        }

        // --- 2. QUANTITY ---
        function updateQuantity(change) {
            var input = document.getElementById('quantityInput');
            var currentVal = parseInt(input.value);
            var newVal = currentVal + change;
            if (newVal >= 1) {
                input.value = newVal;
            }
        }

        $(document).ready(function() {
            // --- 3. LIVE SEARCH ---
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

            // --- 4. ADD TO CART AJAX ---
            $('#addToCartBtn').click(function() {
                if ($(this).prop('disabled')) return; // Safety check

                var productId = "<?php echo $id; ?>";
                var quantity = $('#quantityInput').val();
                var size = $('input[name="size"]:checked').val();
                var currentImage = $('#mainImage').attr('src');

                if (!size) {
                    alert("Please select a size first!");
                    return;
                }

                var $btn = $(this);
                $btn.text('Adding...').prop('disabled', true);

                $.ajax({
                    url: 'add_to_cart.php',
                    method: 'POST',
                    data: {
                        product_id: productId,
                        quantity: quantity,
                        size: size,
                        image: currentImage
                    },
                    success: function(response) {
                        if (response.trim() == 'success') {
                            $btn.text('Added');
                            $btn.removeClass('btn-danger').addClass('btn-danger');
                            $btn.prop('disabled', true); // Disable button permanently

                            // Update Badge
                            var badge = $("#cart-badge");
                            var currentCount = parseInt(badge.text()) || 0;
                            badge.text(currentCount + 1);
                            badge.show();
                        } else {
                            alert("Response: " + response);
                            $btn.text('Add to Cart').prop('disabled', false);
                        }
                    },
                    error: function() {
                        alert("Error connecting to server.");
                        $btn.text('Add to Cart').prop('disabled', false);
                    }
                });
            });

            // --- 5. ADD TO WISHLIST AJAX ---
            $('#addToWishlistBtn').click(function() {
                if ($(this).prop('disabled')) return; // Safety check

                var productId = "<?php echo $id; ?>";
                var $btn = $(this);

                $.ajax({
                    url: 'add_to_wishlist.php',
                    method: 'POST',
                    data: {
                        product_id: productId
                    },
                    success: function(response) {
                        if (response.trim() == 'success') {
                            $btn.removeClass('btn-outline-danger').addClass('btn-danger text-white');
                            $btn.text('Added to Wishlist');
                            $btn.prop('disabled', true);
                        } else {
                            alert("Something went wrong. (Make sure you are logged in)");
                        }
                    },
                    error: function() {
                        alert("Error connecting to server.");
                    }
                });
            });
        });
    </script>

</body>

</html>