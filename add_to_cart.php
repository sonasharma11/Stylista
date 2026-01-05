<?php
session_start();
include 'connection.php';
include 'auth_session.php';

// 1. Data Receive Karein
// view_product.php se 'product_id' aa raha hai, par agar kisi aur page se 'id' aaye toh wo bhi handle karein
$product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : (isset($_POST['id']) ? intval($_POST['id']) : null);
$quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;
$size = isset($_POST['size']) ? mysqli_real_escape_string($conn, $_POST['size']) : ''; // Size zaroori hai
$selected_image = isset($_POST['image']) ? mysqli_real_escape_string($conn, $_POST['image']) : '';

// Agar Product ID valid hai tabhi process karein
if ($product_id) {

    // Image Fallback: Agar JS se image nahi aayi toh DB se default utha lo
    if (empty($selected_image)) {
        $img_query = mysqli_query($conn, "SELECT image FROM products WHERE id = '$product_id'");
        if ($row = mysqli_fetch_assoc($img_query)) {
            $selected_image = $row['image'];
        }
    }

    // ---------------------------------------------------
    // SCENARIO 1: LOGGED IN USER (Database me Save Karein)
    // ---------------------------------------------------
    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];

        // 1. Check karein: Kya ye Product + Ye Specific Size cart me pehle se hai?
        // Note: Hum 'size' ko bhi check kar rahe hain.
        $check_sql = "SELECT * FROM cart WHERE user_id = '$user_id' AND product_id = '$product_id' AND size = '$size'";
        $result = mysqli_query($conn, $check_sql);

        if (mysqli_num_rows($result) > 0) {
            // A. Agar same size pehle se hai -> Quantity Update karo
            $update_sql = "UPDATE cart SET quantity = quantity + $quantity WHERE user_id = '$user_id' AND product_id = '$product_id' AND size = '$size'";
            if (mysqli_query($conn, $update_sql)) {
                echo "success";
            } else {
                echo "error_update";
            }
        } else {
            // B. Agar ye size naya hai -> New Row Insert karo
            $insert_sql = "INSERT INTO cart (user_id, product_id, size, quantity, selected_image) 
                           VALUES ('$user_id', '$product_id', '$size', '$quantity', '$selected_image')";
            
            if (mysqli_query($conn, $insert_sql)) {
                echo "success";
            } else {
                // Agar DB me 'size' column nahi hoga toh ye error dega
                echo "error_insert: " . mysqli_error($conn); 
            }
        }
    }
    // ---------------------------------------------------
    // SCENARIO 2: GUEST USER (Session me Save Karein)
    // ---------------------------------------------------
    else {
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = array();
        }

        // Abhi ke liye hum sirf ID store kar rahe hain taaki button "Added" dikhaye.
        // (Guest users ke liye size save karna thoda complex hota hai bina DB ke, 
        // filhal ye login wale flow ko fix karega).
        if (!in_array($product_id, $_SESSION['cart'])) {
            $_SESSION['cart'][] = $product_id;
        }

        echo "success";
    }

} else {
    echo "invalid_product";
}
?>