<?php
// Prepare dynamic lists if not set
$featuredKitchens = $featuredKitchens ?? [];
$topSellingDishes = $topSellingDishes ?? [];
$recommendedDishes = $recommendedDishes ?? [];
$customerCity = $customerCity ?? 'Surat';
?>
<style>
/* Hero Section */
.hero-section {
    position: relative;
    background: url('<?= url('/assets/images/hero-bg.jpg') ?>') no-repeat center center/cover;
    height: 480px;
    display: flex;
    align-items: center;
}
.hero-overlay {
    position: absolute;
    top: 0; left: 0; right: 0; bottom: 0;
    background: linear-gradient(to right, rgba(0,0,0,0.85) 20%, rgba(0,0,0,0.4) 100%);
}
.search-wrapper {
    position: relative;
    max-width: 800px;
    width: 100%;
}
.search-container {
    background: white;
    border-radius: 50px;
    padding: 6px;
    display: flex;
    align-items: center;
    width: 100%;
}
.search-input {
    border: none;
    outline: none;
    box-shadow: none;
    flex-grow: 1;
    padding: 10px 15px;
    color: #212529;
}
.btn-search {
    background-color: #ff6b6b;
    color: white;
    border-radius: 50px;
    padding: 10px 30px;
    font-weight: bold;
    border: none;
}
/* Live Dish Suggestions Dropdown */
.dish-suggestions-dropdown {
    position: absolute;
    top: calc(100% + 8px);
    left: 0;
    right: 0;
    background: #ffffff;
    border-radius: 16px;
    box-shadow: 0 16px 40px rgba(0, 0, 0, 0.25);
    border: 1px solid rgba(0, 0, 0, 0.08);
    overflow: hidden;
    z-index: 1050;
    color: #212529;
    max-height: 420px;
    overflow-y: auto;
}
.dish-suggestion-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 16px;
    text-decoration: none;
    color: #212529;
    border-bottom: 1px solid #f2f3f5;
    transition: background-color 0.15s ease;
    cursor: pointer;
}
.dish-suggestion-item:last-child {
    border-bottom: none;
}
.dish-suggestion-item:hover,
.dish-suggestion-item.active {
    background-color: #fff5f5;
    color: #212529;
}
.dish-sugg-img {
    width: 44px;
    height: 44px;
    border-radius: 10px;
    object-fit: cover;
    flex-shrink: 0;
    background-color: #f8f9fa;
}
.dish-sugg-info {
    flex-grow: 1;
    min-width: 0;
}
.dish-sugg-name {
    font-weight: 700;
    font-size: 0.95rem;
    margin-bottom: 2px;
    display: flex;
    align-items: center;
    gap: 6px;
    color: #212529;
}
.dish-sugg-meta {
    font-size: 0.78rem;
    color: #6c757d;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.dish-sugg-price {
    font-weight: 800;
    font-size: 0.95rem;
    color: #ff6b6b;
    flex-shrink: 0;
}
.dish-sugg-footer {
    padding: 10px 16px;
    background-color: #fdfdfe;
    border-top: 1px solid #eee;
    text-align: center;
    font-size: 0.82rem;
    font-weight: 600;
}
.dish-sugg-footer a {
    color: #ff6b6b;
    text-decoration: none;
}
.dish-sugg-footer a:hover {
    text-decoration: underline;
}
.dish-sugg-empty {
    padding: 22px 16px;
    text-align: center;
    color: #6c757d;
    font-size: 0.9rem;
}
/* Category Circles */
.category-scroller {
    display: flex;
    overflow-x: auto;
    gap: 1.5rem;
    padding: 2rem 1rem;
    scrollbar-width: none; /* Firefox */
}
.category-scroller::-webkit-scrollbar {
    display: none; /* Chrome/Safari */
}
.category-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-decoration: none;
    color: #333;
    min-width: 82px;
    max-width: 100px;
    text-align: center;
    transition: transform 0.2s;
    flex-shrink: 0;
}
.category-item:hover {
    transform: translateY(-3px) scale(1.04);
}
.category-circle {
    width: 70px;
    height: 70px;
    border-radius: 50%;
    background: #f8f9fa;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 10px rgba(0,0,0,0.06);
    margin-bottom: 8px;
    overflow: hidden;
    border: 2px solid #ffffff;
    transition: box-shadow 0.2s;
}
.category-item:hover .category-circle {
    box-shadow: 0 6px 14px rgba(0,0,0,0.12);
}
.category-circle img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.category-name-text {
    font-size: 0.8rem;
    font-weight: 600;
    color: #212529;
    line-height: 1.25;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    text-align: center;
    word-break: break-word;
}
/* Cards */
.kitchen-card {
    border: none;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    transition: box-shadow 0.2s;
}
.kitchen-card:hover {
    box-shadow: 0 8px 24px rgba(0,0,0,0.1);
}
.kitchen-img-wrapper {
    position: relative;
    height: 180px;
}
.kitchen-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.badge-open {
    position: absolute;
    top: 10px;
    left: 10px;
    background: #198754;
    color: white;
    font-size: 0.75rem;
    padding: 4px 8px;
    border-radius: 20px;
}
.badge-heart {
    position: absolute;
    top: 10px;
    right: 10px;
    background: white;
    color: #999;
    border-radius: 50%;
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
}
.badge-heart:hover {
    color: #ff6b6b;
}
.dish-card {
    border: none;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
}
.dish-img {
    width: 100%;
    height: 160px;
    object-fit: cover;
}
.veg-indicator {
    position: absolute;
    top: 10px;
    right: 10px;
    background: white;
    padding: 4px;
    border-radius: 4px;
}
.btn-order-outline {
    border: 1px solid #ff6b6b;
    color: #ff6b6b;
    border-radius: 8px;
    background: transparent;
    font-weight: 600;
}
.btn-order-outline:hover {
    background: #ff6b6b;
    color: white;
}
.compact-dish-card {
    display: flex;
    border: 1px solid #eee;
    border-radius: 12px;
    overflow: hidden;
    background: white;
}
.compact-dish-card img {
    width: 100px;
    height: 100px;
    object-fit: cover;
}
.compact-details {
    padding: 10px;
    flex-grow: 1;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}
