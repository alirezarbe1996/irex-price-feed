<?php

namespace App\Console\Commands\Exchanges;

use App\Services\PriceService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class Arzplus extends Command
{
    protected $signature = 'app:arzplus {timeFrame}';
    protected $description = 'Fetch data from Arzplus and store it according to the time frame (daily, weekly, monthly)';

    protected PriceService $priceService;

    public function __construct(PriceService $priceService)
    {
        parent::__construct();
        $this->priceService = $priceService;
    }

    public function handle()
    {
        $timeFrame = $this->argument('timeFrame');

        $validTimeFrames = ['daily', 'weekly', 'monthly'];
        if (!in_array($timeFrame, $validTimeFrames)) {
            $this->error('Invalid time frame. Use: daily, weekly, or monthly.');
            return;
        }

        $urlapi = 'https://api.arzplus.net/api/v1/market/irt/info/';

        try {
            $response = Http::retry(3, 5000)->get($urlapi);

            if ($response->successful()) {
                $dataFetch = $response->json();
                $pricesData = [];

                foreach ($dataFetch as $coinData) {
                    if (!isset($coinData['symbol'], $coinData['ask'], $coinData['bid'])) {
                        continue;
                    }

                    $symbol = strtolower($coinData['symbol']);
                    $highPrice = $coinData['ask'];
                    $lowPrice = $coinData['bid'];

                    $pricesData[$symbol] = [
                        'exchange' => 'Arzplus',
                        'high_price' => $highPrice,
                        'low_price' => $lowPrice,
                        'last_update' => now(),
                    ];
                }

                $this->priceService->storePrices($pricesData, $timeFrame, 'Arzplus');
            } else {
                $this->error('Failed to fetch data from Arzplus API. Status: ' . $response->status());
            }
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
        }
    }
}
