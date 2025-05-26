<?php

namespace App\Providers;

use App\Events\DebtProcessed;
use App\Listeners\UpdatetotalBalance;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use App\Http\Requests\DebetRequest\UpdateDebetData;

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



    }
}
