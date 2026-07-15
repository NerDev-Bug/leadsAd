<?php

namespace App\Console\Commands;

use App\Models\News;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class FetchFacebookPosts extends Command
{
    protected $signature = 'facebook:fetch-posts';
    protected $description = 'Fetch new image posts from Facebook Page and create news articles';

    public function handle(): int
    {
        $pageId = config('facebook.page_id');
        $accessToken = config('facebook.access_token');

        if (!$pageId || !$accessToken) {
            $this->error('FB_PAGE_ID and FB_ACCESS_TOKEN must be set in .env');
            return static::FAILURE;
        }

        $url = "https://graph.facebook.com/v21.0/{$pageId}/photos";
        $response = Http::get($url, [
            'fields' => 'id,name,created_time,source,link',
            'access_token' => $accessToken,
            'type' => 'uploaded',
            'limit' => 50,
        ]);

        if (!$response->successful()) {
            $this->error('Facebook API error: ' . $response->body());
            return static::FAILURE;
        }

        $posts = $response->json('data', []);
        $imported = 0;

        foreach ($posts as $post) {
            $postId = $post['id'] ?? null;
            $message = $post['name'] ?? null;
            $picture = $post['source'] ?? null;

            if (!$postId || !$message || !$picture) {
                continue;
            }

            if (News::where('fb_post_id', $postId)->exists()) {
                continue;
            }

            $imagePath = $this->downloadImage($picture, $postId);
            if (!$imagePath) {
                $this->warn("Failed to download image for post {$postId}");
                continue;
            }

            $title = $this->extractTitle($message);
            $publishedAt = $post['created_time'] ?? now();

            News::create([
                'title' => $title,
                'content' => $message,
                'published_at' => $publishedAt,
                'featured_image' => $imagePath,
                'fb_post_id' => $postId,
            ]);

            $imported++;
            $this->info("Imported post {$postId}: {$title}");
        }

        $this->info("Done. Imported {$imported} new posts.");
        return static::SUCCESS;
    }

    private function downloadImage(string $url, string $postId): ?string
    {
        $response = Http::get($url);
        if (!$response->successful()) {
            return null;
        }

        $extension = 'jpg';
        $contentType = $response->header('Content-Type', '');
        if (str_contains($contentType, 'png')) {
            $extension = 'png';
        } elseif (str_contains($contentType, 'webp')) {
            $extension = 'webp';
        }

        $filename = 'fb_' . md5($postId) . '_' . time() . '.' . $extension;
        $path = public_path('news_image/' . $filename);
        file_put_contents($path, $response->body());

        return 'news/' . $filename;
    }

    private function extractTitle(string $message): string
    {
        $firstLine = strtok($message, "\n");
        if (mb_strlen($firstLine) > 255) {
            return mb_substr($firstLine, 0, 252) . '...';
        }
        return $firstLine;
    }
}
