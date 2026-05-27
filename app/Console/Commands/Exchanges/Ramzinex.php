<?php

namespace App\Console\Commands\Exchanges;

use App\Services\PriceService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class Ramzinex extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:ramzinex {timeFrame}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch data from Ramzinex and store it according to the time frame (daily, weekly, monthly)';

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

        $urlapi = 'https://publicapi.ramzinex.com/exchange/api/v1.0/exchange/prices';

        try {
            $response = Http::retry(3, 5000)->get($urlapi);

            if ($response->successful()) {
                $dataFetch = $response->json();

                if (isset($dataFetch['status']) && $dataFetch['status'] === 0) {
                    $now = now();
                    $pricesData = [];

                    foreach ($dataFetch['data'] as $coin => $info) {
                        if (strpos($coin, 'irr') === false) continue;

                        $symbol = str_replace('irr', '', $coin);
                        $buyPrice = $info['buy'] ?? '-';
                        $sellPrice = $info['sell'] ?? '-';

                        if ($buyPrice !== '-' && $sellPrice !== '-') {
                            $pricesData[$symbol] = [
                                'exchange' => 'Ramzinex',
                                'high_price' => $sellPrice / 10,
                                'low_price' => $buyPrice / 10,
                                'last_update' => $now,
                            ];
                        }
                    }
                    $this->priceService->storePrices($pricesData, $timeFrame, 'Ramzinex');
                } else {
                    $this->error('Failed to fetch valid data from Ramzinex.');
                }
            } else {
                $this->error('Failed to fetch data from Ramzinex API. Status: ' . $response->status());
            }
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
        }
    }
}
