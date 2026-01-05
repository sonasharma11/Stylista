<?php
// auth_session.php

// सेशन स्टार्ट करें (अगर पहले से स्टार्ट नहीं है)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// चेक करें कि यूजर लॉग इन है या नहीं
if (!isset($_SESSION['user_id'])) {
    // अगर लॉग इन नहीं है, तो index.php पर भेज दें
    header("Location: index.php");
    exit(); // कोड यहीं रोक दें ताकि आगे का पेज लोड न हो
}
?>