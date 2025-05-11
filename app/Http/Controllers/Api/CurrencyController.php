<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Http\Resources\UnifiedCurrencyResource;
use App\Services\ExternalApiService;

class CurrencyController extends Controller
{
    public function fetchApiData(ExternalApiService $externalApiService)
    {
        $data = $externalApiService->process();

        return $data;
    }
}