</style>

<!-- Hero Section -->
<section class="hero-section">
    <div class="hero-overlay"></div>
    <div class="container position-relative text-white z-2 pt-4">
        <p class="text-warning fw-bold tracking-widest mb-2" style="letter-spacing: 2px; font-size: 0.85rem;">HOMEMADE FOOD, HAPPIER YOU</p>
        <h1 class="display-4 fw-bolder mb-3" style="max-width: 700px;">Authentic Homemade Meals, <span class="text-warning">Delivered from Local Kitchens</span></h1>
        
        <div class="d-flex gap-4 mb-4 fw-semibold text-light fs-6">
            <div><i class="bi bi-check-circle-fill text-success me-1"></i> Fresh Ingredients</div>
            <div><i class="bi bi-check-circle-fill text-warning me-1"></i> Hygienic & Safe</div>
            <div><i class="bi bi-heart-fill text-danger me-1"></i> Support Home Chefs</div>
        </div>

        <div class="search-wrapper mt-4">
            <form action="<?= url('/home') ?>" method="GET" class="search-container px-2" id="heroSearchForm" autocomplete="off">
                <input type="text" name="q" id="heroSearchInput" class="search-input bg-transparent" placeholder="Search for dishes (e.g. Biryani, Paneer, Dal, Thali)..." value="<?= sanitize($searchQuery ?? '') ?>" required autocomplete="off">
                <button type="submit" class="btn-search" id="btnHeroSearch"><i class="bi bi-search"></i></button>
            </form>
            <div id="dishSuggestionsBox" class="dish-suggestions-dropdown d-none"></div>
        </div>
        
        <div class="mt-3 text-light fs-7">
            <span class="text-muted">Popular searches:</span> 
            <span class="popular-tag text-warning" style="cursor:pointer; text-decoration:underline; margin-right:6px;">Thali</span> • 
            <span class="popular-tag text-warning" style="cursor:pointer; text-decoration:underline; margin-right:6px; margin-left:6px;">Gujarati</span> • 
            <span class="popular-tag text-warning" style="cursor:pointer; text-decoration:underline; margin-right:6px; margin-left:6px;">North Indian</span> • 
            <span class="popular-tag text-warning" style="cursor:pointer; text-decoration:underline; margin-right:6px; margin-left:6px;">Paneer</span> • 
            <span class="popular-tag text-warning" style="cursor:pointer; text-decoration:underline; margin-right:6px; margin-left:6px;">Healthy</span> • 
            <span class="popular-tag text-warning" style="cursor:pointer; text-decoration:underline; margin-left:6px;">Chicken</span>
        </div>
    </div>
