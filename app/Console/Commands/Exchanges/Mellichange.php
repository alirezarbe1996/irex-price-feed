<?php

namespace App\Console\Commands\Exchanges;

use App\Services\PriceService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class Mellichange extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:mellichange {timeFrame}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch data from Mellichange and store it according to the time frame (daily, weekly, monthly)';

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

        $urlapi = 'https://mellichange.com/api/Application/v1/assets/price/';

        try {
            $response = Http::retry(3, 5000)->get($urlapi);

            if ($response->successful()) {
                $responseData = $response->json();

                if ($responseData['Success'] && isset($responseData['Data']['markets'])) {
                    $marketsData = $responseData['Data']['markets'];
                    $now = now();
                    $pricesData = [];

                    foreach ($marketsData as $market => $coinData) {
                        $symbol = strtolower($market);
                        $highPrice = $coinData['sell_price'] ?? 0;
                        $lowPrice = $coinData['buy_price'] ?? 0;

                        $pricesData[$symbol] = [
                            'exchange' => 'Mellichange',
                            'high_price' => $highPrice,
                            'low_price' => $lowPrice,
                            'last_update' => $now,
                        ];
                    }

                    $this->priceService->storePrices($pricesData, $timeFrame, 'Mellichange');
                } else {
                    $this->error('Invalid or empty data received from Mellichange API.');
                }
            } else {
                $this->error('Failed to fetch data from Mellichange API. Status: ' . $response->status());
            }
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
        }
    }
}
