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
        if (!Schema::hasColumn('appointments', 'professional_id')) {
            Schema::table('appointments', function (Blueprint $table) {
                $table->foreignId('professional_id')->nullable()->after('service_id')->constrained()->onDelete('set null');
            });
        }

        if (!Schema::hasColumn('availabilities', 'professional_id')) {
            Schema::table('availabilities', function (Blueprint $table) {
                $table->foreignId('professional_id')->nullable()->after('id')->constrained()->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('availabilities', function (Blueprint $table) {
            if (Schema::hasColumn('availabilities', 'professional_id')) {
                $table->dropForeign(['professional_id']);
                $table->dropColumn('professional_id');
            }
        });

        Schema::table('appointments', function (Blueprint $table) {
            if (Schema::hasColumn('appointments', 'professional_id')) {
                $table->dropForeign(['professional_id']);
                $table->dropColumn('professional_id');
            }
        });
    }
};
