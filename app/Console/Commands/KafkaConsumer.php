<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Junges\Kafka\Facades\Kafka;
use App\Events\RegistrantDataReceived;
use Junges\Kafka\Contracts\KafkaConsumerMessage;

class KafkaConsumer extends Command
{
    protected $signature = 'kafka:consume';
    protected $description = 'Consume messages from Kafka topics';

    public function handle()
    {
        $consumer = Kafka::createConsumer(['registrant-created'])
            ->withHandler(function (KafkaConsumerMessage $message) {
                event(new RegistrantDataReceived(json_encode($message->getBody())));
                $this->info('Received message: ' . json_encode($message->getBody()));
            })->build();

        $consumer->consume();
    }
}