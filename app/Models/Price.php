<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Price extends Model
{
    protected $table = 'daily_prices';


    protected $fillable = [
        'exchange',
        'coin_name',
        'high_price',
        'low_price',
        'last_update',
    ];

    public function setTableBasedOnTimeFrame($timeFrame): static
    {
        switch ($timeFrame) {
            case 'daily':
                $this->table = 'daily_prices';
                break;
            case 'weekly':
                $this->table = 'weekly_prices';
                break;
            case 'monthly':
                $this->table = 'monthly_prices';
                break;
            default:
                throw new \Exception('Invalid time frame');
        }
        return $this;
    }

    public function currency(): HasOne
    {
        return $this->hasOne(Currency::class, 'abbr', 'coin_name');
    }
}
