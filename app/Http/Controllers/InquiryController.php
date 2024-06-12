<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;
use Junges\Kafka\Facades\Kafka;
use Junges\Kafka\Message\Message;
use Enqueue\SimpleClient\SimpleClient;
use App\Http\Resources\PaymentResource;

class InquiryController extends Controller
{
    protected $client;

    public function __construct()
    {
        $this->client = new SimpleClient(config('enqueue.default'));
    }

    public function show(Request $request) {
        $virtualAccount = $request->query('id');
        $inquiry = Payment::where('payment_account', $virtualAccount)->first();

        if (!$inquiry) {
            return response()->json('Tagihan tidak ditemukan!', 422);
        }

        return new PaymentResource(true, 'Tagihan ditemukan!', $inquiry);
    }

    public function update(Request $request) {
        $virtualAccount = $request->query('id');
        $updatePayment = Payment::where('payment_account', $virtualAccount)
            ->update([
                'status' => $request->query('status')
            ]);

        $updatedPaymentData = Payment::where('payment_account', $virtualAccount)->first();

        $message = new Message(
            headers: ['Content-Type' => 'application/json'],
            body: $updatedPaymentData,
            key: 'payment-success'  
        );
        
        try {
            $producer = Kafka::publishOn('payment-success', '192.168.99.100:29092')->withMessage($message);

            $producer->send();
        } catch (Exception $e) {
            dd('Caught exception: ',  $e->getMessage(), "\n");
        }
    
        return new PaymentResource(true, 'Pembayaran Berhasil dikonfirmasi!', $updatedPaymentData);
    }
}
