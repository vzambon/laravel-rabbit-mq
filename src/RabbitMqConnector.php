<?php

namespace Vzambon\LaravelRabbitMq;

use Illuminate\Support\Facades\Config;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;

class RabbitMqConnector
{
    /**
     * @var AMQPStreamConnection
     */
    protected $connection;

    /**
     * @var mixed
     */
    protected $config;

    /**
     * @var \PhpAmqpLib\Channel\AMQPChannel
     */
    protected $channel;

    public function __construct()
    {
        $this->config = Config::get('rabbitmq');
    }

    public function connect()
    {
        $this->connection = new AMQPStreamConnection(
            $this->config['host'],
            $this->config['port'],
            $this->config['user'],
            $this->config['password'],
            $this->config['vhost']
        );

        $this->channel = $this->connection->channel();

        return $this;
    }

    public function getConnection(): AMQPStreamConnection
    {
        return $this->connection;
    }

    public function getChannel(): AMQPChannel
    {
        return $this->channel;
    }

    public function getDefaultQueue()
    {
        return $this->config['queue'];
    }

    public function close()
    {
        $this->channel->close();
        $this->connection->close();
    }

}