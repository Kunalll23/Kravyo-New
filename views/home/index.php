<!-- Kravyo - Full-Width Edge-to-Edge Production Multi-Portal Gateway & Ecosystem Hub -->
<style>
    :root {
        --kravyo-dark: #121721;
        --kravyo-dark-card: #1a202c;
        --kravyo-coral: #f87154;
        --kravyo-coral-hover: #e8593a;
        --kravyo-amber: #f59e0b;
        --kravyo-amber-hover: #d97706;
        --kravyo-emerald: #10b981;
        --kravyo-emerald-hover: #059669;
        --kravyo-indigo: #353962;
        --kravyo-indigo-hover: #292c4e;
        --kravyo-cream: #fbf8f2;
    }

    /* Reset margins & ensure true full-width display */
    html, body {
        width: 100% !important;
        max-width: 100% !important;
        margin: 0 !important;
        padding: 0 !important;
        overflow-x: hidden !important;
        background-color: var(--kravyo-cream) !important;
        font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
    }

    /* ─── 1. FULL-WIDTH HERO SECTION (DARK CHARCOAL EDGE-TO-EDGE) ─── */
    .hero-edge-to-edge {
        width: 100%;
        background: linear-gradient(135deg, #10151d 0%, #171d28 100%);
        color: #ffffff;
        position: relative;
        padding: 1.25rem 0 3rem;
        border-bottom-left-radius: 36px;
        border-bottom-right-radius: 36px;
        box-shadow: 0 16px 40px rgba(0, 0, 0, 0.12);
        z-index: 10;
    }

    /* Top Navbar */
    .hero-navbar-wrap {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding-bottom: 1.5rem;
        border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        margin-bottom: 2rem;
    }

    .brand-logo {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 1.65rem;
        font-weight: 800;
        color: #ffffff;
        text-decoration: none;
        letter-spacing: -0.5px;
    }
    .brand-logo i {
        color: #f59e0b;
        filter: drop-shadow(0 2px 8px rgba(245, 158, 11, 0.5));
    }

    .nav-links-cluster {
        display: flex;
        align-items: center;
        gap: 2rem;
        list-style: none;
        margin: 0;
        padding: 0;
    }
    .nav-links-cluster a {
        color: #94a3b8;
        text-decoration: none;
        font-size: 0.92rem;
        font-weight: 600;
        transition: color 0.2s ease;
    }
    .nav-links-cluster a:hover,
    .nav-links-cluster a.active {
        color: #ffffff;
    }

    .btn-explore-menu {
        background: #f59e0b;
        color: #0f172a;
        font-weight: 700;
        font-size: 0.9rem;
        padding: 0.6rem 1.45rem;
        border-radius: 999px;
        text-decoration: none;
        box-shadow: 0 4px 14px rgba(245, 158, 11, 0.35);
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
    }
    .btn-explore-menu:hover {
        background: #fbbf24;
        color: #0f172a;
        transform: translateY(-1px);
        box-shadow: 0 6px 18px rgba(245, 158, 11, 0.45);
    }

    /* Hero Body Grid */
    .hero-content-grid {
        display: grid;
        grid-template-columns: 1fr 1.05fr;
        gap: 3rem;
        align-items: center;
    }

    .hero-heading {
        font-size: 3rem;
        font-weight: 800;
        line-height: 1.15;
        color: #f8fafc;
        letter-spacing: -0.8px;
        margin-bottom: 1.15rem;
    }

    .hero-subtext {
        color: #94a3b8;
        font-size: 1rem;
        line-height: 1.6;
        margin-bottom: 1.85rem;
        max-width: 500px;
    }

    /* Food Showcase & Glass Badges */
    .food-showcase-container {
        position: relative;
        display: flex;
        justify-content: flex-end;
        align-items: center;
    }

    .food-platter-img {
        width: 100%;
        max-width: 560px;
        height: auto;
        border-radius: 20px;
        object-fit: cover;
        box-shadow: 0 16px 40px rgba(0, 0, 0, 0.45);
        border: 1px solid rgba(255, 255, 255, 0.1);
        transition: transform 0.4s ease;
    }
    .food-platter-img:hover {
        transform: scale(1.02);
    }

    .glass-badge-top {
        position: absolute;
        top: 20px;
        left: 20px;
        background: rgba(18, 23, 33, 0.82);
        backdrop-filter: blur(14px);
        -webkit-backdrop-filter: blur(14px);
        border: 1px solid rgba(255, 255, 255, 0.2);
        padding: 0.55rem 1.1rem;
        border-radius: 12px;
        color: #ffffff;
        font-size: 0.85rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 0.6rem;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.35);
        animation: floatTop 4s ease-in-out infinite;
    }
    .glass-badge-top i {
        background: #10b981;
        color: #fff;
        width: 22px;
        height: 22px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        font-size: 0.72rem;
    }

    .glass-badge-bottom {
        position: absolute;
        bottom: 25px;
        right: 25px;
        background: rgba(18, 23, 33, 0.82);
        backdrop-filter: blur(14px);
        -webkit-backdrop-filter: blur(14px);
        border: 1px solid rgba(255, 255, 255, 0.2);
        padding: 0.55rem 1.1rem;
        border-radius: 12px;
        color: #ffffff;
        font-size: 0.85rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 0.55rem;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.35);
        animation: floatBottom 4.5s ease-in-out infinite;
    }
    .glass-badge-bottom i {
        color: #f87154;
        font-size: 1rem;
    }

    @keyframes floatTop {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-5px); }
    }
    @keyframes floatBottom {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(5px); }
    }

    /* ─── 2. THE 4 PORTAL CARDS SECTION (FULL-WIDTH CONTAINER) ─── */
    .portal-cards-section {
        width: 100%;
        padding: 3rem 0 3.5rem;
        background-color: var(--kravyo-cream);
        background-image: 
            radial-gradient(circle at 98% 10%, rgba(245, 158, 11, 0.18) 0%, rgba(251, 248, 242, 0) 40%),
            radial-gradient(circle at 2% 90%, rgba(248, 113, 84, 0.12) 0%, rgba(251, 248, 242, 0) 35%);
    }

    .portal-card {
        background: #ffffff;
        border-radius: 20px;
        padding: 2rem 1.5rem 1.5rem;
        border: 1px solid #eef0f3;
        box-shadow: 0 6px 24px rgba(15, 23, 42, 0.04);
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        height: 100%;
        transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
    }
    .portal-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 16px 36px rgba(15, 23, 42, 0.09);
        border-color: #cbd5e1;
    }

    /* Minimalist Circular Icon Badges */
    .portal-icon-badge {
        width: 76px;
        height: 76px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.85rem;
        margin-bottom: 1.25rem;
        transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
    }
    .portal-card:hover .portal-icon-badge {
        transform: scale(1.08);
    }

    .icon-customer { background-color: #ffeae4; color: var(--kravyo-coral); }
    .icon-chef     { background-color: #fef3c7; color: #d97706; }
    .icon-driver   { background-color: #d1fae5; color: #059669; }
    .icon-admin    { background-color: #e2e8f0; color: #334155; }

    .card-title-text {
        font-size: 1.2rem;
        font-weight: 800;
        color: #0f172a;
        margin-bottom: 0.4rem;
        letter-spacing: -0.01em;
    }

    .card-desc-text {
        font-size: 0.85rem;
        color: #64748b;
        line-height: 1.5;
        margin-bottom: 1.5rem;
        min-height: 42px;
    }

    /* Primary Action Buttons */
    .portal-card-actions {
        width: 100%;
        margin-top: auto;
    }

    .btn-action-primary {
        width: 100%;
        border-radius: 12px;
        padding: 0.72rem 1rem;
        font-weight: 700;
        font-size: 0.95rem;
        text-align: center;
        text-decoration: none;
        color: #ffffff;
        border: none;
        display: block;
        transition: all 0.2s ease;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
    }
    .btn-act-coral { background: var(--kravyo-coral); }
    .btn-act-coral:hover { background: var(--kravyo-coral-hover); color: #fff; transform: translateY(-1px); }

    .btn-act-amber { background: var(--kravyo-amber); }
    .btn-act-amber:hover { background: var(--kravyo-amber-hover); color: #fff; transform: translateY(-1px); }

    .btn-act-emerald { background: var(--kravyo-emerald); }
    .btn-act-emerald:hover { background: var(--kravyo-emerald-hover); color: #fff; transform: translateY(-1px); }

    .btn-act-indigo { background: #1e293b; }
    .btn-act-indigo:hover { background: #0f172a; color: #fff; transform: translateY(-1px); }

    /* Minimal Secondary Text Links */
    .portal-card-footer-links {
        margin-top: 0.75rem;
        font-size: 0.82rem;
        color: #94a3b8;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.45rem;
    }
    .portal-card-footer-links a {
        color: #64748b;
        text-decoration: none;
        font-weight: 600;
        transition: color 0.2s ease;
    }
    .portal-card-footer-links a:hover {
        color: #0f172a;
        text-decoration: underline;
    }
    .portal-card-footer-links .dot-sep {
        font-size: 0.75rem;
        color: #cbd5e1;
    }

    /* ─── 3. BOTTOM LIVE STATS BAR ─── */
    .live-stats-strip {
        background: #121721;
        border-radius: 20px;
        padding: 1.25rem 2.25rem;
        margin-top: 2.75rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 1.5rem;
        color: #ffffff;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
    }

    .stats-brand-pill {
        font-weight: 800;
        font-size: 1.15rem;
        color: #ffffff;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .stat-metric-item {
        display: flex;
        align-items: center;
        gap: 0.85rem;
    }
    .stat-metric-icon {
        width: 42px;
        height: 42px;
        border-radius: 12px;
        background: rgba(255, 255, 255, 0.08);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.15rem;
        color: #f59e0b;
    }
    .stat-metric-value {
        font-size: 1.15rem;
        font-weight: 800;
        color: #ffffff;
        line-height: 1.1;
    }
    .stat-metric-label {
        font-size: 0.75rem;
        color: #94a3b8;
        font-weight: 600;
        text-transform: capitalize;
    }

    /* Responsive adjustments */
    @media (max-width: 992px) {
        .hero-edge-to-edge {
            border-bottom-left-radius: 24px;
            border-bottom-right-radius: 24px;
        }
        .hero-content-grid {
            grid-template-columns: 1fr;
            text-align: center;
            gap: 2rem;
        }
        .hero-subtext {
            margin-left: auto;
            margin-right: auto;
        }
        .hero-heading {
            font-size: 2.4rem;
        }
        .hero-navbar-wrap {
            flex-direction: column;
            gap: 1rem;
        }
        .food-showcase-container {
            justify-content: center;
        }
    }
</style>

<!-- ─── 1. FULL-WIDTH HERO SECTION (DARK CHARCOAL CANVAS) ─── -->
<section class="hero-edge-to-edge">
    <div class="container-xl">
        <!-- Top Navbar Header -->
        <nav class="hero-navbar-wrap">
            <a href="<?= url('/') ?>" class="brand-logo">
                <i class="bi bi-fire"></i> Kravyo
            </a>

            <ul class="nav-links-cluster d-none d-lg-flex">
                <li><a href="<?= url('/home') ?>" class="active">Home</a></li>
                <li><a href="<?= url('/about') ?>">About Us</a></li>
                <li><a href="<?= url('/home') ?>">Dishes</a></li>
                <li><a href="<?= url('/about') ?>">Hygiene</a></li>
                <li><a href="<?= url('/contact') ?>">Contact</a></li>
            </ul>

            <a href="<?= url('/home') ?>" class="btn-explore-menu">
                Explore Menu <i class="bi bi-arrow-right-short fs-5"></i>
            </a>
        </nav>

        <!-- Hero Content (Headline + Food Platter) -->
        <div class="hero-content-grid">
            <!-- Left: Headline & Text -->
            <div>
                <h1 class="hero-heading">
                    Indian Home–Cooked<br>Cloud Kitchen Platform
                </h1>
                <p class="hero-subtext">
                    Authentic, hygienic home-cooked meals by passionate homemakers, connected to you through smart hyper-local delivery.
                </p>
                <a href="<?= url('/home') ?>" class="btn-explore-menu px-4 py-2 fs-6">
                    Explore Menu <i class="bi bi-arrow-right"></i>
                </a>
            </div>

            <!-- Right: Food Platter with Floating Glass Badges -->
            <div class="food-showcase-container">
                <img src="<?= asset('images/kravyo_hero_thali.jpg') ?>" alt="Indian Authentic Home Food Platter" class="food-platter-img">

                <!-- Floating Top Badge -->
                <div class="glass-badge-top">
                    <i class="bi bi-check-lg"></i>
                    <span>45+ Verified Home Kitchens</span>
                </div>

                <!-- Floating Bottom Badge -->
                <div class="glass-badge-bottom">
                    <i class="bi bi-geo-alt-fill"></i>
                    <span>Live GPS Tracking</span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ─── 2. THE 4 PORTAL GATEWAY CARDS SECTION (FULL-WIDTH) ─── -->
<section class="portal-cards-section">
    <div class="container-xl">
        <div class="row g-4 justify-content-center">

            <!-- CARD 1: CUSTOMER (CORAL) -->
            <div class="col-sm-6 col-lg-3">
                <div class="portal-card">
                    <div class="portal-icon-badge icon-customer">
                        <i class="bi bi-basket2-fill"></i>
                    </div>
                    <div class="card-title-text">Order Food</div>
                    <div class="card-desc-text">Browse home chefs & authentic homemade meals.</div>

                    <div class="portal-card-actions">
                        <a href="<?= url('/home') ?>" class="btn-action-primary btn-act-coral">
                            Order Food
                        </a>
                        <div class="portal-card-footer-links">
                            <a href="<?= url('/login') ?>">Login</a>
                            <span class="dot-sep">•</span>
                            <a href="<?= url('/register?role=customer') ?>">Sign Up</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- CARD 2: HOME CHEF (AMBER) -->
            <div class="col-sm-6 col-lg-3">
                <div class="portal-card">
                    <div class="portal-icon-badge icon-chef">
                        <i class="bi bi-shop"></i>
                    </div>
                    <div class="card-title-text">Partner Kitchen</div>
                    <div class="card-desc-text">Cook & sell authentic homemade meals from your kitchen.</div>

                    <div class="portal-card-actions">
                        <a href="<?= url('/chef/login') ?>" class="btn-action-primary btn-act-amber">
                            Chef Portal
                        </a>
                        <div class="portal-card-footer-links">
                            <a href="<?= url('/chef/login') ?>">Login</a>
                            <span class="dot-sep">•</span>
                            <a href="<?= url('/register?role=chef') ?>">Register</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- CARD 3: DELIVERY PARTNER (EMERALD GREEN) -->
            <div class="col-sm-6 col-lg-3">
                <div class="portal-card">
                    <div class="portal-icon-badge icon-driver">
                        <i class="bi bi-bicycle"></i>
                    </div>
                    <div class="card-title-text">Delivery Fleet</div>
                    <div class="card-desc-text">Accept delivery offers, track routes and earn flexibly.</div>

                    <div class="portal-card-actions">
                        <a href="<?= url('/delivery/login') ?>" class="btn-action-primary btn-act-emerald">
                            Deliver with Us
                        </a>
                        <div class="portal-card-footer-links">
                            <a href="<?= url('/delivery/login') ?>">Driver Login</a>
                            <span class="dot-sep">•</span>
                            <a href="<?= url('/delivery/register') ?>">Join Fleet</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- CARD 4: ADMINISTRATOR (DEEP INDIGO / NAVY) -->
            <div class="col-sm-6 col-lg-3">
                <div class="portal-card">
                    <div class="portal-icon-badge icon-admin">
                        <i class="bi bi-shield-lock-fill"></i>
                    </div>
                    <div class="card-title-text">Admin Console</div>
                    <div class="card-desc-text">Manage users, approve kitchens & monitor operations.</div>

                    <div class="portal-card-actions">
                        <a href="<?= url('/admin/login') ?>" class="btn-action-primary btn-act-indigo">
                            Admin Console
                        </a>
                        <div class="portal-card-footer-links">
                            <a href="<?= url('/admin/login') ?>">Secure Login</a>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- ─── 3. BOTTOM LIVE STATS BAR ─── -->
        <div class="live-stats-strip">
            <div class="stats-brand-pill">
                <i class="bi bi-activity text-warning"></i> Live Stats
            </div>

            <div class="stat-metric-item">
                <div class="stat-metric-icon" style="color: #f59e0b;">
                    <i class="bi bi-shop"></i>
                </div>
                <div>
                    <div class="stat-metric-value">45+</div>
                    <div class="stat-metric-label">Home Kitchens</div>
                </div>
            </div>

            <div class="stat-metric-item">
                <div class="stat-metric-icon" style="color: #f87154;">
                    <i class="bi bi-people-fill"></i>
                </div>
                <div>
                    <div class="stat-metric-value">1,250+</div>
                    <div class="stat-metric-label">Active Foodies</div>
                </div>
            </div>

            <div class="stat-metric-item">
                <div class="stat-metric-icon" style="color: #10b981;">
                    <i class="bi bi-graph-up-arrow"></i>
                </div>
                <div>
                    <div class="stat-metric-value">99.4%</div>
                    <div class="stat-metric-label">On-Time Deliveries</div>
                </div>
            </div>

            <div class="stat-metric-item">
                <div class="stat-metric-icon" style="color: #38bdf8;">
                    <i class="bi bi-geo-alt-fill"></i>
                </div>
                <div>
                    <div class="stat-metric-value">Google Maps</div>
                    <div class="stat-metric-label">Live GPS Engine</div>
                </div>
            </div>
        </div>
    </div>
</section>
