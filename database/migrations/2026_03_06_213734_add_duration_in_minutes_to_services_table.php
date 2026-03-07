<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->integer('duration_in_minutes')->default(60)->after('duration');
        });

        // Populate existing records
        $services = DB::table('services')->get();
        foreach ($services as $service) {
            $minutes = $this->parseDuration($service->duration);
            DB::table('services')->where('id', $service->id)->update([
                'duration_in_minutes' => $minutes
            ]);
        }
    }

    private function parseDuration($duration)
    {
        $duration = strtolower((string)$duration);
        $minutes = 0;

        if (!$duration) return 60;

        if (preg_match('/(\d+(\.\d+)?)\s*(hora|h)/', $duration, $matches)) {
            $minutes += (float)$matches[1] * 60;
        }
        
        if (preg_match('/(\d+)\s*(min|m)/', $duration, $matches)) {
             $minutes += (int)$matches[1];
        }
        
        if ($minutes == 0) {
             if (is_numeric($duration)) {
                 $minutes = (int)$duration;
             } else {
                 preg_match('/(\d+)/', $duration, $matches);
                 $minutes = isset($matches[1]) ? (int)$matches[1] : 60;
             }
        }

        return (int)$minutes;
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn('duration_in_minutes');
        });
    }
};
