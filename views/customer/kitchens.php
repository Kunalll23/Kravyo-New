<!-- Phase 5: Kitchen Browse Page — Explore Local Home Kitchens -->
<section class="kitchen-browse-hero">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-7">
                <h1 class="fw-800 text-white mb-2">
                    <i class="bi bi-shop-window me-2 text-warning"></i>Explore Home Kitchens
                </h1>
                <p class="text-white-50 mb-0">Discover home chefs near you serving authentic, hygienic home-cooked meals  </p>
            </div>
            <div class="col-lg-5 text-lg-end mt-3 mt-lg-0 d-flex flex-wrap justify-content-lg-end align-items-center gap-2">
                <a href="<?= ctx_url('/nearby-kitchens') ?>" class="btn btn-warning text-dark fw-bold rounded-pill px-3 py-2 shadow-sm">
                    <i class="bi bi-map-fill me-1 text-danger"></i> Nearby Kitchens Map
                </a>
                <span class="badge bg-white text-dark px-3 py-2 fs-6 rounded-pill" id="kitchensCountBadge">
                    <i class="bi bi-geo-alt-fill text-danger me-1"></i>
                    <span id="kitchensCountText"><?= count($kitchens) ?> Kitchen<?= count($kitchens) !== 1 ? 's' : '' ?> Found</span>
                </span>
            </div>
        </div>
    </div>
</section>

<section class="py-4">
    <div class="container">
        <!-- Live Search & Filter Bar (No Filter Button Required) -->
        <div class="kitchen-filter-bar mb-4">
            <form method="GET" action="<?= ctx_url('/kitchens') ?>" id="kitchenFilterForm" onsubmit="return false;">
                <div class="row g-3 align-items-end">
                    <!-- Search Kitchen Name with Autocomplete (Col 6) -->
                    <div class="col-lg-6 col-md-6">
                        <label class="filter-label"><i class="bi bi-search me-1"></i> Search Kitchen Name</label>
                        <div class="search-input-wrapper position-relative">
                            <i class="bi bi-search search-icon-prefix"></i>
                            <input type="text" name="q" id="kitchenSearchInput" class="form-control filter-input filter-input-search"
                                   placeholder="Search kitchen name..."
                                   value="<?= sanitize($filters['keyword'] ?? '') ?>"
                                   autocomplete="off">
                            <button type="button" id="searchClearBtn" class="search-clear-btn" title="Clear search" aria-label="Clear search">
                                <i class="bi bi-x-lg"></i>
                            </button>
                            <div id="searchLoadingSpinner" class="spinner-border spinner-border-sm text-warning search-loading-spinner" role="status">
                                <span class="visually-hidden">Searching...</span>
                            </div>
                            <!-- Autocomplete Dropdown -->
                            <div id="kitchenSuggestDropdown" class="kitchen-suggest-dropdown shadow-lg d-none"></div>
                        </div>
                    </div>

                    <!-- City Select (Col 3) -->
                    <div class="col-lg-3 col-md-3">
                        <label class="filter-label"><i class="bi bi-geo-alt me-1"></i> City</label>
                        <select name="city" id="citySelect" class="form-select filter-input">
                            <option value="">All Cities</option>
                            <?php foreach ($cities as $city): ?>
                                <option value="<?= sanitize($city) ?>" <?= ($filters['city'] ?? '') === $city ? 'selected' : '' ?>>
                                    <?= sanitize($city) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Open Now Checkbox & Quick Reset (Col 3) -->
                    <div class="col-lg-3 col-md-3 d-flex align-items-center justify-content-between">
                        <div class="form-check filter-checkbox my-1">
                            <input class="form-check-input" type="checkbox" name="open_only" id="openOnlyCheck"
                                   <?= !empty($filters['open_only']) ? 'checked' : '' ?>>
                            <label class="form-check-label fw-600 user-select-none" for="openOnlyCheck" style="font-size: 0.9rem; cursor: pointer;">
                                <i class="bi bi-clock text-success me-1"></i> Open Now
                            </label>
                        </div>
                        <button type="button" id="resetFiltersBarBtn" class="btn btn-sm btn-outline-secondary rounded-pill px-2 py-1" title="Reset all filters">
                            <i class="bi bi-arrow-counterclockwise"></i> Reset
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Kitchen Cards Grid Container -->
        <div id="kitchensCardsWrap">
            <?php include __DIR__ . '/_kitchen_cards.php'; ?>
        </div>
    </div>
</section>

