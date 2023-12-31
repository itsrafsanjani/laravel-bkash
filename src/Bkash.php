<?php

namespace ItsRafsanJani\Bkash;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use ItsRafsanJani\Bkash\Data\CreatePaymentData;

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
        ])->post($this->baseUrl.'/checkout/token/grant', [
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

    public function throwIfError(?array $response): void
    {
        if (is_null($response)) {
            throw new \Exception('Empty response from Bkash or you are in a test environment.');
        }

        if ($response['statusCode'] != 0000 && $response['statusCode'] != 2062) {
            Log::error(json_encode($response));
            throw new \Exception($response['statusMessage']);
        }
    }

    public function createPayment(CreatePaymentData $data)
    {
        $response = Http::withHeaders($this->headers)
            ->post($this->baseUrl.'/checkout/create', $data)
            ->json();

        $this->throwIfError($response);

        return $response;
    }

    public function executePayment($paymentID)
    {
        $response = Http::withHeaders($this->headers)
            ->post($this->baseUrl.'/checkout/execute', [
                'paymentID' => $paymentID,
            ])
            ->json();

        $this->throwIfError($response);

        return $response;
    }

    public function queryPayment($paymentID)
    {
        $response = Http::withHeaders($this->headers)
            ->post($this->baseUrl.'/checkout/payment/status', [
                'paymentID' => $paymentID,
            ])
            ->json();

        $this->throwIfError($response);

        return $response;
    }

    public function searchTransaction($trdID)
    {
        $response = Http::withHeaders($this->headers)
            ->post($this->baseUrl.'/checkout/general/searchTransaction', [
                'trxID' => $trdID,
            ])
            ->json();

        $this->throwIfError($response);

        return $response;
    }
}
