<?php

namespace App\Console\Commands\Exchanges;

use App\Services\PriceService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class Ompfinex extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:ompfinex {timeFrame}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch data from Ompfinex and store it according to the time frame (daily, weekly, monthly)';

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

        $urlapi = 'https://api.ompfinex.com/v1/orderbook';

        try {
            $response = Http::retry(3, 5000)->get($urlapi);

            if ($response->successful()) {
                $dataFetch = $response->json();

                if (isset($dataFetch['status']) && $dataFetch['status'] === 'OK') {
                    $now = now();
                    $pricesData = [];

                    foreach ($dataFetch['data'] as $coin => $info) {
                        if (!str_contains($coin, 'IRR')) continue;

                        $symbol = strtolower(str_replace('IRR', '', $coin));
                        $bids = $info['bids'] ?? [];
                        $asks = $info['asks'] ?? [];

                        if (!empty($bids) && !empty($asks)) {
                            $pricesData[$symbol] = [
                                'exchange' => 'Ompfinex',
                                'high_price' => $bids[0]['price'] / 10,
                                'low_price' => $asks[0]['price'] / 10,
                                'last_update' => $now,
                            ];
                        }
                    }

                    $this->priceService->storePrices($pricesData, $timeFrame, 'Ompfinex');
                } else {
                    $this->error('Failed to fetch valid data from Ompfinex.');
                }
            } else {
                $this->error('Failed to fetch data from Ompfinex API. Status: ' . $response->status());
            }
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
        }
    }
}
