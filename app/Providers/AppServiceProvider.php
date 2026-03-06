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

            // Generate/Update low stock notifications for admin
            if ($user->role === 'admin') {
                // 1. Find low stock products
                $lowStockProducts = \App\Models\Product::where('stock', '<', 5)->get();
                $processedProductIds = [];

                foreach ($lowStockProducts as $product) {
                    $processedProductIds[] = $product->id;
                    
                    // Find all existing notifications for this product to dedup
                    $productNotifications = \App\Models\Notification::where('product_id', $product->id)->get();
                    
                    $msg = 'El stock de este producto es de ' . $product->stock . ' unidades. Se recomienda hacer pedido.';
                    
                    if ($productNotifications->isEmpty()) {
                        \App\Models\Notification::create([
                            'product_id' => $product->id,
                            'title'      => 'Stock Bajo: ' . $product->name,
                            'message'    => $msg,
                            'type'       => 'warning',
                            'action_url' => route('admin.products.index')
                        ]);
                    } else {
                        // Keep the first one and delete any duplicates
                        $notification = $productNotifications->shift();
                        foreach ($productNotifications as $dup) {
                            $dup->delete();
                        }
                        
                        // Update the message of the remaining one if it changed
                        if ($notification->message !== $msg) {
                            $notification->update(['message' => $msg]);
                        }
                    }
                }

                // 2. Delete notifications for products that are no longer low stock
                \App\Models\Notification::whereNotNull('product_id')
                    ->whereNotIn('product_id', $processedProductIds)
                    ->delete();
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
