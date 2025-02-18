<?php

namespace Vzambon\LaravelRabbitMq\Queue;

use Illuminate\Queue\Jobs\Job;
use PhpAmqpLib\Message\AMQPMessage;

class RabbitMqJob extends Job
{
    public function __construct($container, protected RabbitMqQueue $rabbitmq, protected AMQPMessage $message)
    {
        $this->container = $container;
    }

    public function getRawBody()
    {
        return $this->message->body;
    }

    public function getJobId()
    {
        return $this->message->get('message_id') ?? null;
    }

    public function delete()
    {
        $this->rabbitmq->channel->basic_ack($this->message->delivery_info['delivery_tag']);
    }
}