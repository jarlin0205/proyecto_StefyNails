<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Appointment;

$appointments = Appointment::with('service')->latest()->take(50)->get();
$fp = fopen('debug_appointments.csv', 'w');
fputcsv($fp, ['ID', 'Status', 'Service', 'Date']);
foreach($appointments as $a) {
    fputcsv($fp, [$a->id, $a->status, ($a->service ? $a->service->name : 'N/A'), $a->appointment_date]);
}
fclose($fp);
echo "Wrote " . count($appointments) . " appointments to debug_appointments.csv\n";
