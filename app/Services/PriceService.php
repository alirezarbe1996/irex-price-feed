<?php

namespace App\Services;

use App\Models\Price;

class PriceService
{
    protected Price $priceModel;

    public function __construct(Price $priceModel)
    {
        $this->priceModel = $priceModel;
    }

    public function storePrices(array $pricesData, string $timeFrame, string $exchangeName): void
    {
        $now = now();
        $this->priceModel->setTableBasedOnTimeFrame($timeFrame);

        $existingPrices = $this->priceModel->where('exchange', $exchangeName)
            ->pluck('coin_name')
            ->toArray();

        $newCoinNames = [];

        foreach ($pricesData as $symbol => $data) {
            $this->priceModel->updateOrCreate(
                [
                    'exchange' => $exchangeName,
                    'coin_name' => $symbol
                ],
                [
                    'high_price' => $data['high_price'],
                    'low_price' => $data['low_price'],
                    'last_update' => $data['last_update']
                ]
            );

            $newCoinNames[] = $symbol;
        }

        $coinsToDelete = array_diff($existingPrices, $newCoinNames);
        if (!empty($coinsToDelete)) {
            $this->priceModel->whereIn('coin_name', $coinsToDelete)->delete();
        }

        echo 'Data fetched and stored successfully from ' . $exchangeName . '!' . PHP_EOL;
    }
}
