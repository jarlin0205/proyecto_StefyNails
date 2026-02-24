<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\User;
use App\Models\Professional;
use App\Models\Service;
use App\Models\Category;
use App\Models\Appointment;
use App\Models\Expense;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Limpiar Usuarios
        User::all()->each(function ($user) {
            $user->name = $user->name;
            $user->save();
        });

        // Limpiar Profesionales
        Professional::all()->each(function ($prof) {
            $prof->name = $prof->name;
            $prof->save();
        });

        // Limpiar Servicios
        Service::all()->each(function ($service) {
            $service->name = $service->name;
            $service->save();
        });

        // Limpiar Categorías
        Category::all()->each(function ($cat) {
            $cat->name = $cat->name;
            $cat->save();
        });

        // Limpiar Citas
        Appointment::all()->each(function ($app) {
            $app->customer_name = $app->customer_name;
            if ($app->notes) $app->notes = $app->notes;
            if ($app->reschedule_reason) $app->reschedule_reason = $app->reschedule_reason;
            $app->save();
        });

        // Limpiar Gastos
        Expense::all()->each(function ($expense) {
            $expense->description = $expense->description;
            $expense->save();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No hay vuelta atrás fácil para el formato de texto, 
        // pero no es un cambio destructivo.
    }
};
