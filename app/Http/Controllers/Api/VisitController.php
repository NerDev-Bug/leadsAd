<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Visit;
use Carbon\Carbon;

class VisitController extends Controller
{
    public function stats()
    {
        $now = Carbon::now();

        return response()->json([
            'total_visits' => Visit::count(),
            'unique_visitors' => Visit::distinct('ip_address')->count('ip_address'),
            'today' => [
                'visits' => Visit::whereDate('created_at', $now->toDateString())->count(),
                'unique' => Visit::whereDate('created_at', $now->toDateString())->distinct('ip_address')->count('ip_address'),
            ],
            'last_7_days' => Visit::where('created_at', '>=', $now->copy()->subDays(7))->count(),
        ]);
    }
}
