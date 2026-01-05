<?php
session_start();
include 'connection.php'; 
include 'auth_session.php';

// Product ID receive karna
if (isset($_POST['product_id'])) {
    $product_id = intval($_POST['product_id']);

    // CASE 1: Agar User Logged In Hai (Database Use Karein)
    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];

        // Duplicate check
        $check_sql = "SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?";
        $stmt = $conn->prepare($check_sql);
        $stmt->bind_param("ii", $user_id, $product_id);
        $stmt->execute();
        
        if ($stmt->get_result()->num_rows == 0) {
            // Save to DB
            $insert_sql = "INSERT INTO wishlist (user_id, product_id) VALUES (?, ?)";
            $stmt2 = $conn->prepare($insert_sql);
            $stmt2->bind_param("ii", $user_id, $product_id);
            $stmt2->execute();
            $stmt2->close();
        }
        echo "success";
        $stmt->close();

    } 
    // CASE 2: Agar User Logged In NAHI Hai (Session Use Karein)
    else {
        // Agar session array nahi bani hai toh banao
        if (!isset($_SESSION['wishlist_items'])) {
            $_SESSION['wishlist_items'] = array();
        }

        // Agar product pehle se list mein nahi hai, toh add karo
        if (!in_array($product_id, $_SESSION['wishlist_items'])) {
            $_SESSION['wishlist_items'][] = $product_id;
        }
        
        echo "success";
    }
}
?>