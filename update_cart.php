<?php
session_start();
include 'connection.php';
include 'auth_session.php';

if (isset($_POST['cart_id']) && isset($_POST['quantity'])) {
    $cart_id = (int)$_POST['cart_id'];
    $quantity = (int)$_POST['quantity'];

    // Quantity कम से कम 1 होनी चाहिए
    if ($quantity < 1) {
        $quantity = 1;
    }

    // Database update query
    // hum cart table ke 'id' column ko use kar rahe hain update ke liye
    $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
    $stmt->bind_param("ii", $quantity, $cart_id);
    
    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "error";
    }
}
?>