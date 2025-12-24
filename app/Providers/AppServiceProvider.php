<?php
namespace App\Providers;

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
        $baseUrl = config('app.url'); // https://pr9webhub.praram9.com/chkup

        if (! empty($baseUrl)) {
            \URL::forceRootUrl($baseUrl);
        }

        if (config('app.env') !== 'local') {
            \URL::forceScheme('https');
        }
    }
}
