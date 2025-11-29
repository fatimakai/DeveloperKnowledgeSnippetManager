<?php

// Measure Laravel bootstrap time
$start = microtime(true);

// Load autoloader first
$t0 = microtime(true);
require 'vendor/autoload.php';
$t1 = microtime(true);

echo "Autoloader: " . round(($t1 - $t0) * 1000) . "ms\n";

// Start bootstrap timer
$t2 = microtime(true);
$app = require 'bootstrap/app.php';
$t3 = microtime(true);

echo "Bootstrap App: " . round(($t3 - $t2) * 1000) . "ms\n";

// Get kernel
$t4 = microtime(true);
$kernel = $app->make('Illuminate\Contracts\Http\Kernel');
$t5 = microtime(true);

echo "Get HTTP Kernel: " . round(($t5 - $t4) * 1000) . "ms\n";
echo "Total Initialization: " . round(($t5 - $t0) * 1000) . "ms\n";

// Now test a simple request to just get a response without hitting DB
$t6 = microtime(true);
$request = \Illuminate\Http\Request::create('/');
$t7 = microtime(true);

echo "\nRequest Creation: " . round(($t7 - $t6) * 1000) . "ms\n";
