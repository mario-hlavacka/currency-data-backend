<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Events\CurrenciesUpdated;
use App\Services\ExternalApiService;

class FetchAndBroadcastCurrenciesData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fetch-and-broadcast-currencies-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch currency data and broadcast them to clients';

    /**
     * Execute the console command.
     */
    public function handle(ExternalApiService $externalApiService)
    {
        $data = $externalApiService->process();

        broadcast(new CurrenciesUpdated($data));
    }
}