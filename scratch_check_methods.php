<?php
require 'core/Model.php';
require 'app/models/Kitchen.php';
$class = new ReflectionClass('Kitchen');
foreach ($class->getMethods() as $method) {
    echo $method->getName() . PHP_EOL;
}
