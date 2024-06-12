<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Junges\Kafka\Message\Message;
use Enqueue\SimpleClient\SimpleClient;

class InquiryController extends Controller
{
    protected $client;

    public function __construct()
    {
        $this->client = new SimpleClient(config('enqueue.default'));
    }

    public function show(string $virtualAccount) {
        $inquiry = Payment::where('payment_account', $virtualAccount)->first();

        if (!$inquiry) {
            return response()->json('Tagihan tidak ditemukan!', 422);
        }

        return new PaymentResource(true, 'Tagihan ditemukan!', $inquiry);
    }

    public function update(string $virtualAccount, string $status) {
        $updatePayment = Payment::where('payment_account', $virtualAccount)
            ->update([
                'status' => $status
            ]);

        $updatedPaymentData = Payment::where('payment_account', $virtualAccount)->first();

        $message = new Message(
            headers: ['Content-Type' => 'application/json'],
            body: $updatedPaymentData,
            key: 'payment-success'  
        );
        
        Kafka::publishOn('payment-success')->withMessage($message);
    
        return response()->json(['message' => 'Payment Updated successfully']);
    }
}
