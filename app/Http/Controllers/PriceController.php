<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

class PriceController extends Controller
{
    public function getDailyPrices(): JsonResponse
    {
        return $this->getPricesByTimeFrame('daily');
    }

    public function getWeeklyPrices(): JsonResponse
    {
        return $this->getPricesByTimeFrame('weekly');
    }

    public function getMonthlyPrices(): JsonResponse
    {
        return $this->getPricesByTimeFrame('monthly');
    }

    private function getPricesByTimeFrame(string $timeFrame): JsonResponse
    {
        $tableName = match ($timeFrame) {
            'daily' => 'daily_prices',
            'weekly' => 'weekly_prices',
            'monthly' => 'monthly_prices',
            default => throw new \Exception('Invalid time frame'),
        };

        $prices = \DB::table($tableName)
            ->join('currencies', 'currencies.symbol', '=', "{$tableName}.coin_name")
            ->select("{$tableName}.*", 'currencies.enName', 'currencies.faName', 'currencies.symbol', 'currencies.logo')
            ->get();

        $pricesWithInfo = $prices->map(function ($price) {
            return [
                'id' => $price->id,
                'exchange' => $price->exchange,
                'coin_name' => $price->coin_name,
                'high_price' => $price->high_price,
                'low_price' => $price->low_price,
                'last_update' => $price->last_update,
                'coin_info' => [
                    'id' => $price->id,
                    'enName' => $price->enName,
                    'faName' => $price->faName,
                    'symbol' => $price->symbol,
                    'logo' => $price->logo,
                ],
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $pricesWithInfo,
            'message' => ucfirst($timeFrame) . ' prices fetched successfully.',
        ]);
    }
}
