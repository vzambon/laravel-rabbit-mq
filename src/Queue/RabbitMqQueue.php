<?php

namespace Vzambon\LaravelRabbitMq\Queue;

use Illuminate\Queue\Queue;
use Illuminate\Contracts\Queue\Queue as QueueContract;
use PhpAmqpLib\Message\AMQPMessage;
use Vzambon\LaravelRabbitMq\RabbitMqConnector;

class RabbitMqQueue extends Queue implements QueueContract
{
    protected $connection;
    public $channel;
    protected $queue;

    public function __construct()
    {
        $connection = (new RabbitMqConnector())->connect();

        $this->connection = $connection->getConnection();
        
        $this->channel = $connection->getChannel();
        $this->queue = $connection->getDefaultQueue();

        $this->channel->queue_declare($this->queue, false, true, false, false);
    }

    public function size($queue = null)
    {
        $queue = $queue ?: $this->queue;
        $result = $this->channel->queue_declare($queue, true);
        return $result[1];
    }

    public function push($job, $data = '', $queue = null)
    {
        $queue = $queue ?: $this->queue;
        $payload = $this->createPayload($job, $data);
        $message = new AMQPMessage($payload, ['delivery_mode' => 2]);
        $this->channel->basic_publish($message, '', $queue);

        $this->close();
    }

    public function pushRaw($payload, $queue = null, array $options = [])
    {
        $queue = $queue ?: $this->queue;
        $message = new AMQPMessage($payload, ['delivery_mode' => 2]);
        $this->channel->basic_publish($message, '', $queue);

        $this->close();
    }

    public function later($delay, $job, $data = '', $queue = null)
    {
        $queue = $queue ?: $this->queue;
        $payload = $this->createPayload($job, $data);
        
        $message = new AMQPMessage($payload, [
            'delivery_mode' => 2,
            'expiration' => (string) ($delay * 1000), // Convert delay to milliseconds
        ]);
        
        $this->channel->basic_publish($message, '', $queue);
    }

    public function pop($queue = null)
    {
        $queue = $queue ?: $this->queue;
        $message = $this->channel->basic_get($queue);

        if ($message) {
            return new RabbitMQJob($this->container, $this, $message);
        }
    }

    protected function close()
    {
        $this->channel->close();
        $this->connection->close();
    }
}
