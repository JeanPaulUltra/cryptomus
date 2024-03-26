# Laravel wrapper for the Cryptomus API.



## Installation

You can install the package via composer:

```bash
composer require kristof202/cryptomus
```

The service provider will automatically register itself.

You must publish the config file with:
```bash
php artisan vendor:publish --provider="Kristof\Cryptomus\CryptomusServiceProvider" --tag="config"
```

This is the contents of the config file that will be published at `config/cryptomus.php`:

```php
return [
    'MerchantID' => env('CRYPTOMUS_MERCHANT_ID'),
    'PaymentKey' => env('CRYPTOMUS_PAYMENT_KEY'),
    'webhookJobs' => [
        // 'cancel' => \App\Jobs\CryptomusWebhook\HandleCancel::class,
        // 'paid' => \App\Jobs\CryptomusWebhook\HandlePaid::class,
        // 'wrong_amount' => \App\Jobs\CryptomusWebhook\HandleWrongAmount::class,
        // 'paid_over' => \App\Jobs\CryptomusWebhook\HandlePaidOver::class,
    ],
    'webhookModel' => Kristof\Cryptomus\Models\CryptomusWebhookCall::class,
];

```

In the `PaymentKey` key of the config file you should add a valid payment key. You can find the keys at [the Cryptomus Merchants dashboard].

Next, you must publish the migration with:
```bash
php artisan vendor:publish --provider="Kristof\Cryptomus\CryptomusServiceProvider" --tag="migrations"
```

After the migration has been published you can create the `cryptomus_webhook_calls` table by running the migrations:

```bash
php artisan migrate
```

## Usage

### Creating an invoice

```php
$payment = Cryptomus::createPayment([
            "amount"=> "15",
	        "currency"=> "USD",
            "order_id"=>"1",
        ]);
```


### Webhooks

Cryptomus will send out webhooks for several event types. check the Cryptomus API documentation.

Cryptomus will sign all requests hitting the webhook url of your app. This package will automatically verify if the signature is valid. If it is not, the request was probably not sent by Cryptomus.
 
Unless something goes terribly wrong, this package will always respond with a `200` to webhook requests. All webhook requests with a valid signature will be logged in the `cryptomus_webhook_calls` table. The table has a `payload` column where the entire payload of the incoming webhook is saved.

If the signature is not valid, the request will not be logged in the `cryptomus_webhook_calls` table but a `Kristof\Cryptomus\Exceptions\WebhookFailed` exception will be thrown.
If something goes wrong during the webhook request the thrown exception will be saved in the `exception` column. In that case the controller will send a `500` instead of `200`. 
 
There are two ways this package enables you to handle webhook requests: you can opt to queue a job or listen to the events the package will fire.
 
 
### Handling webhook requests using jobs 
If you want to do something when a specific event type comes in you can define a job that does the work. Here's an example of such a job:

```php
<?php

namespace App\Jobs\CryptomusWebhook;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Kristof\Cryptomus\Models\CryptomusWebhookCall;

class HandlePaid implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        CryptomusWebhookCall $webhookCall,
    ) {}

    public function handle(): void
    {
        // do your work here
        
        // you can access the payload of the webhook call with `$this->webhookCall->payload`
    }
}
```

We highly recommend that you make this job queueable, because this will minimize the response time of the webhook requests. This allows you to handle more Cryptomus webhook requests and avoid timeouts.

After having created your job you must register it at the `jobs` array in the `cryptomus.php` config file. The key should be the name of [cryptomus payment status](https://doc.cryptomus.com/payments/payment-statuses) where but with the `.` replaced by `_`. The value should be the fully qualified classname.

```php
// config/cryptomus.php

'jobs' => [
    'paid' =>  \App\Jobs\CryptomusWebhook\HandlePaid::class,
],
```

### Handling webhook requests using events

Instead of queueing jobs to perform some work when a webhook request comes in, you can opt to listen to the events this package will fire. Whenever a valid request hits your app, the package will fire a `cryptomus::<name-of-the-payment-status>`.

The payload of the events will be the instance of `CryptomusWebhookCall` that was created for the incoming request. 

Let's take a look at how you can listen for such an event. In the `EventServiceProvider` you can register listeners.

```php
/**
 * The event listener mappings for the application.
 *
 * @var array
 */
protected $listen = [
    'cryptomus::paid' => [
        App\Listeners\PaymentPaidListener::class,
    ],
];
```

Here's an example of such a listener:

```php
<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Kristof\Cryptomus\Models\CryptomusWebhookCall;

class PaymentPaidListener implements ShouldQueue
{
    public function handle(CryptomusWebhookCall $webhookCall): void
    {
        // do your work here

        // you can access the payload of the webhook call with `$webhookCall->payload`
    }   
}
```

We highly recommend that you make the event listener queueable, as this will minimize the response time of the webhook requests. This allows you to handle more Cryptomus webhook requests and avoid timeouts.

The above example is only one way to handle events in Laravel. To learn the other options, read [the Laravel documentation on handling events](https://laravel.com/docs/10.x/events). 

## Advanced usage

### Retry handling a webhook

All incoming webhook requests are written to the database. This is incredibly valuable when something goes wrong while handling a webhook call. You can easily retry processing the webhook call, after you've investigated and fixed the cause of failure, like this:

```php
use Kristof\Cryptomus\Models\CryptomusWebhookCall;

CryptomusWebhookCall::find($id)->process();
```

### Performing custom logic

You can add some custom logic that should be executed before and/or after the scheduling of the queued job by using your own model. You can do this by specifying your own model in the `model` key of the `cryptomus` config file. The class should extend `Kristof\Cryptomus\Models\CryptomusWebhookCall`.

Here's an example:

```php
use Kristof\Cryptomus\Models\CryptomusWebhookCall;

class MyCustomWebhookCall extends CryptomusWebhookCall
{
    public function process(): void
    {
        // do some custom stuff beforehand
        
        parent::process();
        
        // do some custom stuff afterwards
    }
}
```

