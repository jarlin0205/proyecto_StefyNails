<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Service;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Iterate through all services
        $services = Service::all();

        foreach ($services as $service) {
            if ($service->price) {
                // If price exists and is numeric
                $numericPrice = floatval($service->price);
                
                // Example logic:
                // 45000 -> 45 -> $45k
                // 20000 -> 20 -> $20k
                // 1500 -> 1.5 -> $1.5k
                
                $thousands = $numericPrice / 1000;
                
                // Format: If whole number, show no decimals. If decimal, show up to 1 decimal place.
                // Using round to verify if it is integer
                if (round($thousands) == $thousands) {
                    $formatted = number_format($thousands, 0);
                } else {
                    $formatted = number_format($thousands, 1);
                     // Remove trailing .0 if any, just in case logic fails or future PHP version behaves oddly
                     $formatted = rtrim(rtrim($formatted, '0'), '.');
                }

                $service->price_display = '$' . $formatted . 'k';
                $service->save();
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No easy way to revert exactly without previous values, but we can leave it empty
        // or set it back to just the number if desired. For now, empty is safer.
    }
};
