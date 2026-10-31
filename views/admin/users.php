<?php
/**
 * Kravyo — Admin User Management View
 * Features: Live search with autocomplete suggestions, Role filter, and Status filter.
 */

function userFilterUrl(?string $role = null, ?string $status = null, ?string $search = null): string {
    $p = [];
    if (!empty($role) && $role !== 'all') {
        $p['role'] = $role;
    }
    if (!empty($status) && $status !== 'all') {
        $p['status'] = $status;
    }
    if (!empty($search)) {
        $p['search'] = $search;
    }
    $q = http_build_query($p);
    return url('/admin/users' . ($q ? '?' . $q : ''));
}

$currentRole   = $filters['role'] ?? '';
$currentStatus = $filters['status'] ?? '';
$currentSearch = $filters['search'] ?? '';
$hasFilters    = !empty($currentRole) || !empty($currentStatus) || !empty($currentSearch);
?>

<div class="container py-4">
    <!-- Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">
                <i class="bi bi-people text-warning me-2"></i>User Management
            </h2>
            <p class="text-muted mb-0">Manage platform users, roles, and access statuses.</p>
        </div>
        <div class="d-flex gap-2 mt-3 mt-md-0">
            <a href="<?= url('/admin/dashboard') ?>" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to Dashboard
            </a>
        </div>
    </div>

    <?php View::partial('flash_messages'); ?>

    <!-- Role Quick Tabs / Pill Navigation -->
    <div class="d-flex flex-wrap gap-2 mb-3 align-items-center">
        <a class="btn btn-sm rounded-pill px-3 py-2 fw-semibold <?= empty($currentRole) || $currentRole === 'all' ? 'btn-primary shadow-sm' : 'btn-white border text-secondary bg-white' ?>" 
           href="<?= userFilterUrl('all', $currentStatus, $currentSearch) ?>">
            All Users <span class="badge rounded-pill bg-light text-dark ms-1"><?= $counts['all'] ?? 0 ?></span>
        </a>
        <a class="btn btn-sm rounded-pill px-3 py-2 fw-semibold <?= $currentRole === 'customer' ? 'btn-primary shadow-sm' : 'btn-white border text-secondary bg-white' ?>" 
           href="<?= userFilterUrl('customer', $currentStatus, $currentSearch) ?>">
            <i class="bi bi-person me-1"></i>Customers <span class="badge rounded-pill bg-light text-dark ms-1"><?= $counts['customer'] ?? 0 ?></span>
        </a>
        <a class="btn btn-sm rounded-pill px-3 py-2 fw-semibold <?= $currentRole === 'chef' ? 'btn-warning text-dark shadow-sm' : 'btn-white border text-secondary bg-white' ?>" 
           href="<?= userFilterUrl('chef', $currentStatus, $currentSearch) ?>">
            <i class="bi bi-fire me-1"></i>Chefs <span class="badge rounded-pill bg-light text-dark ms-1"><?= $counts['chef'] ?? 0 ?></span>
        </a>
        <a class="btn btn-sm rounded-pill px-3 py-2 fw-semibold <?= $currentRole === 'delivery' ? 'btn-info text-dark shadow-sm' : 'btn-white border text-secondary bg-white' ?>" 
           href="<?= userFilterUrl('delivery', $currentStatus, $currentSearch) ?>">
            <i class="bi bi-bicycle me-1"></i>Delivery Partners <span class="badge rounded-pill bg-light text-dark ms-1"><?= $counts['delivery'] ?? 0 ?></span>
        </a>
    </div>

    <!-- Filter & Live Autocomplete Search Toolbar -->
    <div class="card kravyo-card border-0 shadow-sm rounded-4 mb-4 bg-white p-3" style="position: relative; z-index: 1040;">
        <form action="<?= url('/admin/users') ?>" method="GET" id="userFilterForm" class="row g-2 align-items-center">
            
            <!-- Search Bar with Live Autocomplete Suggestions -->
            <div class="col-12 col-md-5">
                <div class="position-relative" id="userSearchWrapper">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 text-muted">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" 
                               name="search" 
                               id="userSearchInput" 
                               class="form-control border-start-0 bg-light" 
                               placeholder="Search user name or email..." 
                               autocomplete="off"
                               value="<?= sanitize($currentSearch) ?>">
                        <?php if (!empty($currentSearch)): ?>
                            <a href="<?= userFilterUrl($currentRole, $currentStatus, '') ?>" class="input-group-text bg-light border-start-0 text-muted text-decoration-none" title="Clear Search">
                                <i class="bi bi-x-circle-fill"></i>
                            </a>
                        <?php endif; ?>
                    </div>

                    <!-- Autocomplete Suggestions Dropdown -->
                    <div id="userAutocompleteMenu" class="kravyo-user-suggestions d-none"></div>
                </div>
            </div>

            <!-- Role Filter Dropdown -->
            <div class="col-6 col-md-3">
                <select name="role" id="userRoleSelect" class="form-select bg-light" onchange="document.getElementById('userFilterForm').submit()">
                    <option value="">👥 All Roles (<?= $counts['all'] ?? 0 ?>)</option>
                    <option value="customer" <?= $currentRole === 'customer' ? 'selected' : '' ?>>👤 Customer (<?= $counts['customer'] ?? 0 ?>)</option>
                    <option value="chef" <?= $currentRole === 'chef' ? 'selected' : '' ?>>🍳 Chef (<?= $counts['chef'] ?? 0 ?>)</option>
                    <option value="delivery" <?= $currentRole === 'delivery' ? 'selected' : '' ?>>🚴 Delivery Partner (<?= $counts['delivery'] ?? 0 ?>)</option>
                </select>
            </div>

            <!-- Status Filter Dropdown -->
            <div class="col-6 col-md-2">
                <select name="status" id="userStatusSelect" class="form-select bg-light" onchange="document.getElementById('userFilterForm').submit()">
                    <option value="">⚡ All Statuses</option>
                    <option value="active" <?= $currentStatus === 'active' ? 'selected' : '' ?>>🟢 Active (<?= $counts['active'] ?? 0 ?>)</option>
                    <option value="suspended" <?= $currentStatus === 'suspended' ? 'selected' : '' ?>>🔴 Suspended (<?= $counts['suspended'] ?? 0 ?>)</option>
                </select>
            </div>

            <!-- Actions / Reset Buttons -->
            <div class="col-12 col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100 fw-semibold">
                    <i class="bi bi-filter me-1"></i>Apply
                </button>
                <?php if ($hasFilters): ?>
                    <a href="<?= url('/admin/users') ?>" class="btn btn-outline-secondary text-nowrap" title="Reset all filters">
                        <i class="bi bi-arrow-counterclockwise me-1"></i>Reset
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Status summary & Results Count -->
    <div class="d-flex justify-content-between align-items-center mb-3 text-muted small px-1">
        <div>
            Showing <strong class="text-dark" id="displayCount"><?= $filteredCount ?></strong> of <strong><?= $totalUsersCount ?></strong> total users
            <?php if (!empty($currentSearch)): ?>
                matching "<strong class="text-dark"><?= sanitize($currentSearch) ?></strong>"
            <?php endif; ?>
        </div>
        <?php if ($hasFilters): ?>
            <div>
                <a href="<?= url('/admin/users') ?>" class="text-decoration-none text-danger fw-semibold">
                    <i class="bi bi-x-circle me-1"></i>Clear all filters
                </a>
            </div>
        <?php endif; ?>
    </div>

    <!-- Users Table Card -->
    <div class="card kravyo-card border-0 shadow-sm overflow-hidden">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="usersTable">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">User</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Joined</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="usersTableBody">
                        <?php if (empty($users)): ?>
                            <tr id="noUsersRow">
                                <td colspan="5" class="text-center py-5 text-muted">
                                    <i class="bi bi-person-x display-4 d-block mb-3 opacity-50"></i>
                                    <h6 class="fw-bold mb-1">No users found</h6>
                                    <p class="small text-muted mb-3">No platform users matched your current search and filter criteria.</p>
                                    <a href="<?= url('/admin/users') ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-arrow-counterclockwise me-1"></i>Reset Filters
                                    </a>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($users as $user): ?>
                                <?php
                                $uRole   = $user['role'] ?? 'customer';
                                $uStatus = $user['status'] ?? 'active';
                                $uName   = $user['full_name'] ?? 'Unknown User';
                                $uEmail  = $user['email'] ?? '';
                                ?>
                                <tr class="user-row" 
                                    data-name="<?= htmlspecialchars(strtolower($uName)) ?>" 
                                    data-email="<?= htmlspecialchars(strtolower($uEmail)) ?>"
                                    data-role="<?= htmlspecialchars(strtolower($uRole)) ?>"
                                    data-status="<?= htmlspecialchars(strtolower($uStatus)) ?>">
                                    <td class="ps-4 py-3">
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="avatar-circle">
                                                <?php if ($uRole === 'chef'): ?>
                                                    <i class="bi bi-fire"></i>
                                                <?php elseif ($uRole === 'delivery'): ?>
                                                    <i class="bi bi-bicycle"></i>
                                                <?php else: ?>
                                                    <i class="bi bi-person"></i>
                                                <?php endif; ?>
                                            </div>
                                            <div>
                                                <h6 class="mb-0 fw-semibold user-name-text"><?= sanitize($uName) ?></h6>
                                                <small class="text-muted user-email-text"><?= sanitize($uEmail) ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if ($uRole === 'admin'): ?>
                                            <span class="badge bg-danger">Admin</span>
                                        <?php elseif ($uRole === 'chef'): ?>
                                            <span class="badge bg-warning text-dark"><i class="bi bi-fire me-1"></i>Chef</span>
                                        <?php elseif ($uRole === 'delivery'): ?>
                                            <span class="badge bg-info text-dark"><i class="bi bi-bicycle me-1"></i>Delivery</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary"><i class="bi bi-person me-1"></i>Customer</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($uStatus === 'active'): ?>
                                            <span class="badge bg-success bg-opacity-10 text-success">
                                                <i class="bi bi-check-circle me-1"></i>Active
                                            </span>
                                        <?php elseif ($uStatus === 'suspended'): ?>
                                            <span class="badge bg-danger bg-opacity-10 text-danger">
                                                <i class="bi bi-slash-circle me-1"></i>Suspended
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary bg-opacity-10 text-secondary"><?= ucfirst(sanitize($uStatus)) ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <small class="text-muted"><?= date('d M Y', strtotime($user['created_at'])) ?></small>
                                    </td>
                                    <td class="text-end pe-4">
                                        <?php if (!($uRole === 'admin' && $user['id'] === Session::get('admin_id'))): ?>
                                            <form action="<?= url('/admin/user/toggle') ?>" method="POST" class="d-inline">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                                <input type="hidden" name="role" value="<?= htmlspecialchars($uRole) ?>">
                                                <input type="hidden" name="redirect_back" value="<?= htmlspecialchars($_SERVER['REQUEST_URI'] ?? '/admin/users') ?>">
                                                
                                                <?php if ($uStatus === 'active'): ?>
                                                    <input type="hidden" name="status" value="suspended">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to suspend <?= addslashes($uName) ?>?');">
                                                        <i class="bi bi-slash-circle me-1"></i>Suspend
                                                    </button>
                                                <?php else: ?>
                                                    <input type="hidden" name="status" value="active">
                                                    <button type="submit" class="btn btn-sm btn-outline-success">
                                                        <i class="bi bi-check-circle me-1"></i>Activate
                                                    </button>
                                                <?php endif; ?>
                                            </form>
                                        <?php else: ?>
                                            <span class="badge bg-light text-dark border">You</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Autocomplete Dropdown Styles -->
