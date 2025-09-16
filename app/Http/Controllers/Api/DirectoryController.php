<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Directory;

class DirectoryController extends Controller
{
    public function index()
    {
        $records = Directory::orderBy('region')
            ->orderBy('place')
            ->orderBy('business_name')
            ->get();

        $grouped = [];

        foreach ($records as $record) {
            $grouped[$record->region][$record->place][] = [
                'id' => $record->id,
                'area' => $record->area,
                'business_name' => $record->business_name,
                'address' => $record->address,
                'contact_name' => $record->contact_name,
                'contact_no' => $record->contact_no,
            ];
        }

        return response()->json($grouped ?: new \stdClass());
    }
}
