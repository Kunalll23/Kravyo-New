<div class="container py-4">
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



    <div class="card kravyo-card border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">User</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Joined</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($users)): ?>
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">
                                    <i class="bi bi-person-x display-4 d-block mb-3 opacity-50"></i>
                                    No users found.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td class="ps-4 py-3">
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="avatar-circle">
                                                <i class="bi bi-person"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-0 fw-semibold"><?= sanitize($user['full_name'] ?? 'Unknown User') ?></h6>
                                                <small class="text-muted"><?= sanitize($user['email'] ?? '') ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if ($user['role'] === 'admin'): ?>
                                            <span class="badge bg-danger">Admin</span>
                                        <?php elseif ($user['role'] === 'chef'): ?>
                                            <span class="badge bg-warning text-dark">Chef</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Customer</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($user['status'] === 'active'): ?>
                                            <span class="badge bg-success bg-opacity-10 text-success">Active</span>
                                        <?php elseif ($user['status'] === 'suspended'): ?>
                                            <span class="badge bg-danger bg-opacity-10 text-danger">Suspended</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary bg-opacity-10 text-secondary"><?= ucfirst(sanitize($user['status'] ?? 'unknown')) ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <small class="text-muted"><?= date('d M Y', strtotime($user['created_at'])) ?></small>
                                    </td>
                                    <td class="text-end pe-4">
                                        <?php if ($user['id'] !== Session::get('user_id')): ?>
                                            <form action="<?= url('/admin/user/toggle') ?>" method="POST" class="d-inline">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                                
                                                <?php if ($user['status'] === 'active'): ?>
                                                    <input type="hidden" name="status" value="suspended">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to suspend this user?');">
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
