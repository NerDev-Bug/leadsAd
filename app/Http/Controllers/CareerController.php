<?php

namespace App\Http\Controllers;

use App\Models\Career;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CareerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $perPage = 20;
        $query = Career::orderBy('created_at', 'desc');

        // Search logic
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('employment_type', 'like', "%$search%")
                  ->orWhere('position', 'like', "%$search%")
                  ->orWhere('details', 'like', "%$search%")
                  ->orWhere('location', 'like', "%$search%")
                  ->orWhere('job_description', 'like', "%$search%")
                  ->orWhere('qualifications', 'like', "%$search%")
                ;
            });
        } else {
            $search = '';
        }

        $careers = $query->paginate($perPage)->appends(['search' => $search]);
        return Inertia::render('SubPage/Careers', [
            'careers' => $careers->items(),
            'pagination' => [
                'current_page' => $careers->currentPage(),
                'last_page' => $careers->lastPage(),
                'per_page' => $careers->perPage(),
                'total' => $careers->total(),
                'from' => $careers->firstItem() ?? 0,
                'to' => $careers->lastItem() ?? 0,
                'has_more_pages' => $careers->hasMorePages(),
                'has_previous_page' => !$careers->onFirstPage(),
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
            'employment_type' => 'required|string',
            'position' => 'required|string',
            'details' => 'required|string',
            'location' => 'required|string',
            'job_description' => 'required|string',
            'qualifications' => 'required|string',
        ]);
        Career::create($data);
        return redirect()->route('careers');
    }

    /**
     * Display the specified resource.
     */
    public function show(Career $career)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Career $career)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Career $career)
    {
        $data = $request->validate([
            'employment_type' => 'required|string',
            'position' => 'required|string',
            'details' => 'required|string',
            'location' => 'required|string',
            'job_description' => 'required|string',
            'qualifications' => 'required|string',
        ]);
        $career->update($data);
        return redirect()->route('careers');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Career $career)
    {
        $career->delete();
        return redirect()->route('careers');
    }
}
