<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{

    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        /* COMENTA ESTO PARA QUE SAFARI NO BUSQUE HTTPS EN LOCAL
        if (config('app.env') === 'local') {
            URL::forceScheme('https');
        }
        */
    }
}
