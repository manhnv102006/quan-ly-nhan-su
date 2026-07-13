<?php

namespace App\Providers;

use App\Models\Candidate;
use App\Models\Contract;
use App\Models\LeaveRequest;
use App\Models\OvertimeRequest;
use App\Observers\CandidateObserver;
use App\Policies\ContractPolicy;
use App\Policies\LeaveRequestPolicy;
use App\Policies\OvertimeRequestPolicy;
use App\Services\AdminNotificationService;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Paginator::useBootstrapFive();

        Gate::policy(OvertimeRequest::class, OvertimeRequestPolicy::class);
        Gate::policy(LeaveRequest::class, LeaveRequestPolicy::class);
        Gate::policy(Contract::class, ContractPolicy::class);

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
