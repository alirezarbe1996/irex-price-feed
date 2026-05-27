<div dir="rtl">

# irex-price-feed

یک میکروسرویس Laravel 12 جهت دریافت لحظه‌ای قیمت ارزهای دیجیتال از صرافی‌های اصلی ایرانی، ذخیره‌سازی داده‌های تاریخی در تایم‌فریم‌های مختلف، و ارائه داده‌های بهینه‌شده برای رسم نمودارها و ویژوالایزیشن از طریق REST API.

---

## ویژگی‌ها

- **تجمیع چند صرافی** — دریافت قیمت لحظه‌ای از آرزپلاس، افکس، نوبیتکس، ملی‌چنج، ایرانی‌کارت، اکسیر، امپ‌فینکس، پول‌انو و رمزینکس
- **سه بازه زمانی** — ذخیره مستقل تاریخچه قیمت به صورت `روزانه`، `هفتگی` و `ماهانه`
- **جمع‌آوری زمان‌بندی‌شده** — اجرای خودکار از طریق task scheduler لاراول (ساعتی برای روزانه، ۴ بار در روز برای هفتگی، یک‌بار در روز برای ماهانه)
- **همگام‌سازی متادیتای ارزها** — دریافت و به‌روزرسانی لیست کامل کوین‌ها (نام، نماد، اسلاگ) از CoinMarketCap
- **REST API** — اندپوینت‌های JSON ساده برای قیمت‌ها و اطلاعات صرافی‌ها
- **HTTP مقاوم** — تمام درخواست‌های خارجی با منطق retry (3 تلاش، تأخیر 5 ثانیه)

---

## صرافی‌های پشتیبانی‌شده

| صرافی | وب‌سایت |
|-------|---------|
| آرزپلاس | [arzplus.net](https://arzplus.net) |
| ملی‌چنج | [mellichange.com](https://mellichange.com) |
| ایرانی‌کارت | [iranicard.ir](https://iranicard.ir) |
| نوبیتکس | [nobitex.ir](https://nobitex.ir) |
| اکسیر | [exir.io](https://exir.io) |
| امپ‌فینکس | [ompfinex.com](https://ompfinex.com) |
| افکس | [farhad-exchange.com](https://farhad-exchange.com) |
| پول‌انو | [pooleno.ir](https://pooleno.ir) |
| رمزینکس | [ramzinex.com](https://ramzinex.com) |

---

## پشته فناوری

- **PHP 8.2+** / **Laravel 12**
- **MySQL** (یا هر RDBMS پشتیبانی‌شده توسط لاراول)
- Laravel HTTP Client (بر پایه Guzzle)
- Laravel Task Scheduling (دستورات Artisan + cron)

---

## نصب و راه‌اندازی

```bash
git clone https://github.com/your-username/irex-price-feed.git
cd irex-price-feed

composer install
cp .env.example .env
php artisan key:generate
```

تنظیمات دیتابیس را در فایل `.env` وارد کنید، سپس اجرا کنید:

```bash
php artisan migrate
php artisan db:seed --class=ExchangeSeeder
```

---

## زمان‌بندی

یک cron job روی سرور اضافه کنید تا تمام زمان‌بندی‌ها اجرا شوند:

```cron
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

بقیه کارها را scheduler به صورت خودکار مدیریت می‌کند:

| بازه زمانی | تناوب | تعداد نمونه |
|------------|-------|-------------|
| روزانه | هر ساعت | ~۲۴ در روز |
| هفتگی | ۴ بار در روز (۰۰:۰۰، ۰۶:۰۰، ۱۲:۰۰، ۱۸:۰۰) | ~۲۸ در هفته |
| ماهانه | یک‌بار در روز ساعت ۰۲:۰۰ | ~۳۰ در ماه |

همگام‌سازی متادیتای ارزها هر روز ساعت **۰۳:۰۰** انجام می‌شود.

---

## اجرای دستی دستورات

می‌توانید هر صرافی را به صورت دستی فراخوانی کنید:

```bash
php artisan app:arzplus daily
php artisan app:efex weekly
php artisan app:nobitex monthly

# همگام‌سازی متادیتای ارزها
php artisan app:update-currencies
```

بازه‌های زمانی معتبر: `daily`، `weekly`، `monthly`

---

## اندپوینت‌های API

### صرافی‌ها

```
GET /api/exchanges
```
لیست تمام صرافی‌های ثبت‌شده را برمی‌گرداند.

**نمونه پاسخ:**
```json
{
  "status": "success",
  "data": [
    { "id": 1, "name": "Arzplus" }
  ]
}
```

---

### قیمت‌ها

```
GET /api/prices/daily
GET /api/prices/weekly
GET /api/prices/monthly
```

نمونه‌های قیمت برای بازه زمانی درخواستی، همراه با متادیتای ارز را برمی‌گرداند.

**نمونه پاسخ:**
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

> قیمت‌ها بر اساس **تومان ایران (IRT)** هستند.

---

## ساختار دیتابیس

| جدول | توضیح |
|------|-------|
| `exchanges` | نام صرافی‌های ثبت‌شده |
| `currencies` | متادیتای کوین‌ها (نام، نماد، اسلاگ، لوگو) از CoinMarketCap |
| `daily_prices` | نمونه‌های ساعتی قیمت (پنجره ۲۴ ساعته) |
| `weekly_prices` | نمونه‌های ۶ ساعته قیمت (پنجره ۷ روزه) |
| `monthly_prices` | نمونه‌های روزانه قیمت (پنجره ۳۰ روزه) |

---

## افزودن صرافی جدید

1. یک Artisan command جدید در `app/Console/Commands/Exchanges/` بسازید
2. `PriceService` را تزریق کنید و `storePrices($pricesData, $timeFrame, 'ExchangeName')` را فراخوانی کنید
3. دستور را در `routes/console.php` برای هر سه بازه زمانی ثبت کنید
4. نام صرافی را به `ExchangeSeeder` اضافه کنید

---

## لایسنس

MIT

</div>
