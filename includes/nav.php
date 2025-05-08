<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
    <div class="container">
        <a class="navbar-brand" href="#"><i class="bi bi-building"></i> نظام الشكاوى</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page === 'index.php' ? 'active' : ''; ?>" href="/citizen complaints/index.php"><i class="bi bi-house-fill"></i> الرئيسية</a>
                </li>
                <?php if(isset($_SESSION['admin_id'])): ?>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_page === 'dashboard.php' ? 'active' : ''; ?>" href="/citizen complaints/admin/dashboard.php"><i class="bi bi-speedometer2"></i> لوحة التحكم</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/citizen complaints/admin/logout.php"><i class="bi bi-box-arrow-right"></i> تسجيل الخروج</a>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_page === 'login.php' ? 'active' : ''; ?>" href="/citizen complaints/admin/login.php"><i class="bi bi-person-fill"></i> تسجيل الدخول</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>