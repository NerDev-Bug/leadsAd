<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Career;

class careersController extends Controller
{
    public function index(Request $request)
    {
        $careers = Career::all();
        return response()->json($careers);
    }
}