</section>

<div class="container py-4">
    <!-- Category Scroller (Admin-Defined Categories & Quick Filters) -->
    <div class="category-scroller mb-5">
        <?php
        $cityParam = !empty($customerCity) ? '&city=' . urlencode($customerCity) : '';
        $cityParamOnly = !empty($customerCity) ? '?city=' . urlencode($customerCity) : '';
        ?>
        <!-- All Dishes -->
        <a href="<?= ctx_url('/menu' . $cityParamOnly) ?>" class="category-item" title="Browse All Dishes">
            <div class="category-circle">
                <i class="bi bi-grid-3x3-gap-fill text-primary fs-3"></i>
            </div>
            <span class="category-name-text">All Dishes</span>
        </a>

        <?php
        // Standard icon mapping for well-known categories
        $catIconMap = [
            'north indian'     => 'bi-fire text-danger',
            'south indian'     => 'bi-egg-fried text-info',
            'gujarati'         => 'bi-cup-hot-fill text-warning',
            'gujarati special' => 'bi-cup-hot-fill text-warning',
            'healthy salads'   => 'bi-heart-pulse-fill text-success',
            'main course'      => 'bi-pie-chart-fill text-primary',
            'desserts'         => 'bi-cake2-fill text-danger',
            'snacks & bites'   => 'bi-basket-fill text-warning',
            'beverages'        => 'bi-cup-straw text-primary',
        ];

        if (!empty($adminCategories)):
            foreach ($adminCategories as $cat):
                $cName = trim($cat['category_name']);
                $cLower = strtolower($cName);

                // Select icon based on map or keyword heuristics
                if (isset($catIconMap[$cLower])) {
                    $cIcon = $catIconMap[$cLower];
                } elseif (str_contains($cLower, 'salad') || str_contains($cLower, 'healthy')) {
                    $cIcon = 'bi-heart-pulse-fill text-success';
                } elseif (str_contains($cLower, 'dessert') || str_contains($cLower, 'sweet') || str_contains($cLower, 'cake')) {
                    $cIcon = 'bi-cake2-fill text-danger';
                } elseif (str_contains($cLower, 'snack') || str_contains($cLower, 'bite') || str_contains($cLower, 'fast') || str_contains($cLower, 'chaat')) {
                    $cIcon = 'bi-basket-fill text-warning';
                } elseif (str_contains($cLower, 'drink') || str_contains($cLower, 'beverage') || str_contains($cLower, 'juice') || str_contains($cLower, 'tea')) {
                    $cIcon = 'bi-cup-straw text-primary';
                } elseif (str_contains($cLower, 'north') || str_contains($cLower, 'punjabi')) {
                    $cIcon = 'bi-fire text-danger';
                } elseif (str_contains($cLower, 'south')) {
                    $cIcon = 'bi-egg-fried text-info';
                } elseif (str_contains($cLower, 'gujarat')) {
                    $cIcon = 'bi-cup-hot-fill text-warning';
                } elseif (str_contains($cLower, 'rice') || str_contains($cLower, 'biryani') || str_contains($cLower, 'thali') || str_contains($cLower, 'meal')) {
                    $cIcon = 'bi-pie-chart-fill text-primary';
                } else {
                    $cIcon = 'bi-tag-fill text-warning';
                }

                $hasImg = !empty($cat['image']) && file_exists(PUBLIC_PATH . '/uploads/categories/' . $cat['image']);
                $targetUrl = ctx_url('/menu?category=' . $cat['id'] . $cityParam);
                ?>
                <a href="<?= $targetUrl ?>" class="category-item" title="Browse <?= sanitize($cName) ?>">
                    <div class="category-circle">
                        <?php if ($hasImg && !isset($catIconMap[$cLower])): ?>
                            <img src="<?= UPLOAD_URL . '/categories/' . sanitize($cat['image']) ?>" alt="<?= sanitize($cName) ?>">
                        <?php else: ?>
                            <i class="bi <?= $cIcon ?> fs-3"></i>
                        <?php endif; ?>
                    </div>
                    <span class="category-name-text"><?= sanitize($cName) ?></span>
                </a>
            <?php endforeach;
        endif; ?>

    </div>

    <!-- Featured Home Kitchens / Search Results -->
    <div class="d-flex justify-content-between align-items-end mb-3">
        <div>
            <?php if (!empty($searchQuery)): ?>
                <h4 class="fw-bold mb-0">Search Results for "<?= sanitize($searchQuery) ?>"</h4>
                <p class="text-muted small mb-0">Found in <?= sanitize($customerCity) ?>.</p>
            <?php else: ?>
                <h4 class="fw-bold mb-0">Featured Home Kitchens</h4>
                <p class="text-muted small mb-0">Top-rated home chefs bringing delicious meals to your doorstep in <?= sanitize($customerCity) ?>.</p>
            <?php endif; ?>
        </div>
        <a href="<?= url('/kitchens?city=' . urlencode($customerCity)) ?>" class="text-primary text-decoration-none fw-semibold">View All Kitchens &rarr;</a>
    </div>
    
    <div class="row g-4 mb-5">
        <?php if (empty($featuredKitchens)): ?>
            <div class="col-12 text-center py-5 bg-light rounded">
                <i class="bi bi-shop fs-1 text-muted mb-2 d-block"></i>
                <h5 class="text-muted">No kitchens found matching your search in <?= sanitize($customerCity) ?>.</h5>
            </div>
        <?php else: ?>
            <?php foreach ($featuredKitchens as $k): ?>
            <div class="col-md-3">
                <a href="<?= url('/kitchen/' . $k['id']) ?>" class="card kitchen-card h-100 text-decoration-none text-dark">
                    <div class="kitchen-img-wrapper">
                        <?php if (!empty($k['banner_image'])): ?>
                            <img src="<?= UPLOAD_URL . '/kitchens/' . $k['banner_image'] ?>" class="kitchen-img" alt="<?= sanitize($k['kitchen_name']) ?>">
                        <?php else: ?>
                            <img src="<?= url('/assets/images/default-kitchen.jpg') ?>" class="kitchen-img" alt="<?= sanitize($k['kitchen_name']) ?>">
                        <?php endif; ?>
                        <div class="badge-open"><i class="bi bi-door-open-fill"></i> Open</div>
                        <div class="badge-heart"><i class="bi bi-heart"></i></div>
                    </div>
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-start mb-1">
                            <h6 class="fw-bold mb-0 text-truncate" style="max-width: 75%;"><?= sanitize($k['kitchen_name']) ?></h6>
                            <?php if (!empty($k['avg_rating'])): ?>
                                <span class="badge bg-warning text-dark"><i class="bi bi-star-fill"></i> <?= number_format((float)$k['avg_rating'], 1) ?> (<?= (int)($k['review_count'] ?? 0) ?>)</span>
                            <?php else: ?>
                                <span class="badge bg-light text-muted border"><i class="bi bi-star"></i> New</span>
                            <?php endif; ?>
                        </div>
                        <p class="text-muted small mb-2"><i class="bi bi-geo-alt-fill text-danger"></i> <?= sanitize($k['city']) ?><?= !empty($k['pincode']) ? ' • ' . sanitize($k['pincode']) : '' ?></p>
                        <div class="d-flex flex-wrap gap-2 mb-3">
                            <?php if (!empty($k['hygiene_badge']) && $k['hygiene_badge'] === 'verified'): ?>
                                <span class="badge bg-success-subtle text-success border border-success-subtle"><i class="bi bi-shield-check me-1"></i> Verified</span>
                            <?php endif; ?>
                            <span class="badge bg-light text-dark border"><i class="bi bi-person me-1"></i> Chef <?= sanitize($k['chef_name']) ?></span>
                        </div>
                        <div class="text-muted small fw-semibold">
                        <i class="bi bi-clock"></i> 
                        <?php if (isset($k['distance'])): ?>
                            <span onclick="event.preventDefault(); window.location.href='<?= url('/nearby-kitchens?route_to=' . $k['id']) ?>';" class="text-primary fw-bold" style="cursor: pointer;" title="View route on map">
                                <?= number_format($k['distance'], 1) ?> km away
                            </span>
                        <?php else: ?>
                            30-45 mins
                        <?php endif; ?>
                    </div>
                    </div>
                </a>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Top Selling Dishes -->
    <div class="d-flex justify-content-between align-items-end mb-3">
        <div>
            <?php if (!empty($searchQuery)): ?>
                <h4 class="fw-bold mb-0">Matching Dishes</h4>
                <p class="text-muted small mb-0">Dishes matching "<?= sanitize($searchQuery) ?>" in <?= sanitize($customerCity) ?>.</p>
            <?php else: ?>
                <h4 class="fw-bold mb-0">Top Selling Dishes</h4>
                <p class="text-muted small mb-0">Most loved home-cooked dishes across Kravyo right now in <?= sanitize($customerCity) ?>.</p>
            <?php endif; ?>
        </div>
        <a href="<?= url('/menu?city=' . urlencode($customerCity)) ?>" class="text-primary text-decoration-none fw-semibold">View All Dishes &rarr;</a>
    </div>

    <div class="row g-4 mb-5">
        <?php if (empty($topSellingDishes)): ?>
            <div class="col-12 text-center py-5 bg-light rounded">
                <i class="bi bi-cup-hot fs-1 text-muted mb-2 d-block"></i>
                <h5 class="text-muted">No dishes found matching your search.</h5>
            </div>
        <?php else: ?>
            <?php foreach ($topSellingDishes as $d): ?>
            <div class="col-md-3">
                <div class="card dish-card h-100">
                    <div class="position-relative">
                        <?php if (!empty($d['image'])): ?>
                            <img src="<?= UPLOAD_URL . '/dishes/' . $d['image'] ?>" class="dish-img" alt="<?= sanitize($d['item_name']) ?>">
                        <?php else: ?>
                            <img src="<?= url('/assets/images/default-dish.jpg') ?>" class="dish-img" alt="<?= sanitize($d['item_name']) ?>">
                        <?php endif; ?>
                        <div class="badge-heart"><i class="bi bi-heart"></i></div>
                        <div class="veg-indicator border">
                            <?php if ($d['is_veg']): ?>
                                <i class="bi bi-circle-fill text-success fs-7 d-block"></i>
                            <?php else: ?>
                                <i class="bi bi-triangle-fill text-danger fs-7 d-block"></i>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="card-body p-3 d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-start mb-1">
                            <h6 class="fw-bold mb-0 text-truncate" style="max-width: 80%;"><?= sanitize($d['item_name']) ?></h6>
                            <?php if (isset($d['distance'])): ?>
                                <a href="<?= url('/nearby-kitchens?route_to=' . $d['kitchen_id']) ?>" class="badge bg-light text-primary border text-decoration-none" style="font-size:0.7rem;" title="View route on map">
                                    <i class="bi bi-geo-alt-fill me-1"></i><?= number_format($d['distance'], 1) ?> km
                                </a>
                            <?php endif; ?>
                        </div>
                        <p class="text-muted small mb-2 text-truncate"><i class="bi bi-shop text-danger me-1"></i><?= sanitize($d['kitchen_name']) ?></p>
                        <div class="mt-auto d-flex justify-content-between align-items-center">
                            <span class="fw-bolder fs-5">₹<?= number_format($d['price'], 2) ?></span>
                            <a href="<?= url('/dish/' . $d['id']) ?>" class="btn btn-order-outline btn-sm px-3">Order Now</a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Recommended For You -->
    <div class="d-flex justify-content-between align-items-end mb-3">
        <div>
            <?php if (!empty($searchQuery)): ?>
                <h4 class="fw-bold mb-0"><i class="bi bi-bullseye text-primary me-2"></i>Other Matches</h4>
                <p class="text-muted small mb-0">More matches based on your search in <?= sanitize($customerCity) ?>.</p>
            <?php else: ?>
                <h4 class="fw-bold mb-0"><i class="bi bi-bullseye text-primary me-2"></i>Recommended For You</h4>
                <p class="text-muted small mb-0">Personalised dishes matched to your taste in <?= sanitize($customerCity) ?>.</p>
            <?php endif; ?>
        </div>
        <a href="<?= url('/recommendations') ?>" class="text-primary text-decoration-none fw-semibold">View All Picks &rarr;</a>
    </div>

    <div class="row g-3 mb-5">
        <?php if (empty($recommendedDishes)): ?>
            <div class="col-12 text-center py-4 bg-light rounded">
                <p class="text-muted mb-0">No other matches found.</p>
            </div>
        <?php else: ?>
            <?php foreach ($recommendedDishes as $d): ?>
            <div class="col-md-3">
                <div class="compact-dish-card">
                    <div class="position-relative">
                        <?php if (!empty($d['image'])): ?>
                            <img src="<?= UPLOAD_URL . '/dishes/' . $d['image'] ?>" alt="<?= sanitize($d['item_name']) ?>">
                        <?php else: ?>
                            <img src="<?= url('/assets/images/default-dish.jpg') ?>" alt="<?= sanitize($d['item_name']) ?>">
                        <?php endif; ?>
                        <div class="veg-indicator border p-1" style="top:5px;right:5px;">
                            <?php if ($d['is_veg']): ?>
                                <i class="bi bi-circle-fill text-success d-block" style="font-size:0.5rem"></i>
                            <?php else: ?>
                                <i class="bi bi-triangle-fill text-danger d-block" style="font-size:0.5rem"></i>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="compact-details">
                        <h6 class="fw-bold mb-0 text-truncate" style="font-size:0.9rem;"><?= sanitize($d['item_name']) ?></h6>
                        <div class="text-muted mb-1 text-truncate" style="font-size:0.7rem;"><i class="bi bi-shop text-danger me-1"></i><?= sanitize($d['kitchen_name']) ?></div>
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <div class="fw-bolder fs-6">₹<?= number_format($d['price'], 2) ?></div>
                            <?php if (isset($d['distance'])): ?>
                                <a href="<?= url('/nearby-kitchens?route_to=' . $d['kitchen_id']) ?>" class="text-primary text-decoration-none" style="font-size:0.7rem; font-weight: 600;" title="View route on map">
                                    <i class="bi bi-geo-alt-fill"></i> <?= number_format($d['distance'], 1) ?> km
                                </a>
                            <?php endif; ?>
                        </div>
                        <a href="<?= url('/dish/' . $d['id']) ?>" class="btn btn-order-outline btn-sm py-0 w-100" style="font-size:0.8rem;">Order Now</a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('heroSearchInput');
    const suggestionsBox = document.getElementById('dishSuggestionsBox');
    const searchForm = document.getElementById('heroSearchForm');
    const customerCity = <?= json_encode($customerCity ?? '') ?>;
    let debounceTimer = null;
    let currentFocus = -1;

    if (!searchInput || !suggestionsBox) return;

    function escapeHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function fetchSuggestions(query) {
        const cleanQ = query.trim();
        if (cleanQ.length === 0) {
            suggestionsBox.innerHTML = '';
            suggestionsBox.classList.add('d-none');
            return;
        }

        const url = '<?= url('/api/dishes/suggest') ?>?q=' + encodeURIComponent(cleanQ) + '&city=' + encodeURIComponent(customerCity);

        fetch(url)
            .then(res => res.json())
            .then(data => {
                if (!data || !data.success) {
                    suggestionsBox.classList.add('d-none');
                    return;
                }

                currentFocus = -1;
                renderSuggestions(data.dishes, cleanQ, data.city || customerCity);
            })
            .catch(err => {
                console.error('Error fetching dish suggestions:', err);
            });
    }

    function renderSuggestions(dishes, query, city) {
        const displayCity = city || customerCity || 'your city';

        if (!dishes || dishes.length === 0) {
            suggestionsBox.innerHTML = `
                <div class="dish-sugg-empty">
                    <i class="bi bi-search text-muted fs-4 d-block mb-1"></i>
                    No dishes found matching "<strong>${escapeHtml(query)}</strong>" in <strong>${escapeHtml(displayCity)}</strong>
                </div>
            `;
            suggestionsBox.classList.remove('d-none');
            return;
        }

        let html = '';
        dishes.forEach((dish, idx) => {
            const vegIcon = dish.is_veg
                ? `<span class="badge bg-success-subtle text-success p-1 border border-success-subtle" title="Pure Veg"><i class="bi bi-circle-fill" style="font-size: 0.5rem;"></i></span>`
                : `<span class="badge bg-danger-subtle text-danger p-1 border border-danger-subtle" title="Non-Veg"><i class="bi bi-triangle-fill" style="font-size: 0.5rem;"></i></span>`;

            html += `
                <a href="${escapeHtml(dish.url)}" class="dish-suggestion-item" data-index="${idx}">
                    <img src="${escapeHtml(dish.image_url)}" alt="${escapeHtml(dish.name)}" class="dish-sugg-img" onerror="this.src='<?= url('/assets/images/default-dish.jpg') ?>'">
                    <div class="dish-sugg-info">
                        <div class="dish-sugg-name">
                            ${vegIcon}
                            <span>${escapeHtml(dish.name)}</span>
                        </div>
                        <div class="dish-sugg-meta">
                            ${escapeHtml(dish.category)} • by ${escapeHtml(dish.kitchen)}
                        </div>
                    </div>
                    <div class="dish-sugg-price">${escapeHtml(dish.price_display)}</div>
                </a>
            `;
        });

        html += `
            <div class="dish-sugg-footer">
                <a href="<?= url('/menu') ?>?q=${encodeURIComponent(query)}&city=${encodeURIComponent(displayCity)}">
                    See all dishes for "${escapeHtml(query)}" in ${escapeHtml(displayCity)} &rarr;
                </a>
            </div>
        `;

        suggestionsBox.innerHTML = html;
        suggestionsBox.classList.remove('d-none');
    }

    // Live typing with snappy 150ms debounce
    searchInput.addEventListener('input', function() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            fetchSuggestions(this.value);
        }, 150);
    });

    // Clicking / focusing the search input
    searchInput.addEventListener('focus', function() {
        if (this.value.trim().length > 0) {
            fetchSuggestions(this.value);
        }
    });

    // Keyboard navigation (Arrow Down, Up, Enter, Escape)
    searchInput.addEventListener('keydown', function(e) {
        const items = suggestionsBox.querySelectorAll('.dish-suggestion-item');
        if (!items || items.length === 0) return;

        if (e.key === 'ArrowDown') {
            e.preventDefault();
            currentFocus++;
            if (currentFocus >= items.length) currentFocus = 0;
            addActive(items);
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            currentFocus--;
            if (currentFocus < 0) currentFocus = items.length - 1;
            addActive(items);
        } else if (e.key === 'Enter') {
            if (currentFocus > -1 && items[currentFocus]) {
                e.preventDefault();
                items[currentFocus].click();
            }
        } else if (e.key === 'Escape') {
            suggestionsBox.classList.add('d-none');
        }
    });

    function addActive(items) {
        items.forEach(item => item.classList.remove('active'));
        if (currentFocus > -1 && items[currentFocus]) {
            items[currentFocus].classList.add('active');
            items[currentFocus].scrollIntoView({ block: 'nearest' });
        }
    }

    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !suggestionsBox.contains(e.target)) {
            suggestionsBox.classList.add('d-none');
        }
    });

    // Popular tags click support
    document.querySelectorAll('.popular-tag').forEach(tag => {
        tag.addEventListener('click', function() {
            searchInput.value = this.innerText.trim();
            searchInput.focus();
            fetchSuggestions(searchInput.value);
        });
    });
});
</script>

