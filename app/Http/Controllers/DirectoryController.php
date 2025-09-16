<?php

namespace App\Http\Controllers;

use App\Models\Directory;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DirectoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $perPage = 20;
        $query = Directory::orderBy('created_at', 'desc');

        // Search logic
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('area', 'like', "%$search%")
                  ->orWhere('region', 'like', "%$search%")
                  ->orWhere('place', 'like', "%$search%")
                  ->orWhere('business_name', 'like', "%$search%")
                  ->orWhere('address', 'like', "%$search%")
                  ->orWhere('contact_name', 'like', "%$search%")
                  ->orWhere('contact_no', 'like', "%$search%");
            });
        } else {
            $search = '';
        }

        $directories = $query->paginate($perPage)->appends(['search' => $search]);

        return Inertia::render('SubPage/Directories', [
            'directories' => $directories->items(),
            'pagination' => [
                'current_page' => $directories->currentPage(),
                'last_page' => $directories->lastPage(),
                'per_page' => $directories->perPage(),
                'total' => $directories->total(),
                'from' => $directories->firstItem() ?? 0,
                'to' => $directories->lastItem() ?? 0,
                'has_more_pages' => $directories->hasMorePages(),
                'has_previous_page' => !$directories->onFirstPage(),
            ],
            'search' => $search,
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
        $data = $request->validate([
            'area' => 'required|string',
            'region' => 'required|string',
            'place' => 'required|string',
            'business_name' => 'required|string',
            'address' => 'nullable|string',
            'contact_name' => 'required|string',
            'contact_no' => 'required|string',
        ]);

        Directory::create($data);
        return redirect()->route('directories');
    }

    /**
     * Display the specified resource.
     */
    public function show(Directory $directory)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Directory $directory)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Directory $directory)
    {
        $data = $request->validate([
            'area' => 'required|string',
            'region' => 'required|string',
            'place' => 'required|string',
            'business_name' => 'required|string',
            'address' => 'nullable|string',
            'contact_name' => 'required|string',
            'contact_no' => 'required|string',
        ]);

        $directory->update($data);
        return redirect()->route('directories');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Directory $directory)
    {
        $directory->delete();
        return redirect()->route('directories');
    }
}