<style>
.kravyo-user-suggestions {
    position: absolute;
    top: calc(100% + 4px);
    left: 0;
    right: 0;
    width: 100%;
    background: #ffffff !important;
    border-radius: 12px;
    box-shadow: 0 12px 32px rgba(0, 0, 0, 0.16), 0 4px 12px rgba(0, 0, 0, 0.08) !important;
    border: 1px solid #e2e8f0;
    max-height: 380px;
    overflow-y: auto;
    z-index: 1060 !important;
    padding: 6px;
}
.kravyo-user-suggestions .suggest-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 8px 12px;
    border-radius: 8px;
    text-decoration: none;
    color: #1e293b;
    font-size: 0.88rem;
    cursor: pointer;
    transition: background-color 0.15s ease, color 0.15s ease;
}
.kravyo-user-suggestions .suggest-item:hover,
.kravyo-user-suggestions .suggest-item.active {
    background-color: #fff4ed !important;
    color: #ff6b35 !important;
}
.kravyo-user-suggestions .suggest-item.active {
    border-left: 3px solid #ff6b35;
}
.kravyo-user-suggestions .suggest-header {
    font-size: 0.72rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #64748b;
    padding: 6px 12px 4px 12px;
    margin: 0;
}
.kravyo-user-suggestions mark {
    background-color: #fef08a !important;
    color: #854d0e !important;
    padding: 0 2px;
    border-radius: 2px;
}
.suggest-avatar {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.9rem;
    flex-shrink: 0;
}
</style>

