<?php

namespace Kristof\Cryptomus\Http\Controllers;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Kristof\Cryptomus\Http\Middleware\VerifySignature;
class WebhookController extends Controller
{
    public function __construct()
    {
        $this->middleware(VerifySignature::class);
    }

    /**
     * @throws Exception
     */
    public function __invoke(Request $request)
    {
        $payload = $request->input();
        $model = config('cryptomus.webhookModel');
        $cryptomusWebhookCall = $model::create([
            'type' =>  $payload['status'] ?? '',
            'payload' => $payload,
        ]);
        try {
            $cryptomusWebhookCall->process();
        } catch (\Exception $e) {
            $cryptomusWebhookCall->saveException($e);
            throw $e;
        }
    }
}
