<?php

namespace Vzambon\LaravelDiscordLogging;

use Illuminate\Queue\QueueManager;
use Illuminate\Support\ServiceProvider;
use Vzambon\LaravelRabbitMq\Queue\RabbitMqQueueConnector;

class RabbitMqServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/config/rabbitmq.php' => config_path('rabbitmq.php'),
        ], 'config');

        $this->app[QueueManager::class]->addConnector('rabbitmq', function () {
            return $this->app->make(RabbitMqQueueConnector::class);
        });
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/rabbitmq.php',
            'queue.connections.rabbitmq'
        );
    }
}