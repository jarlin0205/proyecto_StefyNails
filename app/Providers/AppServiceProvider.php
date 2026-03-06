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
            if (!$user) return;

            // Generate low stock notifications for admin
            if ($user->role === 'admin') {
                $lowStockProducts = \App\Models\Product::where('stock', '<', 5)->get();
                foreach ($lowStockProducts as $product) {
                    $exists = \App\Models\Notification::where('product_id', $product->id)
                        ->where('is_read', false)
                        ->exists();
                    if (!$exists) {
                        \App\Models\Notification::create([
                            'product_id' => $product->id,
                            'title'      => 'Stock Bajo: ' . $product->name,
                            'message'    => 'El stock de este producto es de ' . $product->stock . ' unidades. Se recomienda hacer pedido.',
                            'type'       => 'warning',
                            'action_url' => route('admin.products.index')
                        ]);
                    }
                }
            }

            // Count unread notifications
            // We show all unread for admin, or filtered for employees
            $query = \App\Models\Notification::where('is_read', false);

            // If it has an appointment, only show if it needs attention
            $query->where(function($q) {
                $q->whereNull('appointment_id')
                  ->orWhereHas('appointment', function($sub) {
                      $sub->whereIn('status', ['pending_admin', 'pending_client']);
                  });
            });

            if ($user->role === 'employee' && $user->professional) {
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
