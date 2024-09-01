<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Billing;
use App\Models\Payment;
use Illuminate\Http\Request;
use App\Models\PaymentMember;
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
        $inquiry = Billing::where('payment_account', $virtualAccount)->where('status', 'unpaid')->first();

        if (!$inquiry) {
            return response()->json('Tagihan tidak ditemukan!', 422);
        }

        return new PaymentResource(true, 'Tagihan ditemukan!', $inquiry);
    }

    public function update(Request $request) {
        $virtualAccount = $request->query('id');
        $inquiry = Billing::where('payment_account', $virtualAccount)->where('status', 'unpaid')->first();
        if (!$inquiry) {
            return response()->json('Tagihan tidak ditemukan!', 422);
        }

        $inquiry->update([
            "status" => "paid"
        ]);

        $updatePayment = Payment::create([
                'amount' => $inquiry->amount,
                'status' => $request->query('status'),
                'payment_account' => $virtualAccount,
                'payment_date' => Carbon::now(),
                'paid_amount' => $request->query('amount'),
                'order_id' => $inquiry->order_id
            ]);

        // $updatedPaymentData = Payment::where('payment_account', $virtualAccount)->first();
        $updatedPaymentData = $updatePayment->toArray();
        $updatedPaymentData['user_id'] = $inquiry->billing_user_id;

        $message = new Message(
            headers: ['Content-Type' => 'application/json'],
            body: $updatedPaymentData,
            key: 'payment-success'  
        );
        
        try {
            $producer = Kafka::publishOn('payment-success')->withMessage($message);

            $producer->send();
        } catch (Exception $e) {
            dd('Caught exception: ',  $e->getMessage(), "\n");
        }
    
        return new PaymentResource(true, 'Pembayaran Berhasil dikonfirmasi!', $updatedPaymentData);
    }
}
