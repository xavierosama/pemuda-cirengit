<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('article_category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('excerpt')->nullable();
            $table->longText('content');
            $table->string('banner_image')->nullable();
            $table->string('youtube_url')->nullable();
            $table->string('status')->default('draft')->index();
            $table->boolean('is_featured')->default(false)->index();
            $table->unsignedBigInteger('views_count')->default(0);
            $table->foreignId('author_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('managed_by_bidang_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->timestamp('published_at')->nullable()->index();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};
