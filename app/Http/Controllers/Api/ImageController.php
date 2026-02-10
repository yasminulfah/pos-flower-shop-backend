<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\JsonResponse;

class ImageController extends Controller
{
    public function search(Request $request): JsonResponse
    {
        $query = $request->query('query');
        $apiKey = env('PEXELS_API_KEY');

        $response = Http::withHeaders([
            'Authorization' => $apiKey
        ])->get('https://api.pexels.com/v1/search', [
            'query' => $query,
            'per_page' => 1
        ]);

        $data = $response->json();

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }
}
