<div class="sidebar bg-dark text-white p-3" style="min-height: 100vh;">
    <h5 class="fw-bold mb-4">Dashboard</h5>
    <ul class="nav flex-column">
        <?php if (Session::get('user_role') === ROLE_CHEF): ?>
            <li class="nav-item">
                <a class="nav-link text-white" href="<?= url('/chef/dashboard') ?>"><i class="bi bi-speedometer2 me-2"></i> Overview</a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-white" href="<?= url('/chef/menu') ?>"><i class="bi bi-egg-fried me-2"></i> Menu Management</a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-white" href="<?= url('/chef/orders') ?>"><i class="bi bi-bag-check me-2"></i> Orders</a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-white" href="<?= url('/chef/zero-waste') ?>"><i class="bi bi-tag-fill me-2"></i> Zero Waste Deals</a>
            </li>
        <?php elseif (Session::get('user_role') === ROLE_ADMIN): ?>
            <li class="nav-item">
                <a class="nav-link text-white" href="<?= url('/admin/dashboard') ?>"><i class="bi bi-speedometer2 me-2"></i> Overview</a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-white" href="<?= url('/admin/chefs') ?>"><i class="bi bi-shop me-2"></i> Chef Management</a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-white" href="<?= url('/admin/users') ?>"><i class="bi bi-people me-2"></i> User Management</a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-white" href="<?= url('/admin/categories') ?>"><i class="bi bi-tags me-2"></i> Food Categories</a>
            </li>
        <?php endif; ?>
    </ul>
</div>
