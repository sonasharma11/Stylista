<?php
session_start();
include 'connection.php';
include 'auth_session.php';

// --- 1. AUTHORIZATION ---
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// --- 2. FETCH USER DATA ---
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_data = $stmt->get_result()->fetch_assoc();
$stmt->close();

$img_src = !empty($user_data['profile_image']) ? $user_data['profile_image'] : "https://ui-avatars.com/api/?name=" . urlencode($user_data['name']) . "&background=212529&color=fff";

// --- 3. FETCH CART ITEMS ---
$cart_items = array();
$total_price = 0;

$sql = "SELECT c.id as cart_id, c.quantity, c.selected_image, c.size, 
               p.id as product_id, p.title, p.price, p.image as default_image 
        FROM cart c 
        JOIN products p ON c.product_id = p.id 
        WHERE c.user_id = '$user_id'";

$result = mysqli_query($conn, $sql);
$item_count = mysqli_num_rows($result);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Cart | STYLISTA</title>

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
            margin-bottom: 20px;
            /* Space below sidebar on mobile */
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

        /* --- CART DESKTOP STYLES --- */
        .cart-card {
            background: white;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .cart-img {
            width: 70px;
            height: 90px;
            object-fit: cover;
            border-radius: 4px;
            border: 1px solid #eee;
        }

        .table-cart th {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #6c757d;
            border-bottom: 2px solid #eee;
            padding-bottom: 15px;
        }

        .qty-input {
            width: 50px;
            text-align: center;
            border: 1px solid #ddd;
            padding: 5px;
            border-radius: 4px;
            font-size: 0.9rem;
        }

        .btn-delete {
            color: #999;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: 0.2s;
            background: #f8f9fa;
        }

        .btn-delete:hover {
            background: #fee2e2;
            color: #dc3545;
        }

        .summary-box {
            background-color: #fdfdfd;
            padding: 20px;
            border-radius: 8px;
            border: 1px solid var(--border-color);
        }

        .checkout-btn {
            background-color: var(--primary-black);
            color: #fff;
            padding: 12px;
            width: 100%;
            text-transform: uppercase;
            font-weight: 600;
            letter-spacing: 1px;
            border: none;
            border-radius: 4px;
            transition: 0.3s;
        }

        .checkout-btn:hover {
            background-color: var(--primary-gold);
            color: #fff;
        }

        .cod-box {
            border: 1px solid #eee;
            padding: 15px;
            border-radius: 6px;
            cursor: pointer;
            display: flex;
            align-items: center;
            transition: 0.2s;
        }

        .cod-box:hover {
            background: #f9f9f9;
        }

        .cod-box.selected {
            border: 2px solid var(--primary-black);
            background-color: #f0f0f0;
        }

        /* --- MOBILE RESPONSIVE FIXES (NO SIDE SLIDE) --- */
        @media (max-width: 991px) {
            .table-cart thead {
                display: none;
            }

            /* Hide Table Headers */

            .table-cart tbody,
            .table-cart tr,
            .table-cart td {
                display: block;
                width: 100%;
            }

            .table-cart tr {
                margin-bottom: 20px;
                border: 1px solid #eee;
                border-radius: 10px;
                background: #fff;
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.03);
                padding: 10px;
            }

            .table-cart td {
                display: flex;
                justify-content: space-between;
                align-items: center;
                text-align: right;
                padding: 10px 5px;
                border-bottom: 1px dashed #eee;
            }

            .table-cart td:last-child {
                border-bottom: none;
            }

            /* Add Labels via CSS */
            .table-cart td::before {
                content: attr(data-label);
                font-weight: 700;
                font-size: 0.8rem;
                text-transform: uppercase;
                color: #888;
            }

            /* Fix Product Image Row */
            .table-cart td:first-child {
                border-bottom: 1px solid #eee;
                background-color: #fdfdfd;
                justify-content: flex-start;
                margin-bottom: 5px;
            }

            .table-cart td:first-child::before {
                display: none;
            }

            .cart-img {
                width: 60px;
                height: 80px;
                margin-right: 15px !important;
            }
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
                        <a href="profile.php" class="nav-link-custom"><i class="bi bi-person"></i> My Profile</a>
                        <a href="cart.php" class="nav-link-custom active"><i class="bi bi-bag"></i> Cart</a>
                        <a href="my_orders.php" class="nav-link-custom"><i class="bi bi-box-seam"></i> Orders</a>
                        <a href="wishlist.php" class="nav-link-custom"><i class="bi bi-heart"></i> Wishlist</a>
                        <div class="border-top my-2"></div>
                        <a href="logout.php" class="nav-link-custom text-danger"><i class="bi bi-box-arrow-right"></i> Log Out</a>
                    </div>
                </div>
            </div>

            <div class="col-lg-9">
                <h4 class="mb-4 fw-bold" style="font-family: var(--font-heading);">Shopping Cart</h4>

                <?php if ($item_count > 0): ?>
                    <div class="row g-4">
                        <div class="col-xl-8">
                            <div class="cart-card">
                                <div class="table-responsive-none">
                                    <table class="table table-cart align-middle mb-0">
                                        <thead>
                                            <tr>
                                                <th width="45%">Product</th>
                                                <th>Price</th>
                                                <th>Qty</th>
                                                <th>Total</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while ($row = mysqli_fetch_assoc($result)):
                                                $item_total = $row['price'] * $row['quantity'];
                                                $total_price += $item_total;
                                            ?>
                                                <tr>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <a href="view_product.php?id=<?php echo $row['product_id']; ?>">
                                                                <?php $display_image = !empty($row['selected_image']) ? $row['selected_image'] : $row['default_image']; ?>
                                                                <img src="<?php echo $display_image; ?>" class="cart-img me-3" alt="img">
                                                            </a>
                                                            <div>
                                                                <a href="view_product.php?id=<?php echo $row['product_id']; ?>" class="text-decoration-none text-dark fw-bold small text-break">
                                                                    <?php echo $row['title']; ?>
                                                                </a>
                                                                <?php if (!empty($row['size'])): ?>
                                                                    <div class="text-muted mt-1" style="font-size: 0.75rem;">Size: <?php echo $row['size']; ?></div>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>
                                                    </td>

                                                    <td data-label="Price">
                                                        <span class="small fw-bold">₹<?php echo number_format($row['price']); ?></span>
                                                    </td>

                                                    <td data-label="Quantity">
                                                        <input type="number" class="qty-input" data-cart-id="<?php echo $row['cart_id']; ?>" value="<?php echo $row['quantity']; ?>" min="1" onchange="updateCart(this)">
                                                    </td>

                                                    <td data-label="Total" class="fw-bold text-dark">
                                                        ₹<?php echo number_format($item_total); ?>
                                                    </td>

                                                    <td data-label="Action">
                                                        <a href="remove_from_cart.php?id=<?php echo $row['cart_id']; ?>" class="btn-delete ms-auto ms-lg-0" onclick="return confirm('Remove this item?');">
                                                            <i class="bi bi-trash"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-4">
                            <div class="cart-card summary-box sticky-top" style="top: 100px; z-index: 1;">
                                <h6 class="text-uppercase fw-bold mb-4" style="font-size: 0.85rem; letter-spacing: 1px;">Order Summary</h6>

                                <div class="d-flex justify-content-between mb-2 small">
                                    <span class="text-muted">Subtotal</span>
                                    <span class="fw-bold">₹<?php echo number_format($total_price); ?></span>
                                </div>
                                <div class="d-flex justify-content-between mb-3 small">
                                    <span class="text-muted">Shipping</span>
                                    <span class="text-success fw-bold">Free</span>
                                </div>
                                <hr class="text-muted">
                                <div class="d-flex justify-content-between mb-4">
                                    <span class="fw-bold">Total</span>
                                    <span class="fw-bold fs-5">₹<?php echo number_format($total_price); ?></span>
                                </div>

                                <button type="button" class="checkout-btn" data-bs-toggle="modal" data-bs-target="#checkoutModal">
                                    Place Order
                                </button>

                                <div class="mt-3 text-center">
                                    <span class="small text-muted"><i class="bi bi-shield-check me-1"></i> Secure Checkout</span>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="cart-card text-center py-5">
                        <div class="mb-3 text-muted" style="font-size: 3rem;">
                            <i class="bi bi-bag"></i>
                        </div>
                        <h5 class="fw-bold">Your cart is empty</h5>
                        <p class="text-muted mb-4">Looks like you haven't added anything yet.</p>
                        <a href="index.php" class="btn btn-dark px-4 py-2">Continue Shopping</a>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </div>

    <div class="modal fade" id="checkoutModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0">
                <div class="modal-header border-bottom-0">
                    <h5 class="modal-title fw-bold" style="font-family: var(--font-heading);">Shipping Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body pt-0">
                    <form id="orderForm" action="place_order.php" method="POST">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Full Name</label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Email</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Phone</label>
                                <input type="tel" class="form-control" id="contact" name="contact" required pattern="[0-9]{10}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Pincode</label>
                                <input type="text" class="form-control" id="pincode" name="pincode" required pattern="[0-9]{6}">
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-bold">Address</label>
                                <textarea class="form-control" id="address" name="address" rows="2" required></textarea>
                            </div>
                        </div>

                        <input type="hidden" name="total_amount" value="<?php echo $total_price; ?>">
                        <input type="hidden" name="payment_method" id="selected_payment_method" value="">

                        <div class="d-grid mt-4">
                            <button type="button" class="btn btn-dark py-2" onclick="validateAndProceed()">Proceed to Payment</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="paymentModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0">
                <div class="modal-header border-bottom-0">
                    <h5 class="modal-title fw-bold" style="font-family: var(--font-heading);">Select Payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body pt-0">
                    <div class="bg-light p-3 rounded text-center mb-4">
                        <small class="text-uppercase text-muted fw-bold" style="font-size: 0.7rem;">Total Payable</small>
                        <h4 class="mb-0 fw-bold">₹<?php echo number_format($total_price); ?></h4>
                    </div>

                    <h6 class="text-muted text-uppercase small fw-bold mb-3">Pay via UPI</h6>
                    <div class="text-center mb-4 p-3 border rounded bg-white">
                        <img class="img-fluid" style="max-height: 180px;" src="image/upi copy.jpg" alt="UPI QR Code">
                        <div class="small text-muted mt-2">Scan with any UPI App</div>
                    </div>

                    <h6 class="text-muted text-uppercase small fw-bold mb-3">Or Pay Later</h6>
                    <div class="cod-box mb-4" onclick="selectPaymentOption('COD', this)">
                        <i class="bi bi-cash-coin fs-3 me-3 text-success"></i>
                        <div>
                            <strong class="d-block text-dark">Cash on Delivery</strong>
                            <small class="text-muted" style="font-size: 0.8rem;">Pay via Cash/UPI at doorstep</small>
                        </div>
                    </div>

                    <div class="d-grid">
                        <button type="button" class="btn btn-dark btn-lg" onclick="finalSubmit()">Place Order</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        function validateAndProceed() {
            var name = document.getElementById('name').value;
            var email = document.getElementById('email').value;
            var contact = document.getElementById('contact').value;
            var pincode = document.getElementById('pincode').value;
            var address = document.getElementById('address').value;

            if (name === "" || email === "" || contact === "" || pincode === "" || address === "") {
                alert("Please fill all address details first.");
                return;
            }
            bootstrap.Modal.getInstance(document.getElementById('checkoutModal')).hide();
            new bootstrap.Modal(document.getElementById('paymentModal')).show();
        }

        function selectPaymentOption(methodValue, element) {
            var allBoxes = document.querySelectorAll('.cod-box');
            allBoxes.forEach(box => box.classList.remove('selected'));
            element.classList.add('selected');
            document.getElementById('selected_payment_method').value = methodValue;
        }

        function finalSubmit() {
            var paymentMethod = document.getElementById('selected_payment_method').value;
            if (paymentMethod === "") {
                alert("Please select a payment method.");
                return;
            }
            document.getElementById('orderForm').submit();
        }

        function updateCart(inputElement) {
            var cartId = $(inputElement).data('cart-id');
            var newQuantity = $(inputElement).val();

            $.ajax({
                url: 'update_cart.php',
                method: 'POST',
                data: {
                    cart_id: cartId,
                    quantity: newQuantity
                },
                success: function(response) {
                    if (response.trim() === 'success') {
                        location.reload();
                    } else {
                        alert('Error updating quantity.');
                    }
                }
            });
        }
    </script>

</body>

</html>