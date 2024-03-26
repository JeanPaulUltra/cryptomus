<?php

namespace Kristof\Cryptomus\Exceptions;
use Exception;
use Kristof\Cryptomus\Models\CryptomusWebhookCall;
class WebhookFailed extends Exception
{
    public static function missingSignature()
    {
        return new static('The request did not contain a signature');
    }
    public static function invalidSignature($signature)
    {
        return new static("The signature `{$signature}` found in the payload `sign` is invalid. Make sure that the `Cryptomus.PaymentKey` config key is set to the value you found on the Cryptomus Merchants dashboard. If you are caching your config try running `php artisan clear:cache` to resolve the problem.");
    }
    public static function sharedSecretNotSet()
    {
        return new static('The Cryptomus payment key is not set. Make sure that the `Cryptomus.PaymentKey` config key is set to the value you found on the Cryptomus Merchants dashboard.');
    }

    public static function jobClassDoesNotExist(string $jobClass, CryptomusWebhookCall $webhookCall)
    {
        return new static("Could not process webhook id `{$webhookCall->id}` of type `{$webhookCall->type} because the configured jobclass `$jobClass` does not exist.");
    }

    public static function missingType()
    {
        return new static('The webhook call did not contain a type. Valid Cryptomus webhook calls should always contain a type.');
    }

    public function render($request)
    {
        return response(['error' => $this->getMessage()], 400);
    }
}
