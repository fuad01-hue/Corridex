<?php

namespace App\Providers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
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
        View::composer('*', function ($view) {
            $unreadNotificationsCount = 0;
            if (Auth::check()) {
                $unreadNotificationsCount = Auth::user()->notifications()->where('is_read', false)->count();
            }
            $view->with('unreadNotificationsCount', $unreadNotificationsCount);
        });
    }
}
