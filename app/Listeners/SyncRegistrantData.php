<?php

namespace App\Listeners;

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
    
            $payment = Payment::create([
                'amount' => $data->amount, 
                'payment_account' => $data->account, 
                'user_id' => $data->id, 
            ]);
    
            echo "Created Payment: ", print_r($payment, true);
    
            Log::info('Registrant data synchronized: ' . $data->id);
        } catch (\Exception $e) {
            // Log the error message
            Log::error('Error in handle method: ' . $e->getMessage());
        }
    }
}
