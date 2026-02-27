<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Schema;

echo "Appointments columns:\n";
print_r(Schema::getColumnListing('appointments'));

echo "\nExpenses columns:\n";
print_r(Schema::getColumnListing('expenses'));

echo "\nMigration status:\n";
\Illuminate\Support\Facades\Artisan::call('migrate:status');
echo \Illuminate\Support\Facades\Artisan::output();
