<?php

namespace Anakadote\BAMLTReferrals;

use Illuminate\Support\ServiceProvider;

class BAMLTReferralsServiceProvider extends ServiceProvider {

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(Anakadote\BAMLTReferrals\Facades\BAMLTReferrals::class, function($app) {
            return new BAMLTReferrals;
        });
        
        $this->app['bamlt-referrals'] = $this->app->make(Anakadote\BAMLTReferrals\Facades\BAMLTReferrals::class);
        
        $this->app->booting(function() {
            $loader = \Illuminate\Foundation\AliasLoader::getInstance();
            $loader->alias('BAMLTReferrals', 'Anakadote\BAMLTReferrals\Facades\BAMLTReferrals');
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [];
    }

}
