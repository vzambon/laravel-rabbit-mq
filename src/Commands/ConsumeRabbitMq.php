<?php

namespace Vzambon\LaravelRabbitMq\Commands;

use Illuminate\Console\Command;
use PhpAmqpLib\Message\AMQPMessage;
use Vzambon\LaravelRabbitMq\RabbitMqConnector;

class ConsumeRabbitMQ extends Command
{
    protected $signature = 'rabbitmq:consume
                            {queue : The name of the queue to consume from}
                            {--max-jobs=100 : The maximum number of jobs to process before stopping}
                            {--max-duration=30 : The maximum duration in seconds to run the consumer}';

    protected $description = 'Consume messages from RabbitMQ queue and stop after processing max jobs or max duration';

    protected $connector;
    protected $connection;
    protected $channel;
    protected $maxJobs;
    protected $maxDuration;

    public function handle()
    {
        
        $queue = $this->argument('queue');
        $this->maxJobs = (int) $this->option('max-jobs');
        $this->maxDuration = (int) $this->option('max-duration');
        
        $this->info("Starting consumer for queue: {$queue}");
        
        $this->connector = new RabbitMqConnector();

        $connection = $this->connector->connect();

        $this->connection = $connection->getConnection();
        $this->channel = $connection->getChannel();

        // Track the start time and number of processed jobs
        $startTime = microtime(true);
        $processedJobs = 0;

        $this->channel->basic_qos(null, 1, null);

        // Define the callback for processing messages
        $this->channel->basic_consume($queue, '', false, true, false, false, function (AMQPMessage $msg) use (&$processedJobs, $startTime) {
            $this->processMessage($msg);
            $processedJobs++;

            // Check if max jobs or duration are reached
            if ($processedJobs >= $this->maxJobs || (microtime(true) - $startTime) >= $this->maxDuration) {
                $this->info("Stopping consumption: Max jobs or duration reached");
                $this->connector->stop();
            }
        });

        // Consume messages until the maximum jobs or duration is reached
        while ($processedJobs < $this->maxJobs && (microtime(true) - $startTime) < $this->maxDuration) {
            $this->channel->wait();
        }
    }

    protected function processMessage(AMQPMessage $msg)
    {
        $this->info("Processing message: " . $msg->getBody());
    }
}
