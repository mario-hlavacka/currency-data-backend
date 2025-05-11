<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UnifiedCurrencyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'name' => $this['name'],
            'icon' => $this['image'] ?? $this['logo'],
            'current_price' => $this['current_price'] ?? $this['quote']['EUR']['price'],
            'percent_change_24h' => $this['price_change_percentage_24h'] ?? $this['quote']['EUR']['percent_change_24h'],
            'market_cap' => $this['market_cap'] ?? $this['quote']['EUR']['market_cap'],
            'source' => isset($this['market_cap']) ? 'CoinGecko' : 'CoinMarketCap',
        ];
    }
}
