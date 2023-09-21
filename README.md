# Bkash Payment Gateway for Laravel

[![Latest Version on Packagist](https://img.shields.io/packagist/v/itsrafsanjani/laravel-bkash.svg?style=flat-square)](https://packagist.org/packages/itsrafsanjani/laravel-bkash)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/itsrafsanjani/laravel-bkash/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/itsrafsanjani/laravel-bkash/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/itsrafsanjani/laravel-bkash/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/itsrafsanjani/laravel-bkash/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/itsrafsanjani/laravel-bkash.svg?style=flat-square)](https://packagist.org/packages/itsrafsanjani/laravel-bkash)

Laravel Bkash is a Laravel package for Bkash Payment Gateway. It uses the Tokenized API of Bkash.
## Installation

You can install the package via composer:

```bash
composer require itsrafsanjani/laravel-bkash
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="laravel-bkash-config"
```

This is the contents of the published config file:

```php
return [
    'sandbox' => env('BKASH_SANDBOX', true), // true for testing, false for production

    'app_key' => env('BKASH_APP_KEY', ''),
    'app_secret' => env('BKASH_APP_SECRET', ''),
    'username' => env('BKASH_USERNAME', ''),
    'password' => env('BKASH_PASSWORD', ''),

    // bkash will send data to this url
    'callbackURL' => env('BKASH_CALLBACK_URL', 'http://127.0.0.1:8000/bkash/callback'),
    'timezone' => 'Asia/Dhaka',
];
```

## Configuration

- Setup `.env`
```dotenv
# true for testing, false for production
BKASH_SANDBOX=true
BKASH_APP_KEY=
BKASH_APP_SECRET=
BKASH_USERNAME=
BKASH_PASSWORD=
# bkash will send data to this url
BKASH_CALLBACK_URL=http://127.0.0.1:8000/bkash/callback
```

- Create route for bkash in `routes/web.php`
```php
Route::get('/bkash/callback', [PaymentController::class, 'callback']);
```

- Add exception in `app\Http\Middleware\VerifyCsrfToken.php`
```php
protected $except = [
    'bkash/*',
];
```

## Usage

### Pay and Callback (Example Code for Controller)
```php
use Illuminate\Http\Request;
use ItsRafsanJani\Bkash\Data\CreatePaymentData;
use ItsRafsanJani\Bkash\Facades\Bkash;

class PaymentController extends Controller
{
    public function pay()
    {
        // ..
        // save payment related data in your database or anything
        // ..

        $invoiceId = uniqid(); // could be any string

        $response = Bkash::createPayment(
            new CreatePaymentData(
                amount: 20.50,
                payerReference: $invoiceId,
            )
        );

        // dd($response);

        return redirect()->away($response->bkashURL);
    }

    public function callback(Request $request)
    {
        // first you need to execute
        $executeResponse = Bkash::executePayment($request->paymentID);

        // then query
        $queryResponse =  Bkash::queryPayment($request->paymentID);
        
        // ..
        // update payment status
        // ..
    }
}
```

## Available Methods and Response

### Create Payment
```php
$invoiceId = uniqid(); // could be any string

$response = Bkash::createPayment(
    new CreatePaymentData(
        amount: 20.50,
        payerReference: $invoiceId,
    )
);

return response()->json($response);
```

### Example Response
```json
{
    "statusCode": "0000",
    "statusMessage": "Successful",
    "paymentID": "TR0011J9EMqSy16948652*****",
    "bkashURL": "https://sandbox.payment.bkash.com/redirect/tokenized/?paymentID=TR0011J9EMqSy16948652*****&hash=4kRP(RZBQ8Xn15a_0x4gW79V0EUZoaJJJv!UJmd10(s1Cl3ciz8aolIOsQ!3vlOno*vkq3jO-76BCoN7f4c8Zfykw1vaPsarABG21694865217269&mode=0011&apiVersion=v1.2.0-beta",
    "callbackURL": "http://localhost:8000/api/bkash-callback",
    "successCallbackURL": "http://localhost:8000/api/bkash-callback?paymentID=TR0011J9EMqSy16948652*****&status=success",
    "failureCallbackURL": "http://localhost:8000/api/bkash-callback?paymentID=TR0011J9EMqSy16948652*****&status=failure",
    "cancelledCallbackURL": "http://localhost:8000/api/bkash-callback?paymentID=TR0011J9EMqSy16948652*****&status=cancel",
    "amount": "10",
    "intent": "sale",
    "currency": "BDT",
    "paymentCreateTime": "2023-09-16T17:53:37:268 GMT+0600",
    "transactionStatus": "Initiated",
    "merchantInvoiceNumber": "65059731*****"
}
```

### Execute Payment
```php
$response = Bkash::executePayment($request->paymentID);

return response()->json($response);
```

### Example Response
```json
{
  "statusCode": "0000",
  "statusMessage": "Successful",
  "paymentID": "TR0011f0CE1zl16944532*****",
  "payerReference": "64ff4dd*****",
  "customerMsisdn": "018777*****",
  "trxID": "AIB10*****",
  "amount": "10",
  "transactionStatus": "Completed",
  "paymentExecuteTime": "2023-09-11T23:31:24:581 GMT+0600",
  "currency": "BDT",
  "intent": "sale",
  "merchantInvoiceNumber": "64ff4dd6*****"
}
```

### Query Payment
```php
$response =  Bkash::queryPayment($request->paymentID);

return response()->json($response);
```

### Example Response
```json
{
  "paymentID": "TR0011f0CE1zl16944532*****",
  "mode": "0011",
  "paymentCreateTime": "2023-09-11T23:26:49:676 GMT+0600",
  "paymentExecuteTime": "2023-09-11T23:31:24:581 GMT+0600",
  "amount": "10",
  "currency": "BDT",
  "intent": "sale",
  "merchantInvoice": "64ff4dd6*****",
  "trxID": "AIB10DO2ON",
  "transactionStatus": "Completed",
  "verificationStatus": "Complete",
  "statusCode": "0000",
  "statusMessage": "Successful",
  "payerReference": "64ff4dd*****"
}
```
### Search Transaction
```php
$searchTransactionResponse = Bkash::searchTransaction($response['trxID']);

return response()->json($searchTransactionResponse);
```

### Example Response
```json
{
  "trxID": "AIB10*****",
  "initiationTime": "2023-09-11T23:31:22:000 GMT+0600",
  "completedTime": "2023-09-11T23:31:22:000 GMT+0600",
  "transactionType": "bKash Tokenized Checkout via API",
  "customerMsisdn": "018777*****",
  "transactionStatus": "Completed",
  "amount": "10",
  "currency": "BDT",
  "organizationShortCode": "50***",
  "statusCode": "0000",
  "statusMessage": "Successful"
}
```

## Testing

```bash
./vendor/bin/pest
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [itsrafsanjani](https://github.com/itsrafsanjani)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
