<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\News;

class newsController extends Controller
{
    public function index(Request $request)
    {
        $news = News::all();

        $news = $news->map(function ($item) {
            $filename1 = $item->featured_image ? str_replace('news/', '', $item->featured_image) : null;
            $item->featured_image_url = $filename1 ? asset("news_image/$filename1") : null;

            if ($item->featured_image_2) {
                $filenames = collect(explode(',', $item->featured_image_2))->filter();
                $item->featured_image_2_url = $filenames->map(function ($img) {
                    $filename = str_replace('news/', '', trim($img));
                    return asset("news_image/$filename");
                })->values();
            } else {
                $item->featured_image_2_url = [];
            }
            return $item;
        });

        return response()->json($news);
    }
}
