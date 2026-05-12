<?php

namespace App\Jobs;

use App\Http\Controllers\Admin\DealController;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateInvoiceFromDealJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $dealId;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($dealId)
    {
        $this->dealId = $dealId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // You may want to resolve the controller or move the logic to a service
        $controller = app(DealController::class);
        $controller->generateInvoiceFromDeal($this->dealId);
    }
}
