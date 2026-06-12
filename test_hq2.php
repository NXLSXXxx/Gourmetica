<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$c = new \App\Http\Controllers\Admin\SaleController();
$ref = new ReflectionMethod($c, 'getLiveOrdersData');
$ref->setAccessible(true);
$data = $ref->invokeArgs($c, [2]);

echo json_encode($data, JSON_PRETTY_PRINT);
