<?php

namespace App\Console\Commands\Exchanges;

use App\Services\PriceService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class Iranicard extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:iranicard {timeFrame}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch data from Iranicard and store it according to the time frame (daily, weekly, monthly)';

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

        $urlapi = 'https://api.iranicard.ir/api/v1/cryptocurrencies';

        try {
            $response = Http::retry(3, 5000)->get($urlapi);

            if ($response->successful()) {
                $dataFetch = $response->json();
                $now = now();
                $pricesData = [];

                foreach ($dataFetch as $coin => $info) {
                    if (!isset($info['buy']['price_rial']) || !isset($info['sell']['price_rial'])) {
                        continue;
                    }

                    $symbol = strtolower($coin);
                    $highPrice = $info['buy']['price_rial'] / 10;
                    $lowPrice = $info['sell']['price_rial'] / 10;

                    $pricesData[$symbol] = [
                        'exchange' => 'Iranicard',
                        'high_price' => $highPrice,
                        'low_price' => $lowPrice,
                        'last_update' => $now,
                    ];
                }

                $this->priceService->storePrices($pricesData, $timeFrame, 'Iranicard');
            } else {
                $this->error('Failed to fetch data from Iranicard API. Status: ' . $response->status());
            }
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
        }
    }
}
