# irex-price-feed

A Laravel 12 microservice that aggregates real-time cryptocurrency prices from major Iranian exchanges and stores historical snapshots across multiple timeframes, primarily designed to power interactive charts and market visualizations via a clean REST API.

---

## Features

- **Multi-exchange aggregation** — pulls live price data from Arzplus, Efex, Nobitex, Mellichange, Iranicard, Exir, Ompfinex, Pooleno, and Ramzinex
- **Three timeframe buckets** — independently tracks `daily`, `weekly`, and `monthly` price histories
- **Scheduled collection** — automated via Laravel's task scheduler (hourly for daily, 4×/day for weekly, once/day for monthly)
- **Currency metadata sync** — fetches and keeps the full coin list (name, symbol, slug) in sync with CoinMarketCap
- **REST API** — simple JSON endpoints for prices and exchange info
- **Resilient HTTP** — all external API calls use retry logic (3 attempts, 5s delay)

---

## Tech Stack

- **PHP 8.2+** / **Laravel 12**
- **MySQL** (or any Laravel-supported RDBMS)
- Laravel HTTP Client (Guzzle under the hood)
- Laravel Task Scheduling (Artisan commands + cron)

---

## Installation

```bash
git clone https://github.com/your-username/irex-price-feed.git
cd irex-price-feed

composer install
cp .env.example .env
php artisan key:generate
```

Configure your database in `.env`, then run:

```bash
php artisan migrate
php artisan db:seed --class=ExchangeSeeder
```

---

## Scheduling

Add a single cron entry to your server to drive all scheduled jobs:

```cron
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

The scheduler then handles the rest automatically:

| Timeframe | Frequency | Samples |
|-----------|-----------|---------|
| Daily     | Every hour | ~24/day |
| Weekly    | 4× per day (00:00, 06:00, 12:00, 18:00) | ~28/week |
| Monthly   | Once per day at 02:00 | ~30/month |

Currency metadata syncs daily at **03:00**.

---

## Running Commands Manually

You can trigger any exchange fetch manually:

```bash
php artisan app:arzplus daily
php artisan app:efex weekly
php artisan app:nobitex monthly

# Sync currency metadata
php artisan app:update-currencies
```

Valid timeframes: `daily`, `weekly`, `monthly`

---

## API Endpoints

### Exchanges

```
GET /api/exchanges
```
Returns all registered exchanges.

**Response:**
```json
{
  "status": "success",
  "data": [
    { "id": 1, "name": "Arzplus"}
  ]
}
```

---

### Prices

```
GET /api/prices/daily
GET /api/prices/weekly
GET /api/prices/monthly
```

Returns price snapshots for the requested timeframe, joined with currency metadata.

**Response:**
```json
{
  "success": true,
  "message": "Daily prices fetched successfully.",
  "data": [
    {
      "id": 1,
      "exchange": "Arzplus",
      "coin_name": "btc",
      "high_price": 4250000000,
      "low_price": 4200000000,
      "last_update": "2025-01-01T12:00:00.000000Z",
      "coin_info": {
        "enName": "Bitcoin",
        "faName": "بیت‌کوین",
        "symbol": "BTC",
        "logo": "https://..."
      }
    }
  ]
}
```

> Prices are in **Iranian Toman (IRT)**.

---

## Database Schema

| Table | Description |
|-------|-------------|
| `exchanges` | Registered exchange names |
| `currencies` | Coin metadata (name, symbol, slug, logo) from CoinMarketCap |
| `daily_prices` | Hourly price snapshots (rolling 24h window) |
| `weekly_prices` | 6-hour price snapshots (rolling 7-day window) |
| `monthly_prices` | Daily price snapshots (rolling 30-day window) |

---

## Adding a New Exchange

1. Create a new Artisan command in `app/Console/Commands/Exchanges/`
2. Inject `PriceService` and call `storePrices($pricesData, $timeFrame, 'ExchangeName')`
3. Register the command in `routes/console.php` for all three timeframes
4. Add the exchange name to `ExchangeSeeder`

---

## License

MIT
