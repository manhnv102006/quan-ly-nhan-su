<?php

namespace App\Providers;

use App\Models\LeaveRequest;
use App\Models\OvertimeRequest;
use App\Policies\LeaveRequestPolicy;
use App\Policies\OvertimeRequestPolicy;
use Illuminate\Support\Facades\Gate;
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
        Gate::policy(OvertimeRequest::class, OvertimeRequestPolicy::class);
        Gate::policy(LeaveRequest::class, LeaveRequestPolicy::class);
    }
}
