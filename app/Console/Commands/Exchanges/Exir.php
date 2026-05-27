<?php

namespace App\Console\Commands\Exchanges;

use App\Services\PriceService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class Exir extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:exir {timeFrame}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch data from Exir and store it according to the time frame (daily, weekly, monthly)';

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

        $urlapi = 'https://api.exir.io/v1/orderbooks';

        try {
            $response = Http::retry(3, 5000)->get($urlapi);

            if ($response->successful()) {
                $dataFetch = $response->json();
                $now = now();
                $pricesData = [];

                foreach ($dataFetch as $coin => $info) {
                    if (!str_contains($coin, '-irt')) continue;

                    $symbol = strtolower(str_replace('-irt', '', $coin));

                    if (!isset($info['asks']) || !isset($info['bids'])) {
                        continue;
                    }

                    $pricesData[$symbol] = [
                        'exchange' => 'Exir',
                        'high_price' => $info['asks'][0][0],
                        'low_price' => $info['bids'][0][0],
                        'last_update' => $now,
                    ];
                }

                $this->priceService->storePrices($pricesData, $timeFrame, 'Exir');
            } else {
                $this->error('Failed to fetch data from Exir API. Status: ' . $response->status());
            }
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
        }
    }
}
