<?php

namespace App\Http\Controllers;

use App\Models\ArchiveNews;
use App\Models\News;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ArchiveNewsController extends Controller
{
    public function index(Request $request)
    {
        $perPage = 20;
        $query = ArchiveNews::orderBy('created_at', 'desc');

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

        $archived = $query->paginate($perPage)->appends(['search' => $search]);

        // Provide JSON for modal consumption or render an Inertia page if preferred
        if ($request->wantsJson()) {
            return response()->json([
                'data' => $archived->items(),
                'pagination' => [
                    'current_page' => $archived->currentPage(),
                    'last_page' => $archived->lastPage(),
                    'per_page' => $archived->perPage(),
                    'total' => $archived->total(),
                    'from' => $archived->firstItem() ?? 0,
                    'to' => $archived->lastItem() ?? 0,
                    'has_more_pages' => $archived->hasMorePages(),
                    'has_previous_page' => !$archived->onFirstPage(),
                ],
            ]);
        }

        return Inertia::render('SubPage/ArchiveNews', [
            'archived' => $archived->items(),
            'pagination' => [
                'current_page' => $archived->currentPage(),
                'last_page' => $archived->lastPage(),
                'per_page' => $archived->PerPage(),
                'total' => $archived->total(),
                'from' => $archived->firstItem() ?? 0,
                'to' => $archived->lastItem() ?? 0,
                'has_more_pages' => $archived->hasMorePages(),
                'has_previous_page' => !$archived->onFirstPage(),
            ],
            'search' => $search,
        ]);
    }

    public function restore(Request $request, ArchiveNews $archiveNews)
    {
        // Ensure destination dir exists
        $destDir = public_path('news_image');
        if (!is_dir($destDir)) {
            @mkdir($destDir, 0775, true);
        }

        // Move featured image back
        $featuredPath = null;
        if ($archiveNews->featured_image) {
            $filename = basename($archiveNews->featured_image); // already in archive_news/...
            $base = $this->stripCyclePrefix($filename);
            $source = public_path('archive_news/' . $filename);
            $targetFilename = 'rest_' . uniqid() . '_' . $base;
            $target = $destDir . DIRECTORY_SEPARATOR . $targetFilename;
            if (is_file($source)) {
                @rename($source, $target);
                $featuredPath = 'news/' . $targetFilename;
            }
        }

        // Move secondary images back
        $image2List = [];
        if ($archiveNews->featured_image_2) {
            foreach (explode(',', $archiveNews->featured_image_2) as $img) {
                $img = trim($img);
                if ($img === '') continue;
                $filename = basename($img);
                $base = $this->stripCyclePrefix($filename);
                $source = public_path('archive_news/' . $filename);
                $targetFilename = 'rest_' . uniqid() . '_' . $base;
                $target = $destDir . DIRECTORY_SEPARATOR . $targetFilename;
                if (is_file($source)) {
                    @rename($source, $target);
                    $image2List[] = 'news/' . $targetFilename;
                }
            }
        }

        // Create new news record
        $news = News::create([
            'title' => $archiveNews->title,
            'content' => $archiveNews->content,
            'published_at' => $archiveNews->published_at,
            'featured_image' => $featuredPath,
            'featured_image_2' => empty($image2List) ? null : implode(',', $image2List),
        ]);

        // Remove archive record
        $archiveNews->delete();

        // Return JSON for modal
        return response()->json([
            'success' => true,
            'restored_id' => $news->id,
        ]);
    }

    /**
     * Strip cyclic prefixes like arch_<uniq>_ or rest_<uniq>_ from the start of a filename.
     */
    private function stripCyclePrefix(string $filename): string
    {
        while (preg_match('/^(arch|rest)_[^_]+_(.+)$/', $filename, $m)) {
            $filename = $m[2];
        }
        return $filename;
    }
}
