<?php

namespace Kristof\Cryptomus\Http\Middleware;
use Closure;
use Illuminate\Support\Facades\Log;
use Kristof\Cryptomus\Exceptions\WebhookFailed;
class VerifySignature
{
    /**
     * @throws WebhookFailed
     */
    public function handle($request, Closure $next){
        $signature = $request->input()['sign'];
        if (! $signature) {
            throw WebhookFailed::missingSignature();
        }
        if (! $this->isValid($signature, $request->getContent())) {
            throw WebhookFailed::invalidSignature($signature);
        }

        return $next($request);
    }
    protected function isValid(string $signature, string $payload): bool
    {
        $paymentKey = config('cryptomus.PaymentKey');
        if (empty($paymentKey)) {
            throw WebhookFailed::sharedSecretNotSet();
        }
        $data = json_decode($payload, true);
        unset($data['sign']);
        $computedHash = md5(base64_encode(json_encode($data, JSON_UNESCAPED_UNICODE)) . $paymentKey);
        return hash_equals($signature, $computedHash);
    }
}
