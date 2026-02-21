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
            $user = auth()->user();
            $query = \App\Models\Notification::where('is_read', false);

            if ($user && $user->role === 'employee' && $user->professional) {
                $query->whereHas('appointment', function($q) use ($user) {
                    $q->where('professional_id', $user->professional->id);
                });
            }

            $unreadNotificationsCount = $query->count();
            $services = \App\Models\Service::all();
            $view->with('unreadNotificationsCount', $unreadNotificationsCount)
                 ->with('allServices', $services);
        });
    }
}
