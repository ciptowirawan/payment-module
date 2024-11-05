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

    // public function show(Request $request) {
    //     $virtualAccount = $request->query('id');
    //     $inquiry = Billing::where('payment_account', $virtualAccount)->where('status', 'unpaid')->first();

    //     if (!$inquiry) {
    //         return response()->json('Tagihan tidak ditemukan!', 422);
    //     }

    //     return new PaymentResource(true, 'Tagihan ditemukan!', $inquiry);
    // }

    public function show(Request $request) {
        $virtualAccount = $request->query('id');
        // $inquiry = Billing::where('payment_account', $virtualAccount)->where('status', 'unpaid')->first();
        $inquiry = Billing::where('payment_account', $virtualAccount)
        ->where(function($query) {
            $query->where('status', 'unpaid')
                ->orWhere('due_date', '<', now());
        })->first();
    
        if (!$inquiry) {
            return response()->json([
                'responseCode' => '2002404',
                'responseMessage' => 'Inquiry Not Found',
                'virtualAccountData' => null
            ], 404);
        }
    
        $response = [
            'responseCode' => '2002400',
            'responseMessage' => 'Successful',
            'virtualAccountData' => [
                'inquiryStatus' => '00',
                'inquiryReason' => [
                    'english' => 'Success',
                    'indonesia' => 'Sukses'
                ],
                'partnerServiceId' => $inquiry->partner_service_id ?? '12345',
                'customerNo' => $inquiry->customer_no ?? $inquiry->user_id,
                'virtualAccountNo' => $inquiry->payment_account,
                'virtualAccountName' => $inquiry->user->name ?? 'Customer Name',
                'inquiryRequestId' => $inquiry->id,
                'totalAmount' => [
                    'value' => number_format($inquiry->amount, 2, '.', ''),
                    'currency' => 'IDR'
                ],
                'subCompany' => '00000',
                'billDetails' => [
                    [
                        'billNo' => $inquiry->bill_no ?? $inquiry->id,
                        'billDescription' => [
                            'english' => $inquiry->description_en ?? 'Bill Payment',
                            'indonesia' => $inquiry->description_id ?? 'Pembayaran Tagihan'
                        ],
                        'billSubCompany' => '00000',
                        'billAmount' => [
                            'value' => number_format($inquiry->amount, 2, '.', ''),
                            'currency' => 'IDR'
                        ]
                    ]
                ],
                'virtualAccountTrxType' => 'C',
                'feeAmount' => [
                    'value' => '',
                    'currency' => ''
                ],
                'additionalInfo' => [
                    'additionalInfo1' => [
                        'label' => [
                            'indonesia' => 'Info Tambahan 1',
                            'english' => 'Additional Info 1'
                        ],
                        'value' => [
                            'indonesia' => $inquiry->additional_info_1 ?? '',
                            'english' => $inquiry->additional_info_1 ?? ''
                        ]
                    ],
                    'additionalInfo2' => [
                        'label' => [
                            'indonesia' => 'Info Tambahan 2',
                            'english' => 'Additional Info 2'
                        ],
                        'value' => [
                            'indonesia' => $inquiry->additional_info_2 ?? '',
                            'english' => $inquiry->additional_info_2 ?? ''
                        ]
                    ]
                ]
            ]
        ];
    
        return response()->json($response);
    }

    // public function update(Request $request) {
    //     $virtualAccount = $request->query('id');
    //     $inquiry = Billing::where('payment_account', $virtualAccount)->where('status', 'unpaid')->first();
    //     if (!$inquiry) {
    //         return response()->json('Tagihan tidak ditemukan!', 422);
    //     }

    //     $inquiry->update([
    //         "status" => "paid"
    //     ]);

    //     $updatePayment = Payment::create([
    //             'amount' => $inquiry->amount,
    //             'status' => $request->query('status'),
    //             'payment_account' => $virtualAccount,
    //             'payment_date' => Carbon::now(),
    //             'paid_amount' => $request->query('amount'),
    //             'order_id' => $inquiry->order_id
    //         ]);

    //     // $updatedPaymentData = Payment::where('payment_account', $virtualAccount)->first();
    //     $updatedPaymentData = $updatePayment->toArray();
    //     $updatedPaymentData['user_id'] = $inquiry->billing_user_id;

    //     $message = new Message(
    //         headers: ['Content-Type' => 'application/json'],
    //         body: $updatedPaymentData,
    //         key: 'payment-success'  
    //     );
        
    //     try {
    //         $producer = Kafka::publishOn('payment-success')->withMessage($message);

    //         $producer->send();
    //     } catch (Exception $e) {
    //         dd('Caught exception: ',  $e->getMessage(), "\n");
    //     }
    
    //     return new PaymentResource(true, 'Pembayaran Berhasil dikonfirmasi!', $updatedPaymentData);
    // }

    public function update(Request $request) {
        $virtualAccount = $request->id;
        $inquiry = Billing::where('payment_account', $virtualAccount)->where('status', 'unpaid')->first();
        if (!$inquiry) {
            return response()->json([
                'responseCode' => '2002404',
                'responseMessage' => 'Inquiry Not Found',
                'virtualAccountData' => null
            ], 404);
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
            // Log the error instead of using dd()
            \Log::error('Kafka error: ' . $e->getMessage());
        }
    
        // Prepare the response in the format specified by BCA
        $response = [
            "virtualAccountNo" => $virtualAccount,
            "partnerReferenceNo" => $inquiry->order_id ?? '12345678', // Adjust as needed
            "trxDateTime" => Carbon::now()->format('Y-m-d\TH:i:sP'),
            "paymentStatus" => "Success",
            "paymentFlagReason" => [
                "english" => "Success",
                "indonesia" => "Sukses"
            ]
        ];
    
        return response()->json($response);
    }
}
