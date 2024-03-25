<?php

namespace Kristof\Cryptomus;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

class Cryptomus
{
    const BASE_URI ='https://api.cryptomus.com';

    private $client;

    private $merchantid;

    private $paymentKey;


    public function __construct()
    {
        $this->merchantid = config('cryptomus.MerchantID');
        $this->paymentKey = config('cryptomus.PaymentKey');
        $this->client = new Client([
            'base_uri'=>self::BASE_URI,
            'headers' => [
                'Content-Type' => 'application/json',
                'merchant'=>$this->merchantid,
            ]
        ]);
    }
    public function makeRequest(string $method, string $uri,array $params = []){
        try {
            //$params['url_callback']=env('APP_URL').'/cryptomus/webhook';//set webhook url
            $params['url_callback']='https://play.svix.com/in/e_MzawbTDguMcXHbx5ElPiPTCRqsE/';
            $data =json_encode($params);
            $response = $this->client->request($method, 'v1/test-webhook/'.$uri,
                ['headers' => [
                'sign'=>md5(base64_encode($data) . $this->paymentKey)
                ],
            'body' => $data]);
            return json_decode((string) $response->getBody(), true);
        }catch (GuzzleException $e){
            dd($e->getMessage());
            Log::error($e->getMessage());
        }
    }
    public function createPayment(array $params = []){
        return $this->makeRequest('post', 'payment',$params);
    }
}
