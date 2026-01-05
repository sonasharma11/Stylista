<?php
if (isset($_GET['status']) && $_GET['status'] == 'loggedout') {
    echo "<p style='color: green; text-align: center;'>Successfully Logged Out!</p>";
}
?>

<?php
session_start();
$_SESSION = [];
session_destroy();

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
}

// Redirect with a success parameter
header("Location: index.php?status=loggedout");
exit();
?>