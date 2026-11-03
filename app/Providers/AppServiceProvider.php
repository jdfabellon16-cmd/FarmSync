<?php

namespace App\Providers;

use App\Models\Farm;
use App\Models\Field;
use App\Policies\FarmPolicy;
use App\Policies\FieldPolicy;
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
        Gate::policy(Farm::class, FarmPolicy::class);
        Gate::policy(Field::class, FieldPolicy::class);
    }
}
