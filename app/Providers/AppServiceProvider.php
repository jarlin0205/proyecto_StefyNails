<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \Illuminate\Support\Facades\View::composer('layouts.admin', function ($view) {
            // Contar notificaciones que tengan una cita pendiente O que no hayan sido leídas
            $unreadNotificationsCount = \App\Models\Notification::where('is_read', false)
                ->orWhereHas('appointment', function($query) {
                    $query->where('status', 'pending');
                })
                ->count();
            $services = \App\Models\Service::all();
            $view->with('unreadNotificationsCount', $unreadNotificationsCount)
                 ->with('allServices', $services);
        });
    }
}
