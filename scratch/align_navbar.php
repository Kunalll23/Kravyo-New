<?php
// 1. Update views/partials/header.php
$headerPath = __DIR__ . '/../views/partials/header.php';
$headerContent = file_get_contents($headerPath);

$isCrlf = strpos($headerContent, "\r\n") !== false;
$eol = $isCrlf ? "\r\n" : "\n";

$headerSearch = '    <nav class="navbar navbar-expand-lg navbar-kravyo">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center me-4" href="<?= $_navHome ?>">
                <img src="<?= url(\'/assets/images/kravyo-logo.png\') ?>" alt="Kravyo" height="30" style="object-fit: contain; max-height: 30px; transform: scale(1.8); transform-origin: left center; margin-right: 60px;">
            </a>
            
            <button class="navbar-toggler text-white border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarKravyoContent">
                <i class="bi bi-list fs-2 text-white"></i>
            </button>

            <div class="collapse navbar-collapse" id="navbarKravyoContent">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-4">';

$headerReplace = '    <nav class="navbar navbar-expand-lg navbar-kravyo py-2">
        <div class="container-fluid px-3 px-xl-4">
            <a class="navbar-brand d-flex align-items-center me-2 me-xl-3 py-0" href="<?= $_navHome ?>">
                <img src="<?= url(\'/assets/images/kravyo-logo.png\') ?>" alt="Kravyo" height="32" style="object-fit: contain; max-height: 32px; transform: scale(1.35); transform-origin: left center; margin-right: 20px;">
            </a>
            
            <button class="navbar-toggler text-white border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarKravyoContent">
                <i class="bi bi-list fs-2 text-white"></i>
            </button>

            <div class="collapse navbar-collapse" id="navbarKravyoContent">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0 align-items-lg-center">';

$headerSearchRight = '<div class="d-flex align-items-center gap-3">';
$headerReplaceRight = '<div class="d-flex align-items-center gap-2 gap-xl-3 flex-shrink-0 ms-lg-2">';

if ($isCrlf) {
    $headerSearch = str_replace("\n", "\r\n", $headerSearch);
    $headerReplace = str_replace("\n", "\r\n", $headerReplace);
    $headerSearchRight = str_replace("\n", "\r\n", $headerSearchRight);
    $headerReplaceRight = str_replace("\n", "\r\n", $headerReplaceRight);
}

if (strpos($headerContent, $headerSearch) !== false) {
    $headerContent = str_replace($headerSearch, $headerReplace, $headerContent);
    $headerContent = str_replace($headerSearchRight, $headerReplaceRight, $headerContent);
    file_put_contents($headerPath, $headerContent);
    echo "SUCCESS: header.php updated with container-fluid, tuned logo, and flex alignment.\n";
} else {
    echo "ERROR: Target not found in header.php\n";
    exit(1);
}

// 2. Update public/assets/css/style.css
$cssPath = __DIR__ . '/../public/assets/css/style.css';
$cssContent = file_get_contents($cssPath);
$isCrlfCss = strpos($cssContent, "\r\n") !== false;
$eolCss = $isCrlfCss ? "\r\n" : "\n";

$cssSearch = '/* Custom Navbar */
.navbar-kravyo {
    background-color: var(--kravyo-dark);
    padding: 0.8rem 1rem;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
}

.navbar-kravyo .navbar-brand {
    font-weight: 800;
    font-size: 1.5rem;
    color: var(--kravyo-primary);
    letter-spacing: -0.5px;
}

.navbar-kravyo .nav-link {
    color: #e0e0e0;
    font-weight: 500;
    transition: color 0.2s ease;
}

.navbar-kravyo .nav-link:hover,
.navbar-kravyo .nav-link.active {
    color: var(--kravyo-primary);
}';

$cssReplace = '/* Custom Navbar - Precision Aligned Single-Line Navigation */
.navbar-kravyo {
    background-color: var(--kravyo-dark);
    padding: 0.55rem 1rem;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
}

.navbar-kravyo .navbar-brand {
    font-weight: 800;
    font-size: 1.5rem;
    color: var(--kravyo-primary);
    letter-spacing: -0.5px;
    margin-right: 0.5rem;
}

.navbar-kravyo .navbar-nav {
    display: flex;
    align-items: center;
    flex-wrap: nowrap;
    gap: 2px;
}

.navbar-kravyo .nav-item {
    display: flex;
    align-items: center;
}

.navbar-kravyo .nav-link {
    color: #e2e8f0;
    font-weight: 500;
    font-size: 0.88rem;
    white-space: nowrap !important;
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    padding: 0.45rem 0.65rem !important;
    border-radius: 8px;
    line-height: 1.2;
    transition: all 0.2s ease;
}

.navbar-kravyo .nav-link i {
    font-size: 0.95rem;
    line-height: 1;
    margin-right: 0 !important;
    display: inline-flex;
    align-items: center;
}

.navbar-kravyo .nav-link:hover {
    color: var(--kravyo-primary);
    background: rgba(255, 255, 255, 0.06);
}

.navbar-kravyo .nav-link.active {
    color: var(--kravyo-primary);
    font-weight: 600;
}

@media (min-width: 1200px) {
    .navbar-kravyo .navbar-nav {
        gap: 4px;
    }
    .navbar-kravyo .nav-link {
        font-size: 0.91rem;
        padding: 0.45rem 0.75rem !important;
        gap: 0.4rem;
    }
}

@media (min-width: 1400px) {
    .navbar-kravyo .navbar-nav {
        gap: 6px;
    }
    .navbar-kravyo .nav-link {
        font-size: 0.94rem;
        padding: 0.5rem 0.85rem !important;
        gap: 0.45rem;
    }
}';

if ($isCrlfCss) {
    $cssSearch = str_replace("\n", "\r\n", $cssSearch);
    $cssReplace = str_replace("\n", "\r\n", $cssReplace);
}

if (strpos($cssContent, $cssSearch) !== false) {
    $cssContent = str_replace($cssSearch, $cssReplace, $cssContent);
    file_put_contents($cssPath, $cssContent);
    echo "SUCCESS: style.css updated with single-line whitespace nowrap rules.\n";
} else {
    echo "ERROR: Target not found in style.css\n";
    exit(1);
}
