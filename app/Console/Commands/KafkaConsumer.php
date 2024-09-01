<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Junges\Kafka\Facades\Kafka;
use App\Events\MemberDataReceived;
use App\Events\RegistrantDataReceived;
use Junges\Kafka\Contracts\KafkaConsumerMessage;

class KafkaConsumer extends Command
{
    protected $signature = 'kafka:consume';
    protected $description = 'Consume messages from Kafka topics';

    public function handle()
    {
        // $consumerRegistrant = Kafka::createConsumer(['registrant-created'])
        //     ->withHandler(function (KafkaConsumerMessage $message) {
        //         event(new RegistrantDataReceived(json_encode($message->getBody())));
        //         $this->info('Received message: ' . json_encode($message->getBody()));
        //     })->build();        

        // $consumerMember = Kafka::createConsumer(['member-created'])
        //     ->withHandler(function (KafkaConsumerMessage $message) {
        //         event(new MemberDataReceived(json_encode($message->getBody())));
        //         $this->info('Received message: ' . json_encode($message->getBody()));
        //     })->build();

        // $consumerRegistrant->consume();
        // $consumerMember->consume();

        $consumer = Kafka::createConsumer(['registrant-created', 'member-created'])
            ->withHandler(function (KafkaConsumerMessage $message) {
                if ($message->getTopicName() === 'registrant-created') {
                    event(new RegistrantDataReceived(json_encode($message->getBody())));
                } elseif ($message->getTopicName() === 'member-created') {
                    event(new MemberDataReceived(json_encode($message->getBody())));
                }
                $this->info('Received message from ' . $message->getTopicName() . ': ' . json_encode($message->getBody()));
            })->build();

        $consumer->consume();
    }
}