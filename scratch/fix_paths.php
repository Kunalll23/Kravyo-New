<?php

$replacements = [
    // customer/order_track.php (dishes)
    'views/customer/order_track.php' => [
        "url('/public/uploads/' . \$item['image'])" => "UPLOAD_URL . '/dishes/' . \$item['image']"
    ],
    // customer/menu.php (dishes)
    'views/customer/menu.php' => [
        "url('/public/uploads/' . \$dish['image'])" => "UPLOAD_URL . '/dishes/' . \$dish['image']"
    ],
    // customer/kitchen_detail.php (kitchen banner + dishes)
    'views/customer/kitchen_detail.php' => [
        "url('/public/uploads/' . \$kitchen['banner_image'])" => "UPLOAD_URL . '/kitchens/' . \$kitchen['banner_image']",
        "url('/public/uploads/' . \$item['image'])" => "UPLOAD_URL . '/dishes/' . \$item['image']"
    ],
    // customer/dish_detail.php (dishes)
    'views/customer/dish_detail.php' => [
        "url('/public/uploads/' . \$dish['image'])" => "UPLOAD_URL . '/dishes/' . \$dish['image']"
    ],
    // customer/cart.php (dishes)
    'views/customer/cart.php' => [
        "url('/public/uploads/' . \$item['image'])" => "UPLOAD_URL . '/dishes/' . \$item['image']"
    ],
    // chef/profile.php (kitchens)
    'views/chef/profile.php' => [
        "url('/public/uploads/kitchens/' . \$kitchen['hygiene_certificate_image'])" => "UPLOAD_URL . '/kitchens/' . \$kitchen['hygiene_certificate_image']",
        "url('/public/uploads/kitchens/' . \$kitchen['banner_image'])" => "UPLOAD_URL . '/kitchens/' . \$kitchen['banner_image']"
    ],
    // chef/menu.php (dishes)
    'views/chef/menu.php' => [
        "url('/public/uploads/dishes/' . \$dish['image'])" => "UPLOAD_URL . '/dishes/' . \$dish['image']"
    ],
    // chef/dashboard.php (kitchens)
    'views/chef/dashboard.php' => [
        "url('/public/uploads/kitchens/' . \$kitchen['banner_image'])" => "UPLOAD_URL . '/kitchens/' . \$kitchen['banner_image']"
    ],
    // admin/chefs.php (kitchens)
    'views/admin/chefs.php' => [
        "url('/public/uploads/kitchens/' . \$kitchen['banner_image'])" => "UPLOAD_URL . '/kitchens/' . \$kitchen['banner_image']",
        "url('/public/uploads/kitchens/' . \$kitchen['hygiene_certificate_image'])" => "UPLOAD_URL . '/kitchens/' . \$kitchen['hygiene_certificate_image']"
    ],
    // admin/categories.php (categories)
    'views/admin/categories.php' => [
        "url('/public/uploads/categories/' . \$cat['image'])" => "UPLOAD_URL . '/categories/' . \$cat['image']"
    ]
];

foreach ($replacements as $file => $rules) {
    $path = __DIR__ . '/../' . $file;
    if (file_exists($path)) {
        $content = file_get_contents($path);
        foreach ($rules as $search => $replace) {
            $content = str_replace($search, $replace, $content);
        }
        file_put_contents($path, $content);
        echo "Fixed $file\n";
    } else {
        echo "File not found: $file\n";
    }
}
echo "Done.\n";
