<?php

namespace App\Providers;

use App\Models\Candidate;
use App\Observers\CandidateObserver;
use App\Services\AdminNotificationService;
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
        Candidate::observe(CandidateObserver::class);

        View::composer(['admin.partials.notification-dropdown', 'partials.notification-dropdown'], function ($view) {
            $user = auth()->user();

            if (! $user) {
                $view->with([
                    'headerNotifications' => collect(),
                    'headerUnreadCount' => 0,
                ]);

                return;
            }

            $service = app(AdminNotificationService::class);

            $view->with([
                'headerNotifications' => $service->recentForUser($user, 5),
                'headerUnreadCount' => $service->unreadCount($user),
            ]);
        });
    }
}
