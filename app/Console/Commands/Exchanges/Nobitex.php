<?php

namespace App\Console\Commands\Exchanges;

use App\Models\Price;
use App\Services\PriceService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class Nobitex extends Command
{
    protected $signature = 'app:nobitex {timeFrame}';
    protected $description = 'Fetch data from Nobitex and store it according to the time frame (daily, weekly, monthly)';

    protected PriceService $priceService;

    public function __construct(PriceService $priceService)
    {
        parent::__construct();
        $this->priceService = $priceService;
    }

    public function handle()
    {
        $timeFrame = $this->argument('timeFrame');
        $exchangeName = 'Nobitex';

        $validTimeFrames = ['daily', 'weekly', 'monthly'];
        if (!in_array($timeFrame, $validTimeFrames)) {
            $this->error('Invalid time frame. Use: daily, weekly, or monthly.');
            return;
        }

        $urlapi = 'https://api.nobitex.ir/v2/orderbook/all';

        try {
            $response = Http::retry(3, 5000)->get($urlapi);

            if ($response->successful()) {
                $dataFetch = $response->json();

                if (isset($dataFetch['status']) && $dataFetch['status'] === 'ok') {
                    $pricesData = [];

                    foreach ($dataFetch as $coin => $info) {
                        if ($coin === 'status' || !str_contains($coin, 'IRT')) continue;

                        $symbol = strtolower(str_replace('IRT', '', $coin));
                        $bids = $info['bids'] ?? [];
                        $asks = $info['asks'] ?? [];

                        if (!empty($bids) && !empty($asks)) {
                            $highPrice = ($symbol === 'shib') ? $bids[0][0] / 10000 : $bids[0][0] / 10;
                            $lowPrice = ($symbol === 'shib') ? $asks[0][0] / 10000 : $asks[0][0] / 10;

                            $pricesData[$symbol] = [
                                'exchange' => $exchangeName,
                                'high_price' => $highPrice,
                                'low_price' => $lowPrice,
                                'last_update' => now(),
                            ];
                        }
                    }

                    $this->priceService->storePrices($pricesData, $timeFrame, $exchangeName);
                } else {
                    $this->error('Failed to fetch valid data from Nobitex.');
                }
            } else {
                $this->error('Failed to fetch data from Nobitex API. Status: ' . $response->status());
            }
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
        }
    }
}
