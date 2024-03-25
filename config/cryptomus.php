<?php
return [
    'MerchantID' => env('CRYPTOMUS_MERCHANT_ID'),
    'PaymentKey'=>env('CRYPTOMUS_PAYMENT_KEY'),
    'webhookJobs' => [],
    'webhookModel' => Kristof\Cryptomus\Models\CryptomusWebhookCall::class,
];
