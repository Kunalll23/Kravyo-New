<?php
/**
 * Kravyo - Site Footer (Context-Aware)
 *
 * Uses the $_nav* variables computed in header.php so every link stays within
 * the current role's URL prefix and NEVER silently switches the session context.
 *
 * Logo + Quick Links all use context-aware URLs:
 *   Chef    → /chef/dashboard, /chef/kitchens, /chef/menu-browse, /chef/zero-waste-deals
 *   Admin   → /admin/dashboard, /admin/kitchens-browse, /admin/menu-browse, /admin/zero-waste-browse
 *   Delivery→ /delivery/dashboard, /delivery/kitchens, /delivery/menu-browse, /delivery/zero-waste-browse
 *   Customer→ /, /kitchens, /menu, /zero-waste
 */

// $_nav* vars are set by header.php which is always included before footer in the layout.
// Fall back to customer URLs only on layouts that skip the header (e.g. admin_auth).
$_footerHome      = $_navHome      ?? url('/');
$_footerKitchens  = $_navKitchens  ?? url('/kitchens');
$_footerMenu      = $_navMenu      ?? url('/menu');
$_footerZeroWaste = $_navZeroWaste ?? url('/zero-waste');

// Identify the current session context for role-specific middle column
$_footerCtx = Session::currentContext();
?>
<footer>
    <div class="container">
        <div class="row g-4">
            <!-- Brand -->
            <div class="col-lg-3 col-md-6">
                <a href="<?= $_footerHome ?>" class="d-inline-block mb-3 text-decoration-none">
                    <img src="<?= url('/assets/images/kravyo-logo.png') ?>" alt="Kravyo" height="35" style="object-fit: contain; transform: scale(1.8); transform-origin: left center;">
                </a>
                <p class="small text-white-50">
                    Empowering homemakers, home chefs, and small food businesses to sell authentic, hygienic home-cooked meals without physical restaurant investment.
                </p>
            </div>

            <!-- Quick Links — always uses context-aware URLs -->
            <div class="col-6 col-lg-2">
                <h6 class="text-white fw-bold mb-3">Quick Links</h6>
                <ul class="list-unstyled small">
                    <li class="mb-2"><a href="<?= $_footerKitchens ?>">Explore Kitchens</a></li>
                    <li class="mb-2"><a href="<?= $_footerMenu ?>">Dish Catalog</a></li>
                    <li class="mb-2"><a href="<?= $_footerZeroWaste ?>">Zero Waste Section</a></li>
                    <?php if ($_footerCtx === Session::ADMIN_SESSION): ?>
                        <li class="mb-2"><a href="<?= url('/admin/dashboard') ?>" class="text-white-50"><i class="bi bi-shield-lock"></i> Admin Dashboard</a></li>
                    <?php else: ?>
                        <li class="mb-2"><a href="<?= url('/admin/login') ?>" target="_blank" class="text-white-50"><i class="bi bi-shield-lock"></i> Admin Portal</a></li>
                    <?php endif; ?>
                </ul>
            </div>

            <!-- Role-specific portal column -->
            <div class="col-6 col-lg-2">
                <?php if ($_footerCtx === Session::CHEF_SESSION): ?>
                    <h6 class="text-white fw-bold mb-3">Chef Portal</h6>
                    <ul class="list-unstyled small">
                        <li class="mb-2"><a href="<?= url('/chef/dashboard') ?>">My Dashboard</a></li>
                        <li class="mb-2"><a href="<?= url('/chef/menu') ?>">Manage Menu</a></li>
                        <li class="mb-2"><a href="<?= url('/chef/orders') ?>">My Orders</a></li>
                    </ul>
                <?php elseif ($_footerCtx === Session::DELIVERY_SESSION): ?>
                    <h6 class="text-white fw-bold mb-3">Delivery Portal</h6>
                    <ul class="list-unstyled small">
                        <li class="mb-2"><a href="<?= url('/delivery/dashboard') ?>">My Dashboard</a></li>
                        <li class="mb-2"><a href="<?= url('/delivery/history') ?>">Delivery History</a></li>
                    </ul>
                <?php elseif ($_footerCtx === Session::ADMIN_SESSION): ?>
                    <h6 class="text-white fw-bold mb-3">Admin Portal</h6>
                    <ul class="list-unstyled small">
                        <li class="mb-2"><a href="<?= url('/admin/dashboard') ?>">Dashboard</a></li>
                        <li class="mb-2"><a href="<?= url('/admin/orders') ?>">Manage Orders</a></li>
                        <li class="mb-2"><a href="<?= url('/admin/chefs') ?>">Manage Chefs</a></li>
                    </ul>
                <?php elseif ($_footerCtx === Session::PUBLIC_SESSION): ?>
                    <h6 class="text-white fw-bold mb-3">Portals</h6>
                    <ul class="list-unstyled small">
                        <li class="mb-2"><a href="<?= url('/login') ?>">Customer Login</a></li>
                        <li class="mb-2"><a href="<?= url('/chef/login') ?>">Chef Login</a></li>
                        <li class="mb-2"><a href="<?= url('/delivery/login') ?>">Delivery Login</a></li>
                    </ul>
                <?php else: // Customer ?>
                    <h6 class="text-white fw-bold mb-3">For Home Chefs</h6>
                    <ul class="list-unstyled small">
                        <li class="mb-2"><a href="<?= url('/register') ?>">Partner with Us</a></li>
                        <li class="mb-2"><a href="<?= url('/chef/login') ?>" target="_blank">Chef Portal Login</a></li>
                        <li class="mb-2"><a href="<?= url('/about') ?>">About Kravyo</a></li>
                    </ul>
                <?php endif; ?>
            </div>

            <!-- Column 4: Role-specific tools or Delivery Partners -->
            <div class="col-6 col-lg-2">
                <?php if ($_footerCtx === Session::CHEF_SESSION): ?>
                    <h6 class="text-white fw-bold mb-3">Kitchen Tools</h6>
                    <ul class="list-unstyled small">
                        <li class="mb-2"><a href="<?= url('/chef/profile') ?>">Edit Profile</a></li>
                        <li class="mb-2"><a href="<?= url('/chef/subscriptions') ?>">Tiffin Plans</a></li>
                        <li class="mb-2"><a href="<?= url('/chef/zero-waste') ?>">Zero Waste Deals</a></li>
                    </ul>
                <?php elseif ($_footerCtx === Session::DELIVERY_SESSION): ?>
                    <h6 class="text-white fw-bold mb-3">Rider Portal</h6>
                    <ul class="list-unstyled small">
                        <li class="mb-2"><a href="<?= url('/delivery/dashboard') ?>">Active Deliveries</a></li>
                        <li class="mb-2"><a href="<?= url('/delivery/history') ?>">Past Deliveries</a></li>
                    </ul>
                <?php elseif ($_footerCtx === Session::ADMIN_SESSION): ?>
                    <h6 class="text-white fw-bold mb-3">Administration</h6>
                    <ul class="list-unstyled small">
                        <li class="mb-2"><a href="<?= url('/admin/users') ?>">Manage Users</a></li>
                        <li class="mb-2"><a href="<?= url('/admin/categories') ?>">Categories</a></li>
                        <li class="mb-2"><a href="<?= url('/admin/reports') ?>">Reports</a></li>
                    </ul>
                <?php elseif ($_footerCtx === Session::PUBLIC_SESSION): ?>
                    <h6 class="text-white fw-bold mb-3">Delivery Partners</h6>
                    <ul class="list-unstyled small">
                        <li class="mb-2"><a href="<?= url('/delivery/register') ?>">Join as Rider</a></li>
                        <li class="mb-2"><a href="<?= url('/delivery/login') ?>">Delivery Portal Login</a></li>
                    </ul>
                <?php else: // Customer ?>
                    <h6 class="text-white fw-bold mb-3">Delivery Partners</h6>
                    <ul class="list-unstyled small">
                        <li class="mb-2"><a href="<?= url('/delivery/register') ?>" target="_blank">Join as Rider</a></li>
                        <li class="mb-2"><a href="<?= url('/delivery/login') ?>" target="_blank">Delivery Portal</a></li>
                    </ul>
                <?php endif; ?>
            </div>

            <!-- Column 5: Queries & Contact -->
            <div class="col-6 col-lg-3">
                <h6 class="text-white fw-bold mb-3">
                    <i class="bi bi-headset text-warning me-2"></i>Queries &amp; Contact
                </h6>
                <ul class="list-unstyled small text-white-50 mb-0">
                    <li class="mb-2 d-flex align-items-start">
                        <i class="bi bi-telephone-fill text-success me-2 mt-1"></i>
                        <div>
                            <span class="d-block text-white-50" style="font-size:0.75rem;">Support Helpline</span>
                            <a href="tel:+919876543210" class="text-white fw-semibold text-decoration-none">+91 98765 43210</a>
                        </div>
                    </li>
                    <li class="mb-2 d-flex align-items-start">
                        <i class="bi bi-envelope-fill text-warning me-2 mt-1"></i>
                        <div>
                            <span class="d-block text-white-50" style="font-size:0.75rem;">Email Us</span>
                            <a href="mailto:support@kravyo.com" class="text-white fw-semibold text-decoration-none">support@kravyo.com</a>
                        </div>
                    </li>
                    <li class="mb-2 d-flex align-items-start">
                        <i class="bi bi-geo-alt-fill text-danger me-2 mt-1"></i>
                        <div>
                            <span class="text-white-50">Surat, Gujarat, India</span>
                        </div>
                    </li>
                    <li class="mt-3">
                        <a href="<?= url('/contact') ?>" class="btn btn-sm btn-outline-warning text-white px-3 py-1 rounded-pill fw-semibold" style="font-size:0.75rem; border-color: rgba(255,107,53,0.5);">
                            <i class="bi bi-chat-dots-fill text-warning me-1"></i> Have a Query? Contact Us
                        </a>
                    </li>
                </ul>
            </div>
        </div>
        <hr class="my-4 border-secondary">
        <div class="text-center small">
            <p class="mb-0 text-white-50">&copy; <?= date('Y') ?> Kravyo Platform. Built for Homemakers &amp; Small Food Businesses.</p>
        </div>
    </div>
</footer>
