<?php

namespace App\Listeners;

use App\Models\Billing;
use App\Models\Payment;
use Illuminate\Support\Facades\Log;
use App\Events\RegistrantDataReceived;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class SyncRegistrantData
{
    public function handle(RegistrantDataReceived $event): void
    {
        try {
            // Extract the movie data from the event
            $data = json_decode($event->data);
    
            $billing = Billing::create([
                'amount' => $data->amount,
                'payment_account' => $data->account,
                'billing_name' => $data->full_name, 
                'billing_user_id' => $data->user_id,
                'order_id' => $data->id
            ]);
    
            echo "Created Billing: ", print_r($billing, true);
            
            Log::info('Registrant data synchronized: ' . $data->id);
        } catch (\Exception $e) {
            // Log the error message
            Log::error('Error in handle method: ' . $e->getMessage());
        }
    }
}
