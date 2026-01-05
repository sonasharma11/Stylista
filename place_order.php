<?php
session_start();
include 'connection.php';
include 'auth_session.php';

// 1. Load PHPMailer Dependencies
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

// Check if form is submitted and user is logged in
if (isset($_POST['name']) && isset($_SESSION['user_id'])) {

    $user_id = $_SESSION['user_id'];

    // 2. Get Form Data & Sanitize
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $contact = mysqli_real_escape_string($conn, $_POST['contact']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $pincode = mysqli_real_escape_string($conn, $_POST['pincode']);
    $total_amount = $_POST['total_amount'];
    $payment_method = $_POST['payment_method'];

    // Optional: Get UPI ID if it exists
    $upi_id = isset($_POST['upi_id']) ? mysqli_real_escape_string($conn, $_POST['upi_id']) : '';

    // 3. Insert into 'orders' table
    $order_query = "INSERT INTO orders (user_id, name, email, contact, address, pincode, total_amount, payment_method, upi_id) 
                    VALUES ('$user_id', '$name', '$email', '$contact', '$address', '$pincode', '$total_amount', '$payment_method', '$upi_id')";

    if (mysqli_query($conn, $order_query)) {
        // Get the ID of the order just created
        $order_id = mysqli_insert_id($conn);

        // 4. Move items from 'cart' to 'order_items'
        $cart_sql = "SELECT * FROM cart WHERE user_id = '$user_id'";
        $cart_result = mysqli_query($conn, $cart_sql);

        while ($row = mysqli_fetch_assoc($cart_result)) {
            $p_id = $row['product_id'];
            $qty = $row['quantity'];
            
            // --- NEW: Cart se Size fetch karein ---
            // (Make sure cart table me size column hai)
            $size = isset($row['size']) ? $row['size'] : 'Standard'; 

            // Get current price of product
            $price_sql = "SELECT price FROM products WHERE id = '$p_id'";
            $price_res = mysqli_query($conn, $price_sql);
            $price_row = mysqli_fetch_assoc($price_res);
            $price = $price_row['price'];

            // --- UPDATED: Insert into order_items with SIZE ---
            $item_query = "INSERT INTO order_items (order_id, product_id, quantity, price, size) 
                           VALUES ('$order_id', '$p_id', '$qty', '$price', '$size')";
            mysqli_query($conn, $item_query);
        }

        // 5. Empty the Cart
        $clear_cart = "DELETE FROM cart WHERE user_id = '$user_id'";
        mysqli_query($conn, $clear_cart);

        // ---------------------------------------------------------
        // 6. SEND EMAIL LOGIC
        // ---------------------------------------------------------
        $mail = new PHPMailer(true);

        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'sonasharma42003@gmail.com'; // Your Gmail
            $mail->Password   = 'jcyr spis albp vows';       // Your App Password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            // Recipients
            $mail->setFrom('sonasharma42003@gmail.com', 'STYLISTA');
            $mail->addAddress($email, $name); 

            // Content
            $mail->isHTML(true);
            $mail->Subject = "Order Confirmed - Order #$order_id";

            $mail->Body = "
                <div style='font-family: Arial, sans-serif; border: 1px solid #ddd; padding: 20px; max-width: 600px;'>
                    <h2 style='color: #000;'>Order Confirmed!</h2>
                    <p>Hi $name,</p>
                    <p>Thank you for shopping with STYLISTA. Your order (ID: #$order_id) has been successfully placed.</p>
                    
                    <div style='background-color: #f9f9f9; padding: 15px; margin: 20px 0;'>
                        <p style='margin:0;'><strong>Total Amount:</strong> ₹$total_amount</p>
                        <p style='margin:0;'><strong>Payment Method:</strong> $payment_method</p>
                    </div>

                    <p style='color: green; font-size: 18px; font-weight: bold;'>
                        Your order will be delivered within 7 days.
                    </p>

                    <p>Shipping Address:<br> $address, $pincode</p>
                    <hr>
                    <small>Need help? Reply to this email.</small>
                </div>
            ";

            $mail->send();
        } catch (Exception $e) {
            // Email error ignored for user experience flow
        }
        // ---------------------------------------------------------

        // 7. Redirect to Success Page
        header("Location: order_success.php?orderid=" . $order_id);
        exit();
    } else {
        echo "Error: " . mysqli_error($conn);
    }
} else {
    // If accessed directly without form submission
    header("Location: index.php");
    exit();
}
?>