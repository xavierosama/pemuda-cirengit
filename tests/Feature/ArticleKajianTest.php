<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ArticleKajianTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_landing_and_article_detail_are_available(): void
    {
        $category = ArticleCategory::create([
            'name' => 'Aqidah',
            'slug' => 'aqidah',
            'is_active' => true,
        ]);
        $article = Article::create([
            'article_category_id' => $category->id,
            'title' => 'Kajian Tauhid',
            'slug' => 'kajian-tauhid',
            'excerpt' => 'Ringkasan kajian tauhid.',
            'content' => "Paragraf Indonesia.\n\nالحمد لله رب العالمين",
            'status' => 'published',
            'published_at' => now()->subDay(),
            'author_id' => User::factory()->create()->id,
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('Artikel')
            ->assertSee('Aqidah')
            ->assertSee('Kajian Tauhid');

        $this->get(route('public.articles.index'))
            ->assertOk()
            ->assertSee('Kajian Tauhid');

        $this->get('/kajian')
            ->assertOk()
            ->assertSee('Kajian Tauhid');

        $this->get(route('public.categories.show', $category))
            ->assertOk()
            ->assertSee('Kajian Tauhid');

        $this->get(route('public.articles.show', $article))
            ->assertOk()
            ->assertSee('الحمد لله رب العالمين')
            ->assertDontSee('bg-gradient-to-br from-emerald-900 via-emerald-700 to-teal-900 p-6 text-white', false)
            ->assertDontSee('Video Kajian');

        $this->get('/kajian/'.$article->slug)
            ->assertOk()
            ->assertSee('Kajian Tauhid');

        $this->assertSame(1, $article->fresh()->views_count);
    }

    public function test_admin_can_manage_articles_and_upload_banner(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['role' => 'admin']);
        $category = ArticleCategory::create([
            'name' => 'Fiqih',
            'slug' => 'fiqih',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->post(route('articles.store'), [
            'title' => 'Adab Menuntut Ilmu',
            'slug' => '',
            'article_category_id' => $category->id,
            'excerpt' => 'Deskripsi singkat.',
            'content' => 'Isi kajian.',
            'youtube_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'status' => 'published',
            'published_at' => now()->format('Y-m-d H:i:s'),
            'is_featured' => '1',
            'banner_image' => $this->tinyPngUpload(),
        ]);

        $article = Article::where('title', 'Adab Menuntut Ilmu')->firstOrFail();

        $response->assertRedirect(route('articles.show', $article));
        $this->assertSame('adab-menuntut-ilmu', $article->slug);
        $this->assertTrue($article->is_featured);
        $this->assertNotNull($article->banner_image);
        Storage::disk('public')->assertExists($article->banner_image);

        $this->get(route('public.articles.show', $article))
            ->assertOk()
            ->assertSee($article->banner_url, false)
            ->assertSee('https://www.youtube.com/embed/dQw4w9WgXcQ', false);
    }

    public function test_article_form_loads_tinymce_for_content_field(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('articles.create'))
            ->assertOk()
            ->assertSee('content-editor')
            ->assertSee('Preview Artikel')
            ->assertSee('cdn.jsdelivr.net/npm/tinymce', false)
            ->assertSee('window.tinymce', false)
            ->assertSee("selector: '#content-editor'", false)
            ->assertSee('triggerSave', false)
            ->assertSee('fontfamily fontsize', false)
            ->assertSee('font_size_formats', false)
            ->assertSee('font_family_formats', false)
            ->assertSee('images_upload_handler', false)
            ->assertSee('paste_data_images: false', false)
            ->assertSee('openPreview', false)
            ->assertSee('youtubeEmbedUrl', false)
            ->assertSee('createObjectURL', false)
            ->assertSee('article-preview-banner-section', false)
            ->assertDontSee('article-preview-banner-placeholder', false)
            ->assertSee('bullist numlist', false)
            ->assertSee('blockquote', false)
            ->assertSee('removeformat', false)
            ->assertSee('ltr rtl', false);
    }

    public function test_article_content_is_sanitized_before_rendering(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->post(route('articles.store'), [
            'title' => 'Konten Aman',
            'content' => '<h2>Judul</h2><p onclick="alert(1)" style="font-size:18px;font-family:Georgia,serif;text-align:center;position:absolute;color:red">Paragraf <strong>tebal</strong></p><hr><table><tbody><tr><td colspan="2" style="color:red">Tabel</td></tr></tbody></table><img src="/storage/article-content/gambar.webp" alt="Gambar"><img src="data:image/png;base64,AAAA"><script>alert(1)</script><iframe src="https://example.com"></iframe><a href="javascript:alert(1)">bad</a><a href="https://example.com" target="_blank">ok</a>',
            'status' => 'published',
            'published_at' => now()->format('Y-m-d H:i:s'),
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('articles', ['title' => 'Konten Aman']);

        $article = Article::where('title', 'Konten Aman')->firstOrFail();

        $this->assertStringContainsString('<h2>Judul</h2>', $article->content);
        $this->assertStringContainsString('<strong>tebal</strong>', $article->content);
        $this->assertStringContainsString('<hr>', $article->content);
        $this->assertStringContainsString('colspan="2"', $article->content);
        $this->assertStringContainsString('font-size: 18px', $article->content);
        $this->assertStringContainsString('font-family: Georgia, serif', $article->content);
        $this->assertStringContainsString('text-align: center', $article->content);
        $this->assertStringContainsString('/storage/article-content/gambar.webp', $article->content);
        $this->assertStringNotContainsString('color:red', $article->content);
        $this->assertStringNotContainsString('position:absolute', $article->content);
        $this->assertStringNotContainsString('data:image', $article->content);
        $this->assertStringNotContainsString('onclick', $article->content);
        $this->assertStringNotContainsString('<script', $article->content);
        $this->assertStringNotContainsString('<iframe', $article->content);
        $this->assertStringNotContainsString('javascript:', $article->content);
        $this->assertStringContainsString('rel="noopener noreferrer"', $article->content);

        $this->get(route('public.articles.show', $article))
            ->assertOk()
            ->assertSee('<div class="article-content">', false)
            ->assertSee('<h2>Judul</h2>', false)
            ->assertSee('<hr>', false)
            ->assertDontSee('<script>alert', false)
            ->assertDontSee('<iframe', false)
            ->assertDontSee('onclick', false);
    }

    public function test_editor_image_upload_is_validated_and_stored(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)
            ->withHeader('Accept', 'application/json')
            ->post(route('articles.editor-image-upload'), [
                'file' => $this->tinyPngUpload(),
            ]);

        $response->assertOk()
            ->assertJsonStructure(['location']);

        $location = $response->json('location');
        $this->assertStringStartsWith('/storage/article-content/', $location);
        Storage::disk('public')->assertExists(str_replace('/storage/', '', $location));

        $this->actingAs($admin)
            ->withHeader('Accept', 'application/json')
            ->post(route('articles.editor-image-upload'), [
                'file' => UploadedFile::fake()->create('dokumen.pdf', 10, 'application/pdf'),
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['file']);
    }

    public function test_invalid_article_media_and_youtube_are_rejected(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->post(route('articles.store'), [
            'title' => 'Kajian Invalid',
            'content' => 'Isi kajian.',
            'youtube_url' => 'https://example.com/video',
            'status' => 'draft',
        ])->assertSessionHasErrors(['youtube_url']);

        $this->actingAs($admin)->post(route('articles.store'), [
            'title' => 'Kajian Invalid File',
            'content' => 'Isi kajian.',
            'status' => 'draft',
            'banner_image' => UploadedFile::fake()->create('file.pdf', 10, 'application/pdf'),
        ])->assertSessionHasErrors(['banner_image']);
    }

    public function test_member_cannot_access_admin_article_management(): void
    {
        $member = User::factory()->create(['role' => 'member']);

        $this->actingAs($member)
            ->get(route('articles.index'))
            ->assertRedirect(route('member.home'));
    }

    private function tinyPngUpload(): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'article-banner');
        file_put_contents($path, base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/p9sAAAAASUVORK5CYII='));

        return new UploadedFile($path, 'kajian.png', 'image/png', null, true);
    }
}
