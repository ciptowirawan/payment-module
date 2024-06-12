<?php

namespace App\Services;

use App\Models\Payment;
use Interop\Queue\Context;
use Interop\Queue\Message;
use Enqueue\Consumption\QueueConsumer;
use Enqueue\SimpleClient\SimpleClient;

class PaymentService
{
    protected $client;
    protected $consumer;

    public function __construct()
    {
        $this->client = new SimpleClient(config('enqueue.default'));
        $this->consumer = $this->client->getQueueConsumer();
    }

    public function consume()
    {
        $this->consumer->bind('order-created', function (Message $message, Context $context) {
            $event = json_decode($message->getBody(), true);
            // Process the event and create payment credentials
            if (isset($event['id'], $event['account'], $event['amount'])) {
                $this->createPaymentCredentials($event['id'], $event['account'], $event['amount']);
                echo "Received event: ", print_r($event, true);
            } else {
                // Handle the case where the necessary data is missing
                echo "Invalid event data: ", print_r($event, true);
            }
        });

        $this->consumer->consume();
    }

    private function createPaymentCredentials($userId, $account, $amount)
    {
        
        $payment = Payment::create([
            'amount' => $amount, 
            'payment_account' => $account, 
            'user_id' => $userId, 
            // 'payment_evidence'     => $image->hashName(),
            // 'paid_amount'     => $request->paid_amount,
            // 'payment_date'   => $now,
            // 'status'   => $status
        ]);

        echo "Created Payment: ", print_r($payment, true);
    }
}
