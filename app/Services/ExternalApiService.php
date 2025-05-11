<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;

use App\Http\Resources\UnifiedCurrencyResource;

class ExternalApiService
{
    public function process()
    {
        $redisResponseKey = 'currencies:data';

        try {
            if(Redis::exists($redisResponseKey)) {
                $cachedData = json_decode(Redis::get($redisResponseKey));
                return $cachedData;
            }
        } catch (\Exception $e) {
            Log::error('Connection to redis failed: ', [
                'exception' => $e,
            ]);
            return $this->fetchCurrencyApiData();
        }

        $data = $this->fetchCurrencyApiData();
        Redis::set($redisResponseKey, json_encode($data), 'EX', 60);

        return $data;
    }

    private function fetchCurrencyApiData(): array
    {
        $coingeckoData = $this->fetchCoingeckoApiData();
        $coinmarketcapData = $this->fetchCoinmarketcapApiData();

        $mergedApiData = array_merge($coingeckoData, array_values($coinmarketcapData));

        $unifiedData = UnifiedCurrencyResource::collection($mergedApiData);

        return $unifiedData->resolve();
    }

    private function fetchCoingeckoApiData(): array
    {
        $apiKey = env('COINGECKO_API_KEY');
        $cryptoIds = 'bitcoin,ethereum,tether,ripple,binancecoin';
        $convertTo = 'eur';

        $response = Http::withOptions([
            'verify' => false,
        ])->get('https://api.coingecko.com/api/v3/coins/markets', [
            'vs_currency' => $convertTo,
            'ids' => $cryptoIds,
            'x_cg_demo_api_key' => $apiKey,
        ]);

        if ($response->failed()) {
            Log::error('CoinGecko API call failed: ' . $response->status(), [
                'exception' => $response->body(),
            ]);
            return array();
        }

        return $response->json();
    }

    private function fetchCoinmarketcapApiData(): array
    {
        $apiKey = env('COINMARKETCAP_API_KEY');
        $cryptoSlugs = 'bitcoin,ethereum,tether,ripple,bnb';
        $convertTo = 'EUR';

        $responseQuotes = Http::withOptions([
            'verify' => false,
            'headers' => [
                'X-CMC_PRO_API_KEY' => $apiKey,
            ]
        ])->get('https://pro-api.coinmarketcap.com/v2/cryptocurrency/quotes/latest', [
            'convert' => $convertTo,
            'slug' => $cryptoSlugs,
        ]);

        if ($responseQuotes->failed()) {
            Log::error('CoinMarketCap API call failed: ' . $responseQuotes->status(), [
                'exception' => $responseQuotes->body(),
            ]);
            return array();
        }

        $responseInfo = Http::withOptions([
            'verify' => false,
            'headers' => [
                'X-CMC_PRO_API_KEY' => $apiKey,
            ]
        ])->get('https://pro-api.coinmarketcap.com/v2/cryptocurrency/info', [
            'slug' => $cryptoSlugs,
            'aux' => 'logo',
        ]);
        
        if ($responseInfo->failed()) {
            Log::error('CoinMarketCap API call failed: ' . $responseInfo->status(), [
                'exception' => $responseInfo,
            ]);
            return array();
        }

        $responseQuotesJson = $responseQuotes->json('data');
        $responseInfoJson = $responseInfo->json('data');

        foreach ($responseQuotesJson as $index => $riadok) {
            $response[] = array_merge($responseQuotesJson[$index], $responseInfoJson[$index]);
        }

        return $response;
    }
}