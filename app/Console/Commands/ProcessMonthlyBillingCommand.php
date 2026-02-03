<?php

namespace App\Console\Commands;

use App\Jobs\ProcessMonthlyBilling;
use Illuminate\Console\Command;

class ProcessMonthlyBillingCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'billing:process-monthly';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'montly billing process command';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        ProcessMonthlyBilling::dispatch()
            ->onQueue('billing');

        $this->info('Monthly billing job dispatched to billing queue.');

        return self::SUCCESS;
    }
}
