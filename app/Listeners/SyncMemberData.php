<?php

namespace App\Listeners;

use App\Models\Payment;
use App\Models\PaymentMember;
use App\Events\MemberDataReceived;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class SyncMemberData
{
    public function handle(MemberDataReceived $event): void
    {
        try {
            // Extract the movie data from the event
            $data = json_decode($event->data);
    
            $payment = PaymentMember::create([
                'amount' => $data->amount, 
                'payment_account' => $data->virtual_account, 
                'user_id' => $data->id, 
            ]);
    
            echo "Created Payment: ", print_r($payment, true);
    
            Log::info('Member data synchronized: ' . $data->id);
        } catch (\Exception $e) {
            // Log the error message
            Log::error('Error in handle method: ' . $e->getMessage());
        }
    }
}
