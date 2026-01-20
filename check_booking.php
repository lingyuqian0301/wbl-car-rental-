<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$b = App\Models\Booking::find(2);
echo "Booking 2: " . ($b ? "Found" : "Not Found") . PHP_EOL;
if ($b) {
    echo "Payments: " . $b->payments()->count() . PHP_EOL;
    echo "Deposit: " . $b->deposit_amount . PHP_EOL;
    echo "Customer: " . ($b->customer && $b->customer->user ? $b->customer->user->name : "N/A") . PHP_EOL;
}


