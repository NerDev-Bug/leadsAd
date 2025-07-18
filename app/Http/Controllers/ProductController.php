<?php

namespace App\Http\Controllers;

use App\Models\product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
        /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $perPage = 20;
        $products = product::orderBy('created_at', 'desc')
            ->paginate($perPage);

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
            ]
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'description' => 'required|string|max:255',
            'features' => 'required|string|max:255',
            'dosage' => 'required|string|max:255',
            'target' => 'required|string|max:255',
            'category' => 'required|string',
            'type' => 'required|string',
            'image1' => 'nullable|file|image|max:2048',
            'image2' => 'nullable|file|image|max:2048',
        ]);

        if ($request->hasFile('image1')) {
            $file = $request->file('image1');
            $filename = uniqid() . '_' . $file->getClientOriginalName();
            $file->move(public_path('products_image'), $filename);
            $validated['image1'] = 'products/' . $filename;
        }
        if ($request->hasFile('image2')) {
            $file = $request->file('image2');
            $filename = uniqid() . '_' . $file->getClientOriginalName();
            $file->move(public_path('products_image'), $filename);
            $validated['image2'] = 'products/' . $filename;
        }

        $product = product::create($validated);

        // Redirect to products page with a flash message
        return redirect('/products')->with('success', 'Product added successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(product $product)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(product $product)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, product $product)
    {
        $validated = $request->validate([
            'description' => 'required|string',
            'features' => 'required|string',
            'dosage' => 'required|string|max:2555',
            'target' => 'required|string|max:2555',
            'category' => 'required|string',
            'type' => 'required|string',
            'image1' => 'nullable|file|image|max:51200',
            'image2' => 'nullable|file|image|max:51200',
        ]);

        // Handle image updates
        if ($request->hasFile('image1')) {
            // Delete old image if exists
            if ($product->image1) {
                $filename = str_replace('products/', '', $product->image1);
                @unlink(public_path('products_image/' . $filename));
            }
            $file = $request->file('image1');
            $filename = uniqid() . '_' . $file->getClientOriginalName();
            $file->move(public_path('products_image'), $filename);
            $validated['image1'] = 'products/' . $filename;
        }

        if ($request->hasFile('image2')) {
            // Delete old image if exists
            if ($product->image2) {
                $filename = str_replace('products/', '', $product->image2);
                @unlink(public_path('products_image/' . $filename));
            }
            $file = $request->file('image2');
            $filename = uniqid() . '_' . $file->getClientOriginalName();
            $file->move(public_path('products_image'), $filename);
            $validated['image2'] = 'products/' . $filename;
        }

        $product->update($validated);

        return redirect('/products')->with('success', 'Product updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(product $product)
    {
        // Delete associated images
        if ($product->image1) {
            $filename = str_replace('products/', '', $product->image1);
            @unlink(public_path('products_image/' . $filename));
        }
        if ($product->image2) {
            $filename = str_replace('products/', '', $product->image2);
            @unlink(public_path('products_image/' . $filename));
        }

        $product->delete();

        return redirect('/products')->with('success', 'Product deleted successfully!');
    }
}
