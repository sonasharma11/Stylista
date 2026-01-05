<?php
session_start();
include 'connection.php';
include 'auth_session.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Current Page Name
$page = basename($_SERVER['PHP_SELF']);

// Data Fetching
$sql = "SELECT * FROM products";
$result = $conn->query($sql);

// --- HELPER FUNCTION (Stock Logic) ---
// Sold sizes check karne ke liye array pass karenge
function renderSizeGrid($soldSizesArray) {
    $sizes = ['S', 'M', 'L', 'XL'];
    echo '<div class="size-grid">';
    
    foreach ($sizes as $size) {
        // Agar size 'order_items' table mein milta hai, to sold-out class lagao
        $isSold = in_array($size, $soldSizesArray);
        $class = $isSold ? 'sold-out' : ''; 
        
        echo "<div class='size-box $class'>$size</div>";
    }
    echo '</div>';
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Stock Manager - Stylista</title>

    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">

    <style>
        /* --- ORIGINAL CSS START (Aapka purana design) --- */
        :root {
            --primary-pink: #ff6b9d;
            --hover-pink: #ff4785;
            --bg-light: #f5f7fa;
            --sidebar-width: 260px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--bg-light);
            padding-bottom: 80px;
        }

        /* --- SIDEBAR STYLES --- */
        .sidebar {
            width: var(--sidebar-width);
            background: #fff;
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            padding: 2rem 1.5rem;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.02);
            z-index: 1000;
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            overflow-y: auto;
        }

        .sidebar-brand {
            font-size: 1.5rem;
            font-weight: bold;
            color: #333;
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .nav-link {
            padding: 0.9rem 1rem;
            border-radius: 12px;
            color: #666;
            display: flex;
            align-items: center;
            gap: 1rem;
            font-weight: 500;
            margin-bottom: 8px;
            text-decoration: none;
            transition: all 0.2s;
        }

        .nav-link:hover,
        .nav-link.active {
            background: #fff0f5;
            color: var(--primary-pink);
        }

        .nav-link.active {
            background: var(--primary-pink);
            color: #fff;
            box-shadow: 0 4px 15px rgba(255, 107, 157, 0.4);
        }

        /* --- LAYOUT & HEADER --- */
        .main-content {
            margin-left: var(--sidebar-width);
            padding: 2rem;
            min-height: 100vh;
            transition: margin-left 0.3s ease;
        }

        .top-bar {
            position: sticky;
            top: 0;
            background: var(--bg-light);
            z-index: 90;
            padding: 1rem 0;
            margin-top: -2rem;
            margin-bottom: 1rem;
        }

        .hamburger {
            display: none;
            font-size: 1.6rem;
            cursor: pointer;
            border: none;
            background: none;
            color: #333;
        }

        /* --- TABLE STYLES --- */
        .table-container {
            background: white;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            overflow: hidden;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .product-header-row {
            background-color: #2c3e50;
            color: #fff;
            font-size: 1.1em;
            text-align: left;
        }

        .product-header-row td {
            padding: 10px 15px;
        }

        .variant-row {
            border-bottom: 1px solid #eee;
            background: white;
        }

        .variant-row td {
            padding: 12px 15px;
            vertical-align: middle;
        }

        .thumb-img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 4px;
            border: 1px solid #ddd;
        }

        .variant-label {
            font-weight: bold;
            color: #555;
            display: block;
            margin-bottom: 4px;
        }

        .sku-text {
            font-size: 0.8em;
            color: #888;
        }

        .size-grid {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .size-box {
            border: 1px solid #28a745;
            background: #e8f5e9;
            color: #155724;
            padding: 6px 12px;
            border-radius: 4px;
            text-align: center;
            font-size: 0.85em;
            font-weight: bold;
            min-width: 45px;
            flex-grow: 1;
            max-width: 80px;
        }

        /* --- MOBILE RESPONSIVE --- */
        .overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
            opacity: 0;
            transition: opacity 0.3s;
        }

        .overlay.active {
            display: block;
            opacity: 1;
        }

        @media (max-width: 992px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.active {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
                padding: 1rem;
            }

            .hamburger {
                display: block;
            }

            table, thead, tbody, th, td, tr {
                display: block;
                width: 100%;
            }

            thead {
                display: none;
            }

            .product-header-row {
                margin-top: 20px;
                border-radius: 8px 8px 0 0;
                text-align: center;
            }

            .variant-row {
                display: flex;
                flex-wrap: wrap;
                align-items: center;
                padding: 15px;
                border: 1px solid #eee;
                border-top: none;
                margin-bottom: 5px;
            }

            .variant-row td {
                padding: 5px;
                border: none;
            }

            .variant-row td:nth-child(1) {
                width: 80px;
            }

            .variant-row td:nth-child(2) {
                width: calc(100% - 80px);
                padding-left: 15px;
            }

            .variant-row td:nth-child(3) {
                width: 100%;
                margin-top: 10px;
                padding-top: 10px;
                border-top: 1px dashed #ddd;
            }
        }
        /* --- ORIGINAL CSS END --- */

        /* --- SIRF YE NAYA ADD KIYA HAI (SOLD OUT STYLE) --- */
        .size-box.sold-out {
            background-color: #e9ecef !important;
            color: #adb5bd !important;
            border-color: #dee2e6 !important;
            cursor: not-allowed;
            text-decoration: line-through;
        }
    </style>
</head>

<body>

    <?php include 'sidebar.php'; ?>

    <div class="main-content">

        <div class="top-bar d-flex align-items-center justify-content-between mb-4">
            <div class="d-flex align-items-center gap-3">
                <button class="hamburger" onclick="toggleSidebar()">
                    <i class="fas fa-bars"></i>
                </button>
                <h1 class="h4 mb-0 fw-bold">Stock Management</h1>
            </div>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr style="background:#ddd; color:#333; font-size:0.9em;">
                        <th style="padding:10px;">Preview</th>
                        <th>Variation Type</th>
                        <th>Stock (4 Sizes)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            
                            // --- STOCK LOGIC START ---
                            // 1. Current Product ki ID nikalo
                            $currentProductId = $row['id'];
                            
                            // 2. Order_items table se check karo kon se size bik gaye hain
                            $orderSql = "SELECT size FROM order_items WHERE product_id = '$currentProductId'";
                            $orderResult = $conn->query($orderSql);
                            
                            // 3. Jo sizes bik gaye hain unhe list mein dalo
                            $soldSizes = [];
                            if ($orderResult->num_rows > 0) {
                                while($orderRow = $orderResult->fetch_assoc()) {
                                    $soldSizes[] = $orderRow['size'];
                                }
                            }
                            // --- STOCK LOGIC END ---
                    ?>
                            <tr class="product-header-row">
                                <td colspan="3">
                                    <strong>#<?php echo $row['id']; ?> <?php echo $row['title']; ?></strong>
                                    <span style="font-size:0.8em; opacity:0.8; display:block;">(Price: ₹<?php echo $row['price']; ?>)</span>
                                </td>
                            </tr>

                            <tr class="variant-row">
                                <td><img src="<?php echo $row['image']; ?>" class="thumb-img"></td>
                                <td>
                                    <span class="variant-label">Design Variation 1</span>
                                    <span class="sku-text">Product 1</span>
                                </td>
                                <td>
                                    <?php renderSizeGrid($soldSizes); ?>
                                </td>
                            </tr>

                            <?php
                            $subImages = [
                                ['src' => $row['image1'], 'label' => 'Design Variation 2', 'desc' => 'Product 2'],
                                ['src' => $row['image2'], 'label' => 'Design Variation 3', 'desc' => 'Product 3'],
                                ['src' => $row['image3'], 'label' => 'Design Variation 4', 'desc' => 'Product 4'],
                                ['src' => $row['image4'], 'label' => 'Design Variation 5', 'desc' => 'Product 5']
                            ];

                            foreach ($subImages as $img) {
                                if (!empty($img['src'])) {
                            ?>
                                    <tr class="variant-row">
                                        <td><img src="<?php echo $img['src']; ?>" class="thumb-img"></td>
                                        <td>
                                            <span class="variant-label"><?php echo $img['label']; ?></span>
                                            <span class="sku-text"><?php echo $img['desc']; ?></span>
                                        </td>
                                        <td>
                                            <?php renderSizeGrid($soldSizes); ?>
                                        </td>
                                    </tr>
                            <?php
                                }
                            }
                            ?>
                    <?php
                        }
                    } else {
                        echo "<tr><td colspan='3' style='padding:20px; text-align:center;'>No products found</td></tr>";
                    }
                    $conn->close();
                    ?>
                </tbody>
            </table>
        </div>
    </div>
    <div id="overlay" class="overlay" onclick="toggleSidebar()"></div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('active');
            document.getElementById('overlay').classList.toggle('active');
        }
    </script>
</body>

</html>