<?php

namespace ItsRafsanJani\Bkash;

use Illuminate\Support\Facades\Http;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class Bkash
{
    /**
     * @var string $baseUrl
     */
    public string $baseUrl;

    public function __construct()
    {
        $this->baseUrl();
    }

    /**
     * bkash Base Url
     * if sandbox is true it will be sandbox url otherwise it is host url
     */
    public function baseUrl()
    {
        if (config('bkash.sandbox')) {
            $this->baseUrl = 'https://tokenized.sandbox.bka.sh/v1.2.0-beta/tokenized';
        } else {
            $this->baseUrl = 'https://tokenized.pay.bka.sh/v1.2.0-beta/tokenized';
        }
    }

    /**
     * @return string|null
     */
    public function getIp()
    {
        return request()->ip();
    }


    /**
     * @return array|mixed
     * @throws \Exception
     */
    public function getToken()
    {
        session()->forget('bkash_token');
        session()->forget('bkash_token_type');
        session()->forget('bkash_refresh_token');

        $appKey = config('bkash.app_key');
        $appSecret = config('bkash.app_secret');
        $refreshToken = null; // Set your refresh token here

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'password' => config('bkash.password'),
            'username' => config('bkash.username'),
        ])->post("$this->baseUrl/checkout/token/grant", [
            'app_key' => $appKey,
            'app_secret' => $appSecret,
            'refresh_token' => $refreshToken,
        ])->json();


        $this->throwIfError($response);

        session()->put('bkash_token', $response['id_token']);
        session()->put('bkash_token_type', $response['token_type']);
        session()->put('bkash_refresh_token', $response['refresh_token']);

        return true;
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function headers()
    {
        return [
            'Content-Type' => 'application/json',
            'Authorization' => session()->get('bkash_token'),
            'X-APP-KEY' => config('bkash.app_key'),
        ];
    }

    /**
     * @param array $data
     * @return array|mixed
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \Exception
     */
    public function createPayment(array $data)
    {
        $this->getToken();

        $response = Http::withHeaders($this->headers())
            ->post("$this->baseUrl/checkout/create", $data)
            ->json();

        $this->throwIfError($response);

        return $response;
    }

    /**
     * @param array $response
     * @return void
     * @throws \Exception
     */
    public function throwIfError(array $response): void
    {
        if ($response['statusCode'] !== 0000) {
            throw new \Exception($response['statusMessage']);
        }
    }
}
