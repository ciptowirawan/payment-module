<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Payment;
use Illuminate\Http\Request;
use App\Services\KafkaProducerService;
use App\Http\Resources\PaymentResource;
use Illuminate\Support\Facades\Validator;

class PaymentController extends Controller
{
    protected $kafkaProducer;

    public function __construct(KafkaProducerService $kafkaProducer)
    {
        $this->kafkaProducer = $kafkaProducer;
    }

    public function update(Request $request, string $id) {
        $validator = Validator::make($request->all(), [
            'payment_evidence'     => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'paid_amount'   => 'required',
        ]);

        $now = Carbon::now();
        $status = "Paid";

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // //upload image
        // $image = $request->file('payment_evidence');
        // $image->storeAs('public/payments', $image->hashName());

        // $payment = Payment::where('user_id', $id)
        //     ->update([
        //     'payment_evidence'     => $image->hashName(),
        //     'paid_amount'     => $request->paid_amount,
        //     'payment_date'   => $now,
        //     'status'   => $status
        //     ]);

        // $payment = $this->kafkaProducer->sendPaymentEvent($request->all());

        //return response
        return new PaymentResource(true, 'Pembayaran berhasil disimpan!', $payment);
    }
}
