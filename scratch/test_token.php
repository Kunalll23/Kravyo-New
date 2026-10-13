<?php
require_once 'c:/xampp/htdocs/Kravyo/core/Session.php';

Session::init();

$token = Session::generateCsrfToken();
echo "Generated Token: $token\n";
