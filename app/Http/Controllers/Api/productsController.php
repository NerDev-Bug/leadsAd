<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\product;

class productsController extends Controller
{
    public function index(Request $request)
    {
        $product = product::all();
        return response()->json($product);
    }
}