<!-- Client-side Live Auto-Filter & Autocomplete Engine -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchInput      = document.getElementById('kitchenSearchInput');
    const searchClearBtn   = document.getElementById('searchClearBtn');
    const searchSpinner    = document.getElementById('searchLoadingSpinner');
    const suggestDropdown  = document.getElementById('kitchenSuggestDropdown');
    const citySelect       = document.getElementById('citySelect');
    const openOnlyCheck    = document.getElementById('openOnlyCheck');
    const resetBarBtn      = document.getElementById('resetFiltersBarBtn');
    const cardsWrap        = document.getElementById('kitchensCardsWrap');
    const countBadgeText   = document.getElementById('kitchensCountText');

    let filterAbortController = null;
    let suggestAbortController = null;
    let activeSuggestIndex = -1;
    let currentSuggestions = [];

    // Helper: Context URL builder
    function getContextUrl(path) {
        const base = '<?= rtrim(ctx_url(""), "/") ?>';
        return base + (path.startsWith('/') ? path : '/' + path);
    }

    // Toggle clear search button visibility
    function updateClearButton() {
        if (searchInput && searchClearBtn) {
            searchClearBtn.style.display = searchInput.value.trim().length > 0 ? 'block' : 'none';
        }
    }
    updateClearButton();

    if (searchClearBtn) {
        searchClearBtn.addEventListener('click', function () {
            if (searchInput) {
                searchInput.value = '';
                searchInput.focus();
            }
            updateClearButton();
            closeSuggestions();
            triggerLiveFilter();
        });
    }

    // Debounce helper
    function debounce(func, delay) {
        let timeoutId;
        return function (...args) {
            clearTimeout(timeoutId);
            timeoutId = setTimeout(() => func.apply(this, args), delay);
        };
    }

    // -------------------------------------------------------------
    // LIVE FILTERING ENGINE
    // -------------------------------------------------------------
    function triggerLiveFilter() {
        const q    = searchInput ? searchInput.value.trim() : '';
        const city = citySelect ? citySelect.value.trim() : '';
        const open = openOnlyCheck && openOnlyCheck.checked ? '1' : '';

        // Cancel previous pending filter request
        if (filterAbortController) {
            filterAbortController.abort();
        }
        filterAbortController = new AbortController();

        // Build query params
        const params = new URLSearchParams();
        if (q) params.set('q', q);
        if (city) params.set('city', city);
        if (open) params.set('open_only', '1');
        params.set('ajax', '1');

        // Show subtle loading state
        if (cardsWrap) cardsWrap.classList.add('is-loading');

        fetch(getContextUrl('/kitchens?' + params.toString()), {
            signal: filterAbortController.signal,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (cardsWrap) cardsWrap.classList.remove('is-loading');
            if (data.success) {
                if (cardsWrap) cardsWrap.innerHTML = data.html;
                if (countBadgeText && data.count_label) {
                    countBadgeText.textContent = data.count_label;
                }

                // Synchronize browser URL bar without page reload
                params.delete('ajax');
                const newUrl = getContextUrl('/kitchens') + (params.toString() ? '?' + params.toString() : '');
                window.history.replaceState({ path: newUrl }, '', newUrl);

                // Wire up reset button inside empty state if present
                const emptyReset = document.getElementById('resetFiltersEmptyBtn');
                if (emptyReset) {
                    emptyReset.addEventListener('click', resetAllFilters);
                }
            }
        })
        .catch(err => {
            if (err.name !== 'AbortError') {
                if (cardsWrap) cardsWrap.classList.remove('is-loading');
                console.error('Error filtering kitchens:', err);
            }
        });
    }

    const debouncedFilter = debounce(triggerLiveFilter, 300);

    // Filter event listeners
    if (searchInput) {
        searchInput.addEventListener('input', function () {
            updateClearButton();
            debouncedFilter();
            fetchSuggestions(this.value.trim());
        });
    }

    if (citySelect) {
        citySelect.addEventListener('change', function () {
            triggerLiveFilter();
            if (searchInput && searchInput.value.trim().length > 0) {
                fetchSuggestions(searchInput.value.trim());
            }
        });
    }

    if (openOnlyCheck) {
        openOnlyCheck.addEventListener('change', triggerLiveFilter);
    }

    function resetAllFilters() {
        if (searchInput) searchInput.value = '';
        if (citySelect) citySelect.value = '';
        if (openOnlyCheck) openOnlyCheck.checked = false;
        updateClearButton();
        closeSuggestions();
        triggerLiveFilter();
    }

    if (resetBarBtn) {
        resetBarBtn.addEventListener('click', resetAllFilters);
    }

    // Also wire initial reset button in empty state if page loaded empty
    const initialEmptyReset = document.getElementById('resetFiltersEmptyBtn');
    if (initialEmptyReset) {
        initialEmptyReset.addEventListener('click', resetAllFilters);
    }

    // -------------------------------------------------------------
    // CITY-SCOPED AUTOCOMPLETE SUGGESTIONS ENGINE
    // -------------------------------------------------------------
    const fetchSuggestions = debounce(function (query) {
        if (!query || query.length < 1) {
            closeSuggestions();
            return;
        }

        const city = citySelect ? citySelect.value.trim() : '';

        if (suggestAbortController) {
            suggestAbortController.abort();
        }
        suggestAbortController = new AbortController();

        if (searchSpinner) searchSpinner.style.display = 'block';

        const params = new URLSearchParams({
            q: query,
            city: city
        });

        fetch(getContextUrl('/api/kitchens/suggest?' + params.toString()), {
            signal: suggestAbortController.signal,
            headers: { 'Accept': 'application/json' }
        })
        .then(res => res.json())
        .then(data => {
            if (searchSpinner) searchSpinner.style.display = 'none';
            if (data.success && Array.isArray(data.suggestions)) {
                renderSuggestions(data.suggestions, query, city);
            } else {
                closeSuggestions();
            }
        })
        .catch(err => {
            if (err.name !== 'AbortError') {
                if (searchSpinner) searchSpinner.style.display = 'none';
                console.error('Error fetching suggestions:', err);
            }
        });
    }, 200);

    function highlightMatch(text, query) {
        if (!query || !text) return text || '';
        const regex = new RegExp('(' + query.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + ')', 'gi');
        return text.replace(regex, '<span class="text-primary fw-bold">$1</span>');
    }

    function renderSuggestions(suggestions, query, selectedCity) {
        currentSuggestions = suggestions;
        activeSuggestIndex = -1;

        if (!suggestDropdown) return;

        if (suggestions.length === 0) {
            const scopeNote = selectedCity ? ` in ${selectedCity}` : '';
            suggestDropdown.innerHTML = `
                <div class="suggest-empty text-muted p-3 text-center" style="font-size:0.88rem;">
                    <i class="bi bi-search me-1"></i> No kitchens matching "<strong>${escapeHtml(query)}</strong>"${scopeNote}
                </div>
            `;
            suggestDropdown.classList.remove('d-none');
            return;
        }

        let html = '';
        suggestions.forEach((item, index) => {
            const statusDot = item.is_open 
                ? '<span class="badge bg-success-subtle text-success py-0 px-1 ms-1" style="font-size:0.7rem;">Open</span>'
                : '<span class="badge bg-secondary-subtle text-secondary py-0 px-1 ms-1" style="font-size:0.7rem;">Closed</span>';

            html += `
                <div class="suggest-item" data-index="${index}" data-url="${escapeHtml(item.url)}" data-kitchen="${escapeHtml(item.kitchen_name)}">
                    <div class="me-2 text-truncate">
                        <div class="suggest-title text-truncate">
                            <span class="suggest-badge suggest-badge-kitchen">
                                <i class="bi bi-shop me-1"></i>Kitchen
                            </span>
                            <span>${highlightMatch(item.kitchen_name, query)}</span>
                            ${statusDot}
                        </div>
                        <div class="suggest-subtitle text-truncate">
                            <i class="bi bi-person text-secondary me-1"></i>Chef: ${escapeHtml(item.chef_name)} &bull; 
                            <i class="bi bi-geo-alt text-danger me-1"></i>${escapeHtml(item.city)} &bull; 
                            <span>${item.dish_count} dishes</span>
                        </div>
                    </div>
                    <div class="ms-2">
                        <i class="bi bi-arrow-right-short text-muted fs-5"></i>
                    </div>
                </div>
            `;
        });

        suggestDropdown.innerHTML = html;
        suggestDropdown.classList.remove('d-none');

        // Wire click events
        suggestDropdown.querySelectorAll('.suggest-item').forEach(el => {
            el.addEventListener('click', function () {
                const url = this.getAttribute('data-url');
                if (url) {
                    window.location.href = url;
                }
            });
        });
    }

    function closeSuggestions() {
        if (suggestDropdown) {
            suggestDropdown.classList.add('d-none');
            suggestDropdown.innerHTML = '';
        }
        currentSuggestions = [];
        activeSuggestIndex = -1;
    }

    function escapeHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    // Keyboard navigation (Arrow keys + Enter + Escape)
    if (searchInput) {
        searchInput.addEventListener('keydown', function (e) {
            if (!suggestDropdown) return;
            const items = suggestDropdown.querySelectorAll('.suggest-item');
            if (suggestDropdown.classList.contains('d-none') || items.length === 0) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    triggerLiveFilter();
                }
                return;
            }

            if (e.key === 'ArrowDown') {
                e.preventDefault();
                activeSuggestIndex = (activeSuggestIndex + 1) % items.length;
                updateActiveItem(items);
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                activeSuggestIndex = (activeSuggestIndex - 1 + items.length) % items.length;
                updateActiveItem(items);
            } else if (e.key === 'Enter') {
                e.preventDefault();
                if (activeSuggestIndex >= 0 && items[activeSuggestIndex]) {
                    items[activeSuggestIndex].click();
                } else {
                    closeSuggestions();
                    triggerLiveFilter();
                }
            } else if (e.key === 'Escape') {
                closeSuggestions();
            }
        });

        // Reopen dropdown on focus if input has value
        searchInput.addEventListener('focus', function () {
            if (this.value.trim().length > 0 && currentSuggestions.length > 0 && suggestDropdown) {
                suggestDropdown.classList.remove('d-none');
            }
        });
    }

    function updateActiveItem(items) {
        items.forEach((item, idx) => {
            if (idx === activeSuggestIndex) {
                item.classList.add('is-selected');
                item.scrollIntoView({ block: 'nearest' });
            } else {
                item.classList.remove('is-selected');
            }
        });
    }

    // Close dropdown when clicking outside
    document.addEventListener('click', function (e) {
        if (searchInput && suggestDropdown && !searchInput.contains(e.target) && !suggestDropdown.contains(e.target)) {
            closeSuggestions();
        }
    });
});
</script>
