<?php

namespace App\Providers;

use Illuminate\Notifications\Notification;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {

        View::composer('layouts.partials.topbar', function ($view) {
            $notifications = DB::table('notifications')
                ->whereRaw('read_at IS NULL and notifiable_id = :id', ['id' => Auth::id()])
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();
                $view->with('notifications', $notifications);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrapFive();
    }
}
