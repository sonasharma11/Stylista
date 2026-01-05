<?php
include 'connection.php';
include 'auth_session.php';

if (isset($_POST['query'])) {
    $inputText = $_POST['query'];

    // Secure query using Prepared Statements
    $search = "%{$inputText}%";
    $stmt = $conn->prepare("SELECT * FROM products WHERE title LIKE ? OR description LIKE ? LIMIT 5");
    $stmt->bind_param("ss", $search, $search);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo '<div class="list-group">';
        while ($row = $result->fetch_assoc()) {
            echo '
            <a href="view_product.php?id=' . $row["id"] . '" class="list-group-item list-group-item-action d-flex align-items-center gap-3 py-3 border-0 border-bottom">
                <img src="' . $row["image"] . '" alt="product" style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;">
                <div class="w-100">
                    <div class="d-flex w-100 justify-content-between">
                        <h6 class="mb-1" style="font-size: 0.95rem;">' . htmlspecialchars($row["title"]) . '</h6>
                        <small class="fw-bold text-dark">₹ ' . number_format($row["price"]) . '</small>
                    </div>
                </div>
            </a>
            ';
        }
        echo '</div>';
        echo '<a href="see_all.php?search=' . $inputText . '" class="btn btn-dark w-100 mt-3 btn-sm">View All Results</a>';
    } else {
        echo '<div class="text-center py-4 text-muted">
                <i class="bi bi-search fs-1 mb-2 d-block"></i>
                <p>No products found matching "' . htmlspecialchars($inputText) . '"</p>
              </div>';
    }
}
