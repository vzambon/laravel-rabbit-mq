<?php

namespace Vzambon\LaravelRabbitMq\Queue;

class RabbitMqQueueConnector implements \Illuminate\Queue\Connectors\ConnectorInterface
{
    public function connect(array $config)
    {
        return new RabbitMQQueue($config);
    }
}