<!-- Live Auto-Complete & Interactive Filtering Script -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchInput      = document.getElementById('userSearchInput');
    const suggestionsMenu  = document.getElementById('userAutocompleteMenu');
    const searchWrapper    = document.getElementById('userSearchWrapper');
    const filterForm       = document.getElementById('userFilterForm');
    const roleSelect       = document.getElementById('userRoleSelect');
    const statusSelect     = document.getElementById('userStatusSelect');
    const suggestEndpoint  = '<?= url('/admin/users/suggest') ?>';

    // Preloaded users dataset for instant 0ms suggestions
    const preloadedUsers = <?= json_encode($autocompleteUsers ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;

    let activeIndex = -1;
    let currentSuggestions = [];
    let debounceTimer = null;

    function escapeHtml(str) {
        return (str || '').replace(/[&<>"']/g, function (m) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[m];
        });
    }

    function highlightMatch(text, query) {
        if (!query) return escapeHtml(text);
        const escapedQuery = query.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        const regex = new RegExp('(' + escapedQuery + ')', 'gi');
        return escapeHtml(text).replace(regex, '<mark>$1</mark>');
    }

    function showSuggestions() {
        if (suggestionsMenu) suggestionsMenu.classList.remove('d-none');
    }

    function hideSuggestions() {
        if (!suggestionsMenu) return;
        suggestionsMenu.classList.add('d-none');
        activeIndex = -1;
    }

    function getRoleBadge(role) {
        switch (role) {
            case 'chef':
                return '<span class="badge bg-warning text-dark"><i class="bi bi-fire me-1"></i>Chef</span>';
            case 'delivery':
                return '<span class="badge bg-info text-dark"><i class="bi bi-bicycle me-1"></i>Delivery</span>';
            default:
                return '<span class="badge bg-secondary"><i class="bi bi-person me-1"></i>Customer</span>';
        }
    }

    function getRoleAvatar(role) {
        switch (role) {
            case 'chef':
                return '<div class="suggest-avatar bg-warning bg-opacity-25 text-warning-emphasis"><i class="bi bi-fire"></i></div>';
            case 'delivery':
                return '<div class="suggest-avatar bg-info bg-opacity-25 text-info-emphasis"><i class="bi bi-bicycle"></i></div>';
            default:
                return '<div class="suggest-avatar bg-primary bg-opacity-10 text-primary"><i class="bi bi-person"></i></div>';
        }
    }

    function getStatusBadge(status) {
        if (status === 'suspended') {
            return '<span class="badge bg-danger bg-opacity-10 text-danger small"><i class="bi bi-slash-circle me-1"></i>Suspended</span>';
        }
        return '<span class="badge bg-success bg-opacity-10 text-success small"><i class="bi bi-check-circle me-1"></i>Active</span>';
    }

    function renderSuggestions(matches, query) {
        if (!suggestionsMenu) return;
        currentSuggestions = matches;
        activeIndex = -1;

        if (!matches || matches.length === 0) {
            if (query.trim().length > 0) {
                suggestionsMenu.innerHTML = `
                    <div class="p-3 text-center text-muted small">
                        <i class="bi bi-person-x fs-4 d-block mb-1 opacity-50"></i>
                        No users matching "<strong>${escapeHtml(query)}</strong>"
                    </div>`;
                showSuggestions();
            } else {
                hideSuggestions();
            }
            return;
        }

        let html = `
            <div class="suggest-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-lightning-charge-fill text-warning me-1"></i>Suggested Users</span>
                <span class="text-muted fw-normal">${matches.length} found</span>
            </div>`;

        matches.forEach((user, idx) => {
            html += `
                <div class="suggest-item" data-index="${idx}" data-name="${escapeHtml(user.full_name)}" data-email="${escapeHtml(user.email)}">
                    <div class="d-flex align-items-center gap-2 overflow-hidden me-2">
                        ${getRoleAvatar(user.role)}
                        <div class="text-truncate">
                            <div class="fw-semibold text-truncate text-dark">${highlightMatch(user.full_name, query)}</div>
                            <div class="text-muted small text-truncate">${highlightMatch(user.email, query)}</div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-1 flex-shrink-0">
                        ${getRoleBadge(user.role)}
                        ${getStatusBadge(user.status)}
                    </div>
                </div>`;
        });

        suggestionsMenu.innerHTML = html;
        showSuggestions();

        // Attach click listeners to suggestions
        suggestionsMenu.querySelectorAll('.suggest-item').forEach(el => {
            el.addEventListener('click', function () {
                const name = this.getAttribute('data-name');
                searchInput.value = name;
                hideSuggestions();
                filterForm.submit();
            });
        });
    }

    function filterLocalSuggestions(query) {
        const q = query.trim().toLowerCase();
        const role = roleSelect ? roleSelect.value.toLowerCase() : '';
        const status = statusSelect ? statusSelect.value.toLowerCase() : '';

        return preloadedUsers.filter(u => {
            if (role && role !== 'all' && (u.role || '').toLowerCase() !== role) {
                return false;
            }
            if (status && status !== 'all' && (u.status || '').toLowerCase() !== status) {
                return false;
            }
            if (!q) {
                return true;
            }
            const nameMatch  = (u.full_name || '').toLowerCase().includes(q);
            const emailMatch = (u.email || '').toLowerCase().includes(q);
            return nameMatch || emailMatch;
        }).slice(0, 10);
    }

    // Input typing event for suggestions
    if (searchInput) {
        searchInput.addEventListener('input', function () {
            const query = this.value;
            clearTimeout(debounceTimer);

            if (query.trim().length === 0) {
                hideSuggestions();
                return;
            }

            // Instant local suggestion rendering
            const localMatches = filterLocalSuggestions(query);
            renderSuggestions(localMatches, query);

            // Optional AJAX fetch to guarantee server sync
            debounceTimer = setTimeout(() => {
                const role = roleSelect ? encodeURIComponent(roleSelect.value) : '';
                const status = statusSelect ? encodeURIComponent(statusSelect.value) : '';
                const url = `${suggestEndpoint}?q=${encodeURIComponent(query.trim())}&role=${role}&status=${status}`;

                fetch(url, { credentials: 'same-origin' })
                    .then(res => res.json())
                    .then(data => {
                        if (data && Array.isArray(data.suggestions)) {
                            renderSuggestions(data.suggestions, query);
                        }
                    })
                    .catch(() => {
                        // Keep local fallback matches
                    });
            }, 250);
        });

        // Focus event
        searchInput.addEventListener('focus', function () {
            if (this.value.trim().length > 0) {
                const matches = filterLocalSuggestions(this.value);
                renderSuggestions(matches, this.value);
            }
        });

        // Keyboard navigation in suggestions menu
        searchInput.addEventListener('keydown', function (e) {
            if (!suggestionsMenu || suggestionsMenu.classList.contains('d-none')) {
                return;
            }

            const items = suggestionsMenu.querySelectorAll('.suggest-item');
            if (items.length === 0) return;

            if (e.key === 'ArrowDown') {
                e.preventDefault();
                activeIndex = (activeIndex + 1) % items.length;
                updateActiveItem(items);
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                activeIndex = (activeIndex - 1 + items.length) % items.length;
                updateActiveItem(items);
            } else if (e.key === 'Enter') {
                if (activeIndex >= 0 && items[activeIndex]) {
                    e.preventDefault();
                    items[activeIndex].click();
                }
            } else if (e.key === 'Escape') {
                hideSuggestions();
            }
        });
    }

    function updateActiveItem(items) {
        items.forEach((item, idx) => {
            if (idx === activeIndex) {
                item.classList.add('active');
                item.scrollIntoView({ block: 'nearest' });
            } else {
                item.classList.remove('active');
            }
        });
    }

    // Dismiss suggestions on click outside
    document.addEventListener('click', function (e) {
        if (searchWrapper && !searchWrapper.contains(e.target)) {
            hideSuggestions();
        }
    });
});
</script>
