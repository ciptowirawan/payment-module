<?php

namespace App\Services;

use Enqueue\RdKafka\RdKafkaConnectionFactory;

class KafkaProducerService {
    protected $producer;

    public function __construct()
    {
        $connectionFactory = new RdKafkaConnectionFactory(['global' => ['group.id' => 'payment_group_id']]);
        $this->producer = $connectionFactory->createContext()->createProducer();
    }

    public function sendPaymentEvent(array $paymentData)
    {
        $topic = $this->producer->createTopic(env('KAFKA_TOPIC'));
        $message = $this->producer->createMessage(json_encode($paymentData));
        $this->producer->send($topic, $message);
    }
}
