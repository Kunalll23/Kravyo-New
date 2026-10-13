<?php
require 'c:/xampp/htdocs/Kravyo/config/constants.php';
require 'c:/xampp/htdocs/Kravyo/core/Session.php';
$_SERVER['REQUEST_URI'] = '/delivery/offers/accept';
session_save_path('C:/xampp/tmp');
Session::init();
Session::set('user_id', 53);
Session::set('user_role', 'delivery');
Session::set('delivery_partner_id', 53);
$token = Session::generateCsrfToken();
$cookie = session_name() . '=' . session_id();
session_write_close(); // flush session data to disk before curl

$opts = [
    'http' => [
        'method'  => 'POST',
        'header'  => "Content-type: application/x-www-form-urlencoded\r\n" .
                     "X-Requested-With: XMLHttpRequest\r\n" .
                     "Cookie: $cookie\r\n",
        'content' => '_csrf=' . $token . '&order_id=138',
        'ignore_errors' => true // to get content even on 403
    ]
];
$context = stream_context_create($opts);
$response = file_get_contents('http://localhost/Kravyo/delivery/offers/accept', false, $context);
echo "RESPONSE: " . $response . "\n";
