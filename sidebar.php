<div class="offcanvas offcanvas-start" tabindex="-1" id="accountSidebar">
    <div class="offcanvas-header" style="background-color: #D70F64; color: white;">
        <div>
            <h5 class="offcanvas-title fw-bold mb-0">
                <?php echo isset($_SESSION['customer_name']) ? $_SESSION['customer_name'] : 'Guest'; ?>
            </h5>
            <small><?php echo isset($_SESSION['customer_phone']) ? $_SESSION['customer_phone'] : ''; ?></small>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body p-0">
        <div class="list-group list-group-flush">
            <a href="index.php" class="list-group-item list-group-item-action py-3 border-0">
                <i class="bi bi-shop me-3 text-secondary"></i> Home / Menu
            </a>
            <a href="cart.php" class="list-group-item list-group-item-action py-3 border-0">
                <i class="bi bi-basket2 me-3 text-secondary"></i> My Cart
            </a>
            <a href="#" class="list-group-item list-group-item-action py-3 border-0">
                <i class="bi bi-geo-alt me-3 text-secondary"></i> Addresses
            </a>
            <hr class="m-0">
            <a href="logout.php" class="list-group-item list-group-item-action py-3 border-0 text-danger fw-bold">
                <i class="bi bi-box-arrow-right me-3"></i> Logout
            </a>
        </div>
    </div>
</div>