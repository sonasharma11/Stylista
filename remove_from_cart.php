<?php
session_start();
include 'connection.php';
include 'auth_session.php';

if (isset($_GET['id']) && isset($_SESSION['user_id'])) {
    $cart_id = $_GET['id'];
    $user_id = $_SESSION['user_id'];

    // Delete specific cart entry for the logged-in user
    $sql = "DELETE FROM cart WHERE id = '$cart_id' AND user_id = '$user_id'";
    
    if (mysqli_query($conn, $sql)) {
        header("Location: cart.php"); // Reload cart page
    } else {
        echo "Error deleting record";
    }
} else {
    header("Location: index.php");
}
?>