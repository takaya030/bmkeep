<?php

namespace App\Providers;

/**
 * @author     Dariusz Prz?da <artdarek@gmail.com>
 * @copyright  Copyright (c) 2013
 * @license    http://www.opensource.org/licenses/mit-license.html MIT License
 */

use Illuminate\Support\ServiceProvider;

class OAuthServiceProvider extends ServiceProvider {
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
        $this->publishes([
            __DIR__ . '/../../config/config.php' => config_path('oauth-8-laravel.php'),
        ], 'config');
    }
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        // Register 'oauth'
        $this->app->singleton(\Takaya030\OAuth\OAuth::class, function ($app) {
            // create oAuth instance
            $oauth = new \Takaya030\OAuth\OAuth();

			// register custom service
			$oauth->registerService('HatenaBookmark', \App\Models\OAuth\Service\HatenaBookmark::class);

            // return oAuth instance
            return $oauth;
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
