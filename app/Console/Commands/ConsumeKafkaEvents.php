<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\PaymentService;

class ConsumeKafkaEvents extends Command
{
    protected $signature = 'kafka:consume';
    protected $description = 'Consume Kafka events';

    protected $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        parent::__construct();
        $this->paymentService = $paymentService;
    }

    public function handle()
    {
        $this->paymentService->consume();
    }
}
