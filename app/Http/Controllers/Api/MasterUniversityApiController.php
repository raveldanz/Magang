<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\MasterUniversityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MasterUniversityApiController extends Controller
{
    /**
     * Endpoint API pencarian Master Perguruan Tinggi Nasional
     */
    public function search(Request $request, MasterUniversityService $service): JsonResponse
    {
        $q = (string) $request->query('q', '');
        $limit = (int) $request->query('limit', 25);
        $limit = max(1, min(100, $limit));

        $results = $service->search($q, $limit);

        return response()->json([
            'status' => 'success',
            'query' => $q,
            'total' => $results->count(),
            'data' => $results->values(),
        ]);
    }
}
