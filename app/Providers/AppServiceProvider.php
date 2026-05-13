<?php

namespace App\Providers;

use App\Models\DocumentStandard;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

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
        View::composer('partials.sidebar', function ($view): void {
            $view->with('documentStandards', DocumentStandard::query()->orderBy('order_number')->orderBy('name')->get());
        });
    }
}
