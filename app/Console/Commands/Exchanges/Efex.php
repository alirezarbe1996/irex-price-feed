<?php

namespace App\Console\Commands\Exchanges;

use App\Models\Price;
use App\Services\PriceService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class Efex extends Command
{
    protected $signature = 'app:efex {timeFrame}';
    protected $description = 'Fetch data from Efex exchange and store it in the database.';
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

        $urlapi = 'https://api.farhad-exchange.com/get-all-rate';

        try {
            $response = Http::retry(3, 5000)->get($urlapi);

            if ($response->successful()) {
                $dataFetch = $response->json();
                $now = now();
                $pricesData = [];

                foreach ($dataFetch as $entry) {
                    if (str_contains(strtolower($entry['market']), 'irr')) {
                        $symbol = strtolower(explode('-', $entry['market'])[0]);
                        $sell = $entry['sell_price'];
                        $buy = $entry['buy_price'];

                        if ($symbol === 'shib') {
                            $sell /= 1000;
                            $buy /= 1000;
                        }

                        $pricesData[$symbol] = [
                            'exchange' => 'Efex',
                            'high_price' => $sell / 10,
                            'low_price' => $buy / 10,
                            'last_update' => $now,
                        ];
                    }
                }

                $this->priceService->storePrices($pricesData, $timeFrame, 'Efex');
            } else {
                $this->error('Failed to fetch data from Efex API. Status: ' . $response->status());
            }
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
        }
    }
}
