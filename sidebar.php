<?php $page = basename($_SERVER['PHP_SELF']); ?>

<div class="overlay" id="overlay" onclick="toggleSidebar()"></div>

<div class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <i class="fas fa-gem" style="color: var(--primary-pink);"></i> STYLISTA
    </div>

    <h6 class="text-uppercase text-muted fw-bold mb-3"
        style="font-size: 0.75rem; letter-spacing: 1px;">
        Admin Menu
    </h6>

    <nav class="nav flex-column">
        <a href="dashboard.php"
            class="nav-link <?= ($page == 'dashboard.php') ? 'active' : '' ?>">
            <i class="fas fa-chart-line"></i> Dashboard
        </a>

        <a href="inventory.php"
            class="nav-link <?= ($page == 'inventory.php') ? 'active' : '' ?>">
            <i class="fas fa-dolly"></i> All Products
        </a>

        <a href="create.php"
            class="nav-link <?= ($page == 'create.php') ? 'active' : '' ?>">
            <i class="fas fa-plus-circle"></i> Add Product
        </a>

        <a href="sliders.php"
            class="nav-link <?= ($page == 'sliders.php') ? 'active' : '' ?>">
            <i class="fas fa-panorama"></i> Sliders
        </a>

         <a href="stocks.php"
            class="nav-link <?= ($page == 'stocks.php') ? 'active' : '' ?>">
            <i class="fas fa-warehouse"></i> Stocks
        </a>

        <a href="ordersfromuser.php"
            class="nav-link <?= ($page == 'ordersfromuser.php') ? 'active' : '' ?>">
            <i class="fas fa-box"></i> Orders
        </a>

        <a href="bill.php"
            class="nav-link <?= ($page == 'bill.php') ? 'active' : '' ?>">
            <i class="fas fa-file-invoice"></i> Total Bill
        </a>

        <a href="profile.php"
            class="nav-link <?= ($page == 'profile.php') ? 'active' : '' ?>">
           <i class="fas fa-sign-out-alt"></i> Log out
        </a>
    </nav>
</div>