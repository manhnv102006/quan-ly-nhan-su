<?php

namespace App\Providers;

use App\Models\Candidate;
use App\Observers\CandidateObserver;
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
    }
}
