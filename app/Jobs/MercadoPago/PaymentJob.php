<?php

namespace App\Jobs\MercadoPago;

use App\Models\PlanPayment;
use App\Services\MercadoPagoService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class PaymentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $plan_payment = new PlanPayment();
        $payments = $plan_payment->getPaymentOpen();

        foreach ($payments as $key_payment => $payment) {
            $mercado_pago_service = new MercadoPagoService(true);
            $mercado_pago_service->updatePayment($payment->id_transaction);

            Log::info($mercado_pago_service->log_payment_data);

            if (count($payments) != ($key_payment + 1)) {
                echo "-----------------------\n";
            }
        }
    }
}
