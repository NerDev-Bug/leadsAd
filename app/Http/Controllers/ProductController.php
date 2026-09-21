<?php

namespace App\Http\Controllers;

use App\Models\product;
use App\Support\SecureUpload;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $perPage = 15;
        $query = product::orderBy('created_at', 'desc');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%$search%")
                  ->orWhere('features', 'like', "%$search%")
                  ->orWhere('dosage', 'like', "%$search%")
                  ->orWhere('target', 'like', "%$search%")
                  ->orWhere('category', 'like', "%$search%")
                  ->orWhere('type', 'like', "%$search%")
                ;
            });
        } else {
            $search = '';
        }

        $products = $query->paginate($perPage)->appends(['search' => $search]);

        return inertia('SubPage/Products', [
            'products' => $products->items(),
            'pagination' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
                'from' => $products->firstItem() ?? 0,
                'to' => $products->lastItem() ?? 0,
                'has_more_pages' => $products->hasMorePages(),
                'has_previous_page' => !$products->onFirstPage(),
            ],
            'search' => $search,
        ]);
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'description' => 'required|string',
            'features' => 'required|string',
            'dosage' => 'required|string',
            'target' => 'required|string',
            'category' => 'required|string',
            'type' => 'required|string',
            'image1' => 'required|file|image|max:51200',
            'image2' => 'required|file|image|max:51200',
        ]);

        if ($request->hasFile('image1')) {
            $filename = SecureUpload::storeImage($request->file('image1'), 'products_image');
            $validated['image1'] = 'products/' . $filename;
        }
        if ($request->hasFile('image2')) {
            $filename = SecureUpload::storeImage($request->file('image2'), 'products_image');
            $validated['image2'] = 'products/' . $filename;
        }

        product::create($validated);

        return redirect('/products')->with('success', 'Product added successfully!');
    }

    public function show(product $product)
    {
        //
    }

    public function edit(product $product)
    {
        //
    }

    public function update(Request $request, product $product)
    {
        $validated = $request->validate([
            'description' => 'required|string',
            'features' => 'required|string',
            'dosage' => 'required|string',
            'target' => 'required|string',
            'category' => 'required|string',
            'type' => 'required|string',
            'image1' => 'nullable|file|image|max:51200',
            'image2' => 'nullable|file|image|max:51200',
        ]);

        if ($request->hasFile('image1')) {
            SecureUpload::deleteFromPublic('products_image', $product->image1);
            $filename = SecureUpload::storeImage($request->file('image1'), 'products_image');
            $validated['image1'] = 'products/' . $filename;
        }

        if ($request->hasFile('image2')) {
            SecureUpload::deleteFromPublic('products_image', $product->image2);
            $filename = SecureUpload::storeImage($request->file('image2'), 'products_image');
            $validated['image2'] = 'products/' . $filename;
        }

        $product->update($validated);

        return redirect('/products')->with('success', 'Product updated successfully!');
    }

    public function destroy(product $product)
    {
        SecureUpload::deleteFromPublic('products_image', $product->image1);
        SecureUpload::deleteFromPublic('products_image', $product->image2);

        $product->delete();

        return redirect('/products')->with('success', 'Product deleted successfully!');
    }
}
