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
        // Usamos una sentencia RAW para MySQL ya que Doctrine DBAL no soporta bien el cambio de enums
        if (config('database.default') === 'mysql') {
            DB::statement("ALTER TABLE appointments MODIFY COLUMN status ENUM('pending', 'confirmed', 'completed', 'cancelled', 'rescheduled') DEFAULT 'pending'");
        } else {
            // Para SQLite u otros (aunque el proyecto parece MySQL)
            Schema::table('appointments', function (Blueprint $table) {
                $table->string('status')->default('pending')->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (config('database.default') === 'mysql') {
            DB::statement("ALTER TABLE appointments MODIFY COLUMN status ENUM('pending', 'confirmed', 'completed', 'cancelled') DEFAULT 'pending'");
        } else {
            Schema::table('appointments', function (Blueprint $table) {
                $table->enum('status', ['pending', 'confirmed', 'completed', 'cancelled'])->default('pending')->change();
            });
        }
    }
};
