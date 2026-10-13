<?php
$file = 'c:/xampp/htdocs/Kravyo/views/partials/header.php';
$content = file_get_contents($file);

$target = '                <div class="d-flex align-items-center gap-2 gap-xl-3 flex-shrink-0 ms-lg-2">
                    <?php if (Session::has(\'admin_id\')): ?>';

$replacement = '                <div class="d-flex align-items-center gap-2 gap-xl-3 flex-shrink-0 ms-lg-2">
                    <?php if (empty($_userRole) || $_userRole === ROLE_CUSTOMER || $_sessionCtx === Session::PUBLIC_SESSION): ?>
                        <div class="dropdown me-2">
                            <button class="btn btn-light rounded-pill border-0 d-flex align-items-center gap-2 px-3 py-2 shadow-sm" type="button" data-bs-toggle="dropdown" style="background-color: #f8f9fa; color: #333; font-weight: 500;">
                                <i class="bi bi-geo-alt-fill text-dark"></i>
                                <span><?= sanitize(Session::get(\'selected_city\') ?: \'Surat\') ?></span>
                                <i class="bi bi-chevron-down ms-1" style="font-size: 0.8em; color: #888;"></i>
                            </button>
                            <ul class="dropdown-menu shadow border-0" style="border-radius: 12px;">
                                <li><a class="dropdown-item py-2" href="javascript:void(0)" onclick="setGlobalCity(\'Surat\')"><i class="bi bi-geo-alt text-danger me-2"></i> Surat</a></li>
                                <li><a class="dropdown-item py-2" href="javascript:void(0)" onclick="setGlobalCity(\'Ahmedabad\')"><i class="bi bi-geo-alt text-danger me-2"></i> Ahmedabad</a></li>
                                <li><a class="dropdown-item py-2" href="javascript:void(0)" onclick="setGlobalCity(\'Vadodara\')"><i class="bi bi-geo-alt text-danger me-2"></i> Vadodara</a></li>
                            </ul>
                        </div>
                        <a href="<?= url(\'/kitchens\') ?>" class="btn btn-link text-white p-0 border-0 me-2" title="Search"><i class="bi bi-search fs-5"></i></a>
                        <a href="<?= url(\'/cart\') ?>" class="btn btn-link text-white position-relative p-0 border-0 me-2" title="Cart">
                            <i class="bi bi-cart3 fs-5"></i>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size:0.6rem;"><?= isset($_SESSION[\'cart\']) ? count($_SESSION[\'cart\']) : 0 ?></span>
                        </a>
                        <script>
                        function setGlobalCity(city) {
                            fetch(\'<?= url(\'/api/set-city\') ?>\', {
                                method: \'POST\',
                                headers: { \'Content-Type\': \'application/x-www-form-urlencoded\' },
                                body: \'city=\' + encodeURIComponent(city) + \'&_csrf=<?= csrf_token() ?>\'
                            }).then(() => window.location.reload());
                        }
                        </script>
                    <?php endif; ?>
                    <?php if (Session::has(\'admin_id\')): ?>';

$content = str_replace($target, $replacement, $content);
// handle carriage returns just in case
$target_crlf = str_replace("\n", "\r\n", $target);
$replacement_crlf = str_replace("\n", "\r\n", $replacement);
$content = str_replace($target_crlf, $replacement_crlf, $content);

file_put_contents($file, $content);
echo "Header updated successfully.\n";
