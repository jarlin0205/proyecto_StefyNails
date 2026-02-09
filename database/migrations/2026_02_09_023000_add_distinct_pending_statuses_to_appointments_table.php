<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add pending_admin and pending_client to the enum
        if (config('database.default') === 'mysql') {
            DB::statement("ALTER TABLE appointments MODIFY COLUMN status ENUM('pending', 'confirmed', 'completed', 'cancelled', 'rescheduled', 'pending_admin', 'pending_client') DEFAULT 'pending_admin'");
            
            // Migrate existing data
            DB::table('appointments')->where('status', 'pending')->update(['status' => 'pending_admin']);
            DB::table('appointments')->where('status', 'rescheduled')->update(['status' => 'pending_client']);
        } else {
            Schema::table('appointments', function (Blueprint $table) {
                $table->string('status')->default('pending_admin')->change();
            });
            
            DB::table('appointments')->where('status', 'pending')->update(['status' => 'pending_admin']);
            DB::table('appointments')->where('status', 'rescheduled')->update(['status' => 'pending_client']);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (config('database.default') === 'mysql') {
            // Revert data
            DB::table('appointments')->where('status', 'pending_admin')->update(['status' => 'pending']);
            DB::table('appointments')->where('status', 'pending_client')->update(['status' => 'rescheduled']);
            
            DB::statement("ALTER TABLE appointments MODIFY COLUMN status ENUM('pending', 'confirmed', 'completed', 'cancelled', 'rescheduled') DEFAULT 'pending'");
        } else {
            DB::table('appointments')->where('status', 'pending_admin')->update(['status' => 'pending']);
            DB::table('appointments')->where('status', 'pending_client')->update(['status' => 'rescheduled']);
            
            Schema::table('appointments', function (Blueprint $table) {
                $table->string('status')->default('pending')->change();
            });
        }
    }
};
