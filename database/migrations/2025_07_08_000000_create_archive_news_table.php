<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('archive_news', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('original_news_id')->nullable();
            $table->string('title');
            $table->longText('content');
            $table->timestamp('published_at')->nullable();
            $table->string('featured_image')->nullable();
            $table->text('featured_image_2')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('archive_news');
    }
};


