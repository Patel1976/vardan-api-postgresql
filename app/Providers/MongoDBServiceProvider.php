<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use MongoDB\Client as MongoClient;

class MongoDBServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('mongodb', function ($app) {
            // Retrieve MongoDB URL from environment or configuration
            $url = env('MONGODB_URL', 'mongodb://localhost:27017');

            // Optional: You can parse the URL to extract database name, username, password, etc.
            $parsedUrl = parse_url($url);

            $options = [
                'username' => isset($parsedUrl['user']) ? $parsedUrl['user'] : null,
                'password' => isset($parsedUrl['pass']) ? $parsedUrl['pass'] : null,
            ];

            return new MongoClient($url, $options);
        });
    }
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
