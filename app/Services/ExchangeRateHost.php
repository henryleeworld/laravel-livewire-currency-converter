<?php

namespace App\Services;

use App\Exceptions\ExchangeRateException;
use App\Services\Contract\ExchangeRate as ExchangeRateContract;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ExchangeRateHost implements ExchangeRateContract
{
    private string $apiUrl;

    public function __construct()
    {
        $this->apiUrl = config('services.exchangerate.scheme') . '://' . config('services.exchangerate.domain');
    }

    /**
     * @inheritdoc
     * @throws ExchangeRateException
     */
    public function convert(string $from, string $to): float
    {
        $cacheKey = sprintf('%s-%s-%s', __CLASS__, $from, $to);
        $ttl = (int) config('services.exchangerate.ttl.convert', 3600);
        return Cache::remember($cacheKey, $ttl, function () use ($from, $to) {
            $uri = sprintf('/convert?access_key=%s&from=%s&to=%s&amount=1', config('services.exchangerate.key'), $from, $to);
            $response = Http::get($this->apiUrl . $uri);
            $array = $response->json();
            if(!$array['success'] ?? false){
                Log::error("Error in API Response of currency conversion", [
                    'class' => __CLASS__,
                    'from' => $from,
                    'to' => $to,
                    'response' => $response->body(),
                    'status' => $response->status(),
                ]);
                throw new ExchangeRateException("Could not convert from $from to $to");
            }

            return (float) $array['result'];
        });
    }

    /**
     * @inheritdoc
     * @throws ExchangeRateException
     */
    public function getAllowedCurrencies(): array
    {
        $cacheKey = sprintf('%s-%s', __CLASS__, 'list');
        $ttl = (int) config('services.exchangerate.ttl.list', 3600);
        return Cache::remember($cacheKey, $ttl, function (){
            $uri = sprintf('/list?access_key=%s', config('services.exchangerate.key'));
            $response = Http::get($this->apiUrl . $uri);
            $array = $response->json();
            if(!$array['success'] ?? false){
                Log::error("Error in API Response of fetching supported currencies", [
                    'class' => __CLASS__,
                    'response' => $response->body(),
                    'status' => $response->status(),
                ]);
                throw new ExchangeRateException("Could not fetch supported currencies");
            }

            return array_keys($array['currencies']);
        });
    }
}