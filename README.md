## Installation

```bash
composer require samir-hussein/paymob
```

Run command

```bash
php artisan vendor:publish --provider="PayMob\PayMobServiceProvider"
```

## Steps

step 1 :
in config/app.php

```php
//in providers
PayMob\PayMobServiceProvider::class,
//in aliases
'PayMob' => PayMob\Facades\PayMob::class,
```


step 2 : in .env file

```bash
PayMob_Username="Your_Username"
PayMob_Password="Your_Password"
PayMob_Integration_Id="Integration_Id"
PAYMOB_API_KEY="Your_api_key"
PayMob_HMAC="HMAC" // from your dashboard
```

##controllers

step 3 : create controller like this

```php
<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use PayMob\Facades\PayMob;

class PayMobController extends Controller
{
    public static function pay(float $total_price , int $order_id)
    {
        $auth = PayMob::AuthenticationRequest();

        $order = PayMob::OrderRegistrationAPI([
            'auth_token' => $auth->token,
            'amount_cents' => $total_price * 100, //put your price
            'currency' => 'EGP',
            'delivery_needed' => false, // another option true
            'merchant_order_id' => $order_id, //put order id from your database must be unique id
            'items' => [] // all items information or leave it empty
        ]);
        $PaymentKey = PayMob::PaymentKeyRequest([
            'auth_token' => $auth->token,
            'amount_cents' => $total_price * 100, //put your price
            'currency' => 'EGP',
            'order_id' => $order->id,
            "billing_data" => [ // put your client information
                "apartment" => "803",
                "email" => "claudette09@exa.com",
                "floor" => "42",
                "first_name" => "Clifford",
                "street" => "Ethan Land",
                "building" => "8028",
                "phone_number" => "+86(8)9135210487",
                "shipping_method" => "PKG",
                "postal_code" => "01898",
                "city" => "Jaskolskiburgh",
                "country" => "CR",
                "last_name" => "Nicolas",
                "state" => "Utah"
            ]
        ]);

        return $PaymentKey->token;
    }


    public function checkout_processed(Request $request)
    {
        $request_hmac = $request->hmac;
        $calc_hmac = PayMob::calcHMAC($request);

        if ($request_hmac == $calc_hmac) {
            $order_id = $request->obj['order']['merchant_order_id'];
            $amount_cents = $request->obj['amount_cents'];
            $transaction_id = $request->obj['id'];

            $order = Order::find($order_id);

            if ($request->obj['success'] == true && ($order->total_price * 100) == $amount_cents) {
                $order->update([
                    'transaction_status' => 'finished',
                    'transaction_id' => $transaction_id
                ]);
            } else {
                $order->update([
                    'transaction_status' => "failed",
                    'transaction_id' => $transaction_id
                ]);
            }
        }
    }


} //end of class
```


step 4 : create controller like this
```php
<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    public function index(Request $request)
    {
        $order = Order::create([
            'total_price'=>$request->total_price,
        ]);

        $PaymentKey = PayMobController::pay($order->total_price,$order->id);

        return view('paymob_iframe')->with('token',$PaymentKey);
    }

} //end of class
```


## Views

step 5 : create view checkout.blade.php like this

```html
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>

    <style>
        @import url("https://fonts.googleapis.com/css2?family=Istok+Web:wght@400;700&display=swap");

        * {
            margin: 0;
            padding: 0;
            font-family: "Istok Web", sans-serif;
        }

        body {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background: #212121;
        }

        .card {
            position: relative;
            width: 320px;
            height: 480px;
            background: #191919;
            border-radius: 20px;
            overflow: hidden;
        }

        .card::before {
            content: "";
            position: absolute;
            top: -50%;
            width: 100%;
            height: 100%;
            background: #ffce00;
            transform: skewY(345deg);
            transition: 0.5s;
        }

        .card:hover::before {
            top: -70%;
            transform: skewY(390deg);
        }

        .card::after {
            content: "CORSAIR";
            position: absolute;
            bottom: 0;
            left: 0;
            font-weight: 600;
            font-size: 6em;
            color: rgba(0, 0, 0, 0.1);
        }

        .card .imgBox {
            position: relative;
            width: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
            padding-top: 20px;
            z-index: 1;
        }
        /*
        .card .imgBox img {
            max-width: 100%;

            transition: .5s;
        }

        .card:hover .imgBox img {
            max-width: 50%;

        }
        */
        .card .contentBox {
            position: relative;
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            z-index: 2;
        }

        .card .contentBox h3 {
            font-size: 18px;
            color: white;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .card .contentBox .price {
            font-size: 24px;
            color: white;
            font-weight: 700;
            letter-spacing: 1px;
        }

        .card .contentBox .buy {
            position: relative;
            top: 100px;
            opacity: 0;
            padding: 10px 30px;
            margin-top: 15px;
            color: #000000;
            text-decoration: none;
            background: #ffce00;
            border-radius: 30px;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: 0.5s;
            cursor: pointer;
        }

        .card:hover .contentBox .buy {
            top: 0;
            opacity: 1;
        }

        .mouse {
            height: 300px;
            width: auto;
        }

    </style>
</head>
<body>
    <div class="card">
        <form action="{{route('checkout')}}" method="post">
            @csrf
            <div class="imgBox">
                <img src="https://www.corsair.com/corsairmedia/sys_master/productcontent/CH-9300011-NA-M65_PRO_RGB_BLK_04.png" alt="mouse corsair" class="mouse">
            </div>

            <input type="hidden" name="total_price" value="1050">

            <div class="contentBox">
                <h3>Mouse Corsair M65</h3>
                <h2 class="price">1050 EG</h2>
                <button type="submit" class="buy">
                    Buy Now
                </button>
            </div>
        </form>
    </div>
</body>
</html>

```


step 6 : create view paymob_iframe.blade.php and use your iframe like this

```html
<iframe
  width="100%"
  height="800"
  src="https://accept.paymob.com/api/acceptance/iframes/your_iframe_id?payment_token={{$token}}"
>
</iframe>
```

## Routes


step 7 : in routes/api.php
```php
Route::post('/checkout/processed', [\App\Http\Controllers\PayMobController::class,'checkout_processed']);
```

step 8 : in routes/web.php
```php
Route::post('/checkout', [\App\Http\Controllers\CheckoutController::class,'index'])->name('checkout');

Route::get('/checkout/response', function (\Illuminate\Http\Request $request){
    return $request->all();
});
```



<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

You may also try the [Laravel Bootcamp](https://bootcamp.laravel.com), where you will be guided through building a modern Laravel application from scratch.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains over 2000 video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the Laravel [Patreon page](https://patreon.com/taylorotwell).

### Premium Partners

- **[Vehikl](https://vehikl.com/)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Cubet Techno Labs](https://cubettech.com)**
- **[Cyber-Duck](https://cyber-duck.co.uk)**
- **[Many](https://www.many.co.uk)**
- **[Webdock, Fast VPS Hosting](https://www.webdock.io/en)**
- **[DevSquad](https://devsquad.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel/)**
- **[OP.GG](https://op.gg)**
- **[WebReinvent](https://webreinvent.com/?utm_source=laravel&utm_medium=github&utm_campaign=patreon-sponsors)**
- **[Lendio](https://lendio.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
