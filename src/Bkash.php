<?php

namespace ItsRafsanJani\Bkash;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Bkash
{
    public string $baseUrl;

    public array $headers;

    public function __construct()
    {
        $this->setBaseUrl();
        $this->getOrSetTokenInCache();
        $this->setHeaders();
    }

    public function setBaseUrl()
    {
        if (config('bkash.sandbox')) {
            $this->baseUrl = 'https://tokenized.sandbox.bka.sh/v1.2.0-beta/tokenized';
        } else {
            $this->baseUrl = 'https://tokenized.pay.bka.sh/v1.2.0-beta/tokenized';
        }
    }

    public function getOrSetTokenInCache()
    {
        if (Cache::has('bkash_token')) {
            return true;
        }

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'password' => config('bkash.password'),
            'username' => config('bkash.username'),
        ])->post($this->baseUrl . '/checkout/token/grant', [
            'app_key' => config('bkash.app_key'),
            'app_secret' => config('bkash.app_secret'),
            'refresh_token' => null,
        ])->json();

        $this->throwIfError($response);

        Cache::put('bkash_token', $response['id_token'], now()->addMinutes(59));

        return true;
    }

    protected function setHeaders()
    {
        $this->headers = [
            'Content-Type' => 'application/json',
            'Authorization' => Cache::get('bkash_token'),
            'X-APP-KEY' => config('bkash.app_key'),
        ];
    }

    public function throwIfError(array $response): void
    {
        if ($response['statusCode'] != 0000) {
            Log::error(json_encode($response));
            throw new \Exception($response['statusMessage']);
        }
    }

    /*
     * Start payment related methods.
     */
    public function createPayment(array $data)
    {
        $data['callbackURL'] = config('bkash.callbackURL');

        $response = Http::withHeaders($this->headers)
            ->post($this->baseUrl . '/checkout/create', $data)
            ->json();

        $this->throwIfError($response);

        return $response;
    }
}
