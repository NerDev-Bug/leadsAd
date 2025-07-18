<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\product;

class productsController extends Controller
{
    public function index(Request $request)
    {
        $products = product::all();

        $products = $products->map(function ($product) {
            $filename1 = $product->image1 ? str_replace('products/', '', $product->image1) : null;
            $filename2 = $product->image2 ? str_replace('products/', '', $product->image2) : null;

            // Full public URL pointing to public/products_image/
            $product->image1_url = $filename1 ? asset("products_image/$filename1") : null;
            $product->image2_url = $filename2 ? asset("products_image/$filename2") : null;

            return $product;
        });

        return response()->json($products);
    }
}
