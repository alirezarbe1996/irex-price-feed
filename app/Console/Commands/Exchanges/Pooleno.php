<?php

namespace App\Console\Commands\Exchanges;

use App\Services\PriceService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class Pooleno extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:pooleno {timeFrame}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch data from Pooleno and store it according to the time frame (daily, weekly, monthly)';

    protected PriceService $priceService;

    public function __construct(PriceService $priceService)
    {
        parent::__construct();
        $this->priceService = $priceService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $timeFrame = $this->argument('timeFrame');

        $validTimeFrames = ['daily', 'weekly', 'monthly'];
        if (!in_array($timeFrame, $validTimeFrames)) {
            $this->error('Invalid time frame. Use: daily, weekly, or monthly.');
            return;
        }

        $urlapi = 'https://api.pooleno.ir/v1/token/summary/landing';

        try {
            $response = Http::retry(3, 5000)->get($urlapi);

            if ($response->successful()) {
                $dataFetch = $response->json();

                if (isset($dataFetch['tokens']) && is_array($dataFetch['tokens'])) {
                    $now = now();
                    $pricesData = [];

                    foreach ($dataFetch['tokens'] as $token) {
                        if (!isset($token['buyPrice']) || !isset($token['sellPrice'])) {
                            $this->error('Missing price data for token: ' . ($token['tokenShortName'] ?? 'unknown'));
                            continue;
                        }

                        $symbol = strtolower($token['tokenShortName']);
                        $highPrice = $token['buyPrice'] / 10;
                        $lowPrice = $token['sellPrice'] / 10;

                        $pricesData[$symbol] = [
                            'exchange' => 'Pooleno',
                            'high_price' => $highPrice,
                            'low_price' => $lowPrice,
                            'last_update' => $now,
                        ];
                    }

                    $this->priceService->storePrices($pricesData, $timeFrame, 'Pooleno');
                } else {
                    $this->error('Failed to fetch valid data from Pooleno.');
                }
            } else {
                $this->error('Failed to fetch data from Pooleno API. Status: ' . $response->status());
            }
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
        }
    }
}
