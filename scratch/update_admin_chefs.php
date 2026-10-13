<?php
$filePath = __DIR__ . '/../views/admin/chefs.php';
$content = file_get_contents($filePath);

$isCrlf = strpos($content, "\r\n") !== false;
$eol = $isCrlf ? "\r\n" : "\n";

$search = '                            <div class="col-6">
                                <i class="bi bi-patch-check text-muted me-1"></i>
                                <strong>Hygiene:</strong>
                                <?php if ($kitchen[\'hygiene_badge\'] === \'verified\'): ?>
                                    <span class="badge-hygiene">Verified</span>
                                <?php else: ?>
                                    <span class="text-muted">Unverified</span>
                                <?php endif; ?>
                            </div>
                        </div>';

$replace = '                            <div class="col-6">
                                <i class="bi bi-patch-check text-muted me-1"></i>
                                <strong>Hygiene:</strong>
                                <?php if ($kitchen[\'hygiene_badge\'] === \'verified\'): ?>
                                    <span class="badge-hygiene">Verified</span>
                                <?php else: ?>
                                    <span class="text-muted">Unverified</span>
                                <?php endif; ?>
                            </div>
                            <div class="col-12 mt-1">
                                <i class="bi bi-pin-map text-muted me-1"></i>
                                <strong>Map Coordinates:</strong>
                                <?php if (!empty($kitchen[\'latitude\']) && !empty($kitchen[\'longitude\'])): ?>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle py-1 px-2">
                                        <?= (float)$kitchen[\'latitude\'] ?>, <?= (float)$kitchen[\'longitude\'] ?>
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle py-1 px-2">Not set yet</span>
                                <?php endif; ?>
                            </div>
                        </div>';

if ($isCrlf) {
    $search = str_replace("\n", "\r\n", $search);
    $replace = str_replace("\n", "\r\n", $replace);
}

$content = str_replace($search, $replace, $content);
file_put_contents($filePath, $content);
echo "SUCCESS: admin/chefs.php updated.\n";
