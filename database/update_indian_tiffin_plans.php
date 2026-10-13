<?php
define('ROOT_PATH', 'c:/xampp/htdocs/Kravyo');
define('APP_PATH', ROOT_PATH . '/app');
define('CORE_PATH', ROOT_PATH . '/core');
define('CONFIG_PATH', ROOT_PATH . '/config');

require_once ROOT_PATH . '/config/constants.php';
require_once CORE_PATH . '/Database.php';

$db = Database::getInstance();

$updates = [
    1 => [
        'plan_name' => 'Hyderabadi Shahi Dawat Lunch',
        'description' => 'Freshly packed Hyderabadi noon meals featuring aromatic bagara rice or pulao, phulkas, comforting khatti dal, seasonal Nizami veg korma, crisp fryums, and homemade curd.'
    ],
    2 => [
        'plan_name' => 'Dilliwali Swadist Thali Duo',
        'description' => 'Wholesome North Indian fare with 4 tava rotis, rich dal makhani or rajma, homestyle subzi, jeera rice, pickle, and fresh kachumber salad for lunch and dinner.'
    ],
    3 => [
        'plan_name' => 'Rajputana Desi Ghee Thali',
        'description' => 'Authentic Rajasthani midday meal prepared with pure desi ghee. Includes handmade rotis, gatte ki sabzi or ker sangri, panchmel dal, steamed basmati rice, and mint-coriander chutney.'
    ],
    4 => [
        'plan_name' => 'Aamchi Mumbai Purna Brahma Lunch',
        'description' => 'Everyday Maharashtrian nutritious lunch featuring soft chapati, daily varan bhaat with ghee, usal or bhaji (batata, bhindi, methi), fresh koshimbir, and roasted papad.'
    ],
    5 => [
        'plan_name' => 'Dakshin Swaad Traditional Meals',
        'description' => 'South Indian comfort lunch with steamed ponni rice, authentic Madras sambar, tangy rasam, traditional poriyal, kootu, fresh appalam, and set curd.'
    ],
    6 => [
        'plan_name' => 'Mithila Desi Rasoi Combo',
        'description' => 'Homestyle Bihari 2-meal daily plan featuring wheat rotis, sattu-infused delicacies, flavorful chana dal, aloo bhujia, baingan bharta, and jeera rice.'
    ],
    7 => [
        'plan_name' => 'Marwadi Rasoi Ghar Ka Thaal',
        'description' => 'Month-long balanced Marwadi lunch package with wholesome phulkas, dal baati accompaniment varieties, seasonal greens, boondi raita, and sweet gur.'
    ],
    8 => [
        'plan_name' => 'Kathiyawadi Shak & Rotla Diet',
        'description' => 'Traditional Gujarati home-cooked meals featuring hot phulkas or bajra rotla, sev tameta or ringna no olo, sweet-and-sour Gujarati dal, kadhi khichdi, and chaas.'
    ],
    9 => [
        'plan_name' => 'Andhra Bhojanam Daily Thali',
        'description' => 'Spiced and comforting Telugu-style noon meal with hot rice, pappu (dal), gongura pachadi, dry fry vellulli subzi, curd, and papadum.'
    ],
    10 => [
        'plan_name' => 'Lakhnawi Nawabi Zaika Package',
        'description' => 'Rich Awadhi delicacies delivered twice daily. Includes soft ulte tawe ke parathe, paneer lababdar, dal sultani, fragrant dum rice, and cooling raita.'
    ],
    11 => [
        'plan_name' => 'Surati Shuddh Shakahari Tiffin',
        'description' => 'Surat-special pure veg 2-meal box with 4 soft butter phulkas, Gujarati rasawala subzi, aromatic dal-bhat, fresh kachumber, and spiced masala chaas.'
    ],
    12 => [
        'plan_name' => 'Sampoorna Poshan Mahina Thali',
        'description' => 'Complete monthly home nutrition with changing daily menus of paneer, dal tadka, mixed veg handi, multigrain chapatis, jeera rice, and buttermilk.'
    ],
    13 => [
        'plan_name' => 'Maharaja 3-Prahari Amrit Aahar',
        'description' => 'Complete 3-meal daily luxury plan: Hot morning poha, upma or parathas with chai, lavish afternoon royal thali with sweet, and light digestable dinner.'
    ],
    14 => [
        'plan_name' => 'Kisan Rasoi Mini Lunch Box',
        'description' => 'Budget-friendly everyday wholesome lunch: 3 hot wheat rotis, daily seasonal sukhi sabzi, homestyle dal fry, and steaming plain rice.'
    ],
    15 => [
        'plan_name' => 'Shahi Paneer Deluxe Tiffin',
        'description' => 'Premium vegetarian lunch featuring fresh malai paneer sabzi, 4 ghee-brushed phulkas, yellow dal tadka, aromatic jeera basmati, and fresh salad.'
    ],
    16 => [
        'plan_name' => 'Maa Ke Haath Ka Swaad Special',
        'description' => 'Pure comfort home dining for lunch and dinner: Freshly rolled phulkas, comforting khichdi-kadhi or dal-chawal, seasonal dry subzi, and homemade mango pickle.'
    ],
    17 => [
        'plan_name' => 'Punjabi Pind Di Rasoi Feast',
        'description' => 'Hearty North Indian lunch & dinner tiffin with 4 tawa rotis, authentic sarson or dal tadka, pindi chole or shahi paneer, jeera rice, and crunchy lachha pyaz.'
    ]
];

$stmt = $db->prepare("UPDATE tiffin_subscriptions SET plan_name = :plan_name, description = :description WHERE id = :id");

foreach ($updates as $id => $data) {
    $stmt->execute([
        'id' => $id,
        'plan_name' => $data['plan_name'],
        'description' => $data['description']
    ]);
    echo "Updated Plan #$id: {$data['plan_name']}\n";
}

echo "\nAll 17 tiffin plans updated successfully with unique Indian-based names & descriptions!\n";
