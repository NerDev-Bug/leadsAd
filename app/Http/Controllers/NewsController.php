<?php

namespace App\Http\Controllers;

use App\Models\News;
use Illuminate\Http\Request;
use Inertia\Inertia;

class NewsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $perPage = 20;
        $query = News::orderBy('published_at', 'desc');

        // Search logic
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%$search%")
                  ->orWhere('content', 'like', "%$search%")
                ;
            });
        } else {
            $search = '';
        }

        $news = $query->paginate($perPage)->appends(['search' => $search]);

        return inertia('SubPage/News', [
            'news' => $news->items(),
            'pagination' => [
                'current_page' => $news->currentPage(),
                'last_page' => $news->lastPage(),
                'per_page' => $news->perPage(),
                'total' => $news->total(),
                'from' => $news->firstItem() ?? 0,
                'to' => $news->lastItem() ?? 0,
                'has_more_pages' => $news->hasMorePages(),
                'has_previous_page' => !$news->onFirstPage(),
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
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'published_at' => 'required|date',
            'featured_image' => 'nullable|file|image|max:5120', // 5MB max
            'featured_image_2.*' => 'nullable|file|image|max:5120', // Multiple images
        ]);

        // Handle featured image uploads
        if ($request->hasFile('featured_image')) {
            $file = $request->file('featured_image');
            $filename = uniqid() . '_' . $file->getClientOriginalName();
            $file->move(public_path('news_image'), $filename);
            $validated['featured_image'] = 'news/' . $filename;
        }

        // Handle featured_image_2 (multiple files)
        $image2Paths = [];
        if ($request->hasFile('featured_image_2')) {
            foreach ($request->file('featured_image_2') as $file) {
                $filename = uniqid() . '_' . $file->getClientOriginalName();
                $file->move(public_path('news_image'), $filename);
                $image2Paths[] = 'news/' . $filename;
            }
            $validated['featured_image_2'] = implode(',', $image2Paths);
        }

        $news = News::create($validated);

        // Redirect to news page with a flash message
        return redirect('/news')->with('success', 'News article added successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(News $news)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(News $news)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, News $news)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'published_at' => 'required|date',
            'featured_image' => 'nullable|file|image|max:5120', // 5MB max
            'featured_image_2.*' => 'nullable|file|image|max:5120', // Multiple images
        ]);

        // Handle featured_image (single)
        if ($request->hasFile('featured_image')) {
            if ($news->featured_image) {
                $filename = str_replace('news/', '', $news->featured_image);
                @unlink(public_path('news_image/' . $filename));
            }
            $file = $request->file('featured_image');
            $filename = uniqid() . '_' . $file->getClientOriginalName();
            $file->move(public_path('news_image'), $filename);
            $validated['featured_image'] = 'news/' . $filename;
        }

        // Handle featured_image_2 (multiple)
        if ($request->hasFile('featured_image_2')) {
            // Delete old images if you want to clean up
            if ($news->featured_image_2) {
                foreach (explode(',', $news->featured_image_2) as $oldImage) {
                    $filename = str_replace('news/', '', $oldImage);
                    @unlink(public_path('news_image/' . $filename));
                }
            }
            $image2Paths = [];
            foreach ($request->file('featured_image_2') as $file) {
                $filename = uniqid() . '_' . $file->getClientOriginalName();
                $file->move(public_path('news_image'), $filename);
                $image2Paths[] = 'news/' . $filename;
            }
            $validated['featured_image_2'] = implode(',', $image2Paths);
        }

        $news->update($validated);

        return redirect('/news')->with('success', 'News article updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(News $news)
    {
        // Delete associated images
        if ($news->featured_image) {
            $filename = str_replace('news/', '', $news->featured_image);
            @unlink(public_path('news_image/' . $filename));
        }
        if ($news->featured_image_2) {
            foreach (explode(',', $news->featured_image_2) as $oldImage) {
                $filename = str_replace('news/', '', $oldImage);
                @unlink(public_path('news_image/' . $filename));
            }
        }

        $news->delete();

        return redirect('/news')->with('success', 'News article deleted successfully!');
    }
}
