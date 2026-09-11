@csrf

@php
    $inputClass = 'mt-2 block w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-emerald-600 focus:ring-emerald-600';
    $labelClass = 'block text-sm font-semibold text-slate-700';
    $helperClass = 'mt-1 text-xs leading-5 text-slate-500';
    $existingBannerUrl = ($article ?? null)?->banner_url;
    $previewAuthor = auth()->user()?->member?->full_name ?? auth()->user()?->name ?? 'Pemuda Cirengit';
@endphp

<div class="grid gap-4 sm:gap-6 xl:grid-cols-[minmax(0,1fr)_22rem]">
    <div class="space-y-4 sm:space-y-6">
        <x-ui.card padding="md">
            <div class="mb-5 border-b border-slate-100 pb-4">
                <h3 class="text-base font-bold text-slate-950">Informasi Utama</h3>
                <p class="mt-1 text-sm text-slate-500">Judul, slug, kategori, dan ringkasan kajian.</p>
            </div>
            <div class="grid gap-5 md:grid-cols-2">
                <div class="md:col-span-2">
                    <label for="title" class="{{ $labelClass }}">Judul Artikel</label>
                    <input id="title" name="title" type="text" value="{{ old('title', $article->title ?? '') }}" class="{{ $inputClass }}" required autofocus>
                    @error('title') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="slug" class="{{ $labelClass }}">Slug</label>
                    <input id="slug" name="slug" type="text" value="{{ old('slug', $article->slug ?? '') }}" class="{{ $inputClass }}" placeholder="otomatis-jika-kosong">
                    @error('slug') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="article_category_id" class="{{ $labelClass }}">Kategori</label>
                    <select id="article_category_id" name="article_category_id" class="{{ $inputClass }}">
                        <option value="">Tanpa kategori</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected((string) old('article_category_id', $article->article_category_id ?? '') === (string) $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                    @error('article_category_id') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="managed_by_bidang_id" class="{{ $labelClass }}">Bidang Pengelola</label>
                    <select id="managed_by_bidang_id" name="managed_by_bidang_id" class="{{ $inputClass }}">
                        <option value="">Tidak ditentukan</option>
                        @foreach ($departments as $department)
                            <option value="{{ $department->id }}" @selected((string) old('managed_by_bidang_id', $article->managed_by_bidang_id ?? '') === (string) $department->id)>{{ $department->name }}</option>
                        @endforeach
                    </select>
                    @error('managed_by_bidang_id') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="md:col-span-2">
                    <label for="excerpt" class="{{ $labelClass }}">Deskripsi Singkat</label>
                    <textarea id="excerpt" name="excerpt" rows="3" maxlength="500" class="{{ $inputClass }}">{{ old('excerpt', $article->excerpt ?? '') }}</textarea>
                    <p class="{{ $helperClass }}">Tampil di card artikel dan meta description. Maksimal 500 karakter.</p>
                    @error('excerpt') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>
        </x-ui.card>

        <x-ui.card padding="md">
            <div class="mb-5 border-b border-slate-100 pb-4">
                <h3 class="text-base font-bold text-slate-950">Konten Kajian</h3>
                <p class="mt-1 text-sm text-slate-500">Gunakan editor untuk menyusun konten kajian. Gambar utama artikel tetap menggunakan Banner Artikel, sedangkan gambar di editor hanya untuk pendukung isi kajian.</p>
            </div>
            <label for="content-editor" class="{{ $labelClass }}">Isi Kajian</label>
            <div class="article-editor-shell mt-2 rounded-2xl border border-slate-200 bg-white shadow-sm focus-within:border-emerald-500 focus-within:ring-1 focus-within:ring-emerald-500">
                <textarea id="content-editor" name="content" rows="18" class="block w-full border-0 text-sm leading-7 focus:ring-0" required>{{ old('content', $article->content ?? '') }}</textarea>
            </div>
            <p class="{{ $helperClass }}">Untuk kutipan Arab, gunakan paragraf terpisah agar lebih mudah dibaca. Ukuran gambar pendukung maksimal 1 MB. Video YouTube tetap dimasukkan melalui field YouTube URL, bukan iframe di editor.</p>
            @error('content') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
        </x-ui.card>
    </div>

    <div class="space-y-4 sm:space-y-6">
        <x-ui.card padding="md">
            <div class="mb-5 border-b border-slate-100 pb-4">
                <h3 class="text-base font-bold text-slate-950">Media</h3>
                <p class="mt-1 text-sm text-slate-500">Banner opsional. Jika kosong, publik memakai visual default.</p>
            </div>
            @if (($article ?? null)?->banner_url)
                <img src="{{ $article->banner_url }}" alt="{{ $article->title }}" class="mb-4 aspect-video w-full rounded-2xl object-cover">
            @endif
            <label for="banner_image" class="{{ $labelClass }}">Banner Image</label>
            <input id="banner_image" name="banner_image" type="file" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white text-sm text-slate-700 shadow-sm file:mr-4 file:border-0 file:bg-emerald-50 file:px-4 file:py-2.5 file:text-sm file:font-semibold file:text-emerald-700 hover:file:bg-emerald-100 focus:border-emerald-500 focus:ring-emerald-500">
            <p class="{{ $helperClass }}">Format JPG, PNG, WEBP. Maksimal 2MB.</p>
            @error('banner_image') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror

            <div class="mt-5">
                <label for="youtube_url" class="{{ $labelClass }}">YouTube URL</label>
                <input id="youtube_url" name="youtube_url" type="url" value="{{ old('youtube_url', $article->youtube_url ?? '') }}" class="{{ $inputClass }}" placeholder="https://www.youtube.com/watch?v=...">
                <p class="{{ $helperClass }}">Simpan URL YouTube biasa, sistem akan membuat embed aman.</p>
                @error('youtube_url') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
        </x-ui.card>

        <x-ui.card padding="md">
            <div class="mb-5 border-b border-slate-100 pb-4">
                <h3 class="text-base font-bold text-slate-950">Publikasi</h3>
                <p class="mt-1 text-sm text-slate-500">Atur status artikel sebelum tampil di publik.</p>
            </div>
            <div class="space-y-5">
                <div>
                    <label for="status" class="{{ $labelClass }}">Status</label>
                    <select id="status" name="status" class="{{ $inputClass }}" required>
                        @foreach ($statuses as $value => $label)
                            <option value="{{ $value }}" @selected(old('status', $article->status ?? 'draft') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('status') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="published_at" class="{{ $labelClass }}">Published At</label>
                    <input id="published_at" name="published_at" type="datetime-local" value="{{ old('published_at', isset($article) && $article->published_at ? $article->published_at->format('Y-m-d\TH:i') : '') }}" class="{{ $inputClass }}">
                    <p class="{{ $helperClass }}">Jika status Published dan kosong, sistem memakai waktu saat disimpan.</p>
                    @error('published_at') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <label class="inline-flex items-center gap-2">
                    <input name="is_featured" type="hidden" value="0">
                    <input name="is_featured" type="checkbox" value="1" @checked(old('is_featured', $article->is_featured ?? false)) class="rounded border-slate-300 text-emerald-600 shadow-sm focus:ring-emerald-600">
                    <span class="text-sm font-semibold text-slate-700">Jadikan artikel unggulan</span>
                </label>
            </div>
        </x-ui.card>

        <div class="flex flex-col-reverse gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <a href="{{ route('articles.index') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Batal/Kembali</a>
            <button type="button" id="article-preview-button" class="inline-flex items-center justify-center rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm font-semibold text-emerald-700 transition hover:bg-emerald-100 focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:ring-offset-2">
                Preview Artikel
            </button>
            <x-ui.submit-button loading-text="Menyimpan...">Simpan Artikel</x-ui.submit-button>
        </div>
    </div>
</div>

<div id="article-preview-modal" class="fixed inset-0 z-[120] hidden" aria-labelledby="article-preview-title" role="dialog" aria-modal="true">
    <div class="absolute inset-0 bg-slate-950/70 backdrop-blur-sm" data-preview-close></div>
    <div class="relative flex min-h-screen items-center justify-center p-0 sm:p-4">
        <div class="relative flex h-screen w-full flex-col overflow-hidden bg-white shadow-2xl sm:h-[92vh] sm:max-w-5xl sm:rounded-3xl">
            <header class="sticky top-0 z-10 flex items-start justify-between gap-4 border-b border-slate-200 bg-white/95 px-4 py-4 backdrop-blur sm:px-6">
                <div>
                    <h2 id="article-preview-title" class="text-lg font-black text-slate-950">Preview Artikel</h2>
                    <p class="mt-1 text-sm text-slate-500">Tampilan ini hanya pratinjau dan belum menyimpan perubahan.</p>
                </div>
                <button type="button" data-preview-close class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-full border border-slate-200 text-slate-500 transition hover:bg-slate-50 hover:text-slate-800" aria-label="Tutup preview">
                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 0 1 1.414 0L10 8.586l4.293-4.293a1 1 0 1 1 1.414 1.414L11.414 10l4.293 4.293a1 1 0 0 1-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 0 1-1.414-1.414L8.586 10 4.293 5.707a1 1 0 0 1 0-1.414Z" clip-rule="evenodd" />
                    </svg>
                </button>
            </header>

            <div class="flex-1 overflow-y-auto bg-white px-4 py-6 sm:px-6">
                <article>
                    <header class="mx-auto max-w-3xl">
                        <div class="flex flex-wrap items-center gap-2 text-xs font-bold uppercase tracking-[0.16em] text-slate-500">
                            <span id="article-preview-category" class="rounded-full bg-emerald-50 px-3 py-1 text-emerald-700 ring-1 ring-inset ring-emerald-200">Kajian</span>
                            <span>Preview</span>
                        </div>
                        <h1 id="article-preview-title-text" class="mt-5 text-4xl font-black leading-tight tracking-tight text-slate-950 sm:text-5xl">Judul Kajian Akan Tampil di Sini</h1>
                        <p id="article-preview-excerpt" class="mt-5 text-lg leading-8 text-slate-600">Deskripsi singkat kajian akan tampil di sini.</p>
                        <div class="mt-6 flex flex-wrap items-center gap-x-2 gap-y-1 border-y border-slate-200 py-4 text-sm font-medium text-slate-500">
                            <span id="article-preview-author" class="font-bold text-slate-800">{{ $previewAuthor }}</span>
                            <span>&middot;</span>
                            <span id="article-preview-date">Preview</span>
                            <span>&middot;</span>
                            <span id="article-preview-reading">1 menit baca</span>
                            <span>&middot;</span>
                            <span>Preview views</span>
                        </div>
                    </header>

                    <div id="article-preview-banner-section" class="mx-auto mt-7 hidden max-w-5xl">
                        <img id="article-preview-banner-image" src="" alt="" class="aspect-[16/8] max-h-[34rem] w-full rounded-3xl object-cover shadow-xl shadow-emerald-950/10">
                    </div>

                    <div class="mx-auto max-w-3xl py-10">
                        <div id="article-preview-content" class="article-content">
                            <p>Isi kajian belum diisi.</p>
                        </div>

                        <section id="article-preview-youtube-section" class="mt-10 hidden">
                            <h3 class="text-xl font-black text-slate-950">Video Kajian</h3>
                            <div id="article-preview-youtube-frame-wrap" class="mt-4 hidden aspect-video overflow-hidden rounded-2xl border border-slate-200 bg-slate-950 shadow-sm">
                                <iframe id="article-preview-youtube-frame" src="" title="Preview video kajian" class="h-full w-full" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
                            </div>
                            <p id="article-preview-youtube-error" class="mt-3 hidden rounded-2xl bg-amber-50 px-4 py-3 text-sm font-semibold text-amber-700 ring-1 ring-inset ring-amber-200">URL YouTube belum valid.</p>
                        </section>

                        <footer class="mt-12 border-t border-slate-200 pt-6">
                            <p class="text-xs font-bold uppercase tracking-[0.18em] text-slate-400">Share preview</p>
                            <div class="mt-3 flex flex-wrap gap-2">
                                <span class="rounded-full bg-emerald-50 px-4 py-2 text-sm font-bold text-emerald-700 opacity-70">WhatsApp</span>
                                <span class="rounded-full bg-sky-50 px-4 py-2 text-sm font-bold text-sky-700 opacity-70">Telegram</span>
                                <span class="rounded-full border border-slate-200 px-4 py-2 text-sm font-bold text-slate-500 opacity-70">Copy Link</span>
                            </div>
                        </footer>
                    </div>
                </article>
            </div>

            <footer class="border-t border-slate-200 bg-white px-4 py-3 sm:px-6">
                <button type="button" data-preview-close class="inline-flex w-full items-center justify-center rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-bold text-white transition hover:bg-slate-800 sm:w-auto">Tutup Preview</button>
            </footer>
        </div>
    </div>
</div>

@once
    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/tinymce@7.6.1/tinymce.min.js" referrerpolicy="origin"></script>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const textarea = document.querySelector('#content-editor');

                if (! textarea || typeof window.tinymce === 'undefined' || textarea.dataset.editorReady === 'true') {
                    return;
                }

                window.tinymce
                    .init({
                        selector: '#content-editor',
                        license_key: 'gpl',
                        base_url: 'https://cdn.jsdelivr.net/npm/tinymce@7.6.1',
                        suffix: '.min',
                        height: 480,
                        menubar: false,
                        branding: false,
                        promotion: false,
                        resize: true,
                        statusbar: true,
                        plugins: 'lists link table image directionality charmap wordcount',
                        toolbar: 'undo redo | blocks | fontfamily fontsize | bold italic underline | bullist numlist | blockquote | alignleft aligncenter alignright | ltr rtl | link table image hr | removeformat | charmap',
                        block_formats: 'Paragraph=p; Heading 2=h2; Heading 3=h3; Heading 4=h4',
                        font_family_formats: 'Default Sans=system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;Editorial Serif=Georgia,serif;Arabic Friendly=serif',
                        font_size_formats: '14px 16px 18px 20px 24px',
                        table: {
                            resize_bars: true
                        },
                        forced_root_block: 'p',
                        automatic_uploads: true,
                        images_reuse_filename: false,
                        paste_data_images: false,
                        images_file_types: 'jpg,jpeg,png,webp',
                        image_advtab: false,
                        image_title: true,
                        images_upload_handler(blobInfo, progress) {
                            const formData = new FormData();
                            formData.append('file', blobInfo.blob(), blobInfo.filename());

                            return fetch(@js(route('articles.editor-image-upload')), {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                                    'Accept': 'application/json',
                                },
                                body: formData,
                                credentials: 'same-origin',
                            })
                                .then(async (response) => {
                                    const data = await response.json().catch(() => ({}));

                                    if (! response.ok || ! data.location) {
                                        throw new Error(data.message || 'Upload gambar gagal. Pastikan file gambar maksimal 1 MB.');
                                    }

                                    progress(100);

                                    return data.location;
                                });
                        },
                        invalid_elements: 'script,iframe,object,embed,style,form,input,button,svg,math',
                        valid_elements: 'p[dir|class|style],br,strong/b,em/i,u,span[dir|class|style],ul,ol,li[dir|class|style],blockquote[dir|class|style],h2[dir|style],h3[dir|style],h4[dir|style],a[href|target|rel],table,thead,tbody,tr,th[colspan|rowspan|dir|class|style],td[colspan|rowspan|dir|class|style],hr,img[src|alt|width|height]',
                        extended_valid_elements: 'p[dir|class|style],span[dir|class|style],blockquote[dir|class|style],li[dir|class|style],td[colspan|rowspan|dir|class|style],th[colspan|rowspan|dir|class|style],img[src|alt|width|height]',
                        valid_styles: {
                            '*': 'font-size,font-family,text-align'
                        },
                        content_style: `
                            body {
                                color: #334155;
                                font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
                                font-size: 15px;
                                line-height: 1.85;
                                padding: 0.5rem;
                            }
                            h2, h3, h4 {
                                color: #0f172a;
                                font-weight: 700;
                                line-height: 1.3;
                                margin: 1.4rem 0 0.6rem;
                            }
                            blockquote {
                                background: #ecfdf5;
                                border-left: 4px solid #10b981;
                                border-radius: 0.75rem;
                                color: #334155;
                                font-style: italic;
                                margin: 1rem 0;
                                padding: 0.9rem 1rem;
                            }
                            a {
                                color: #047857;
                                text-decoration: underline;
                            }
                            img {
                                border-radius: 1rem;
                                display: block;
                                height: auto;
                                margin: 1.5rem auto;
                                max-width: 100%;
                            }
                            table {
                                border-collapse: collapse;
                                width: 100%;
                            }
                            th, td {
                                border: 1px solid #e2e8f0;
                                padding: 0.65rem;
                            }
                            th {
                                background: #f8fafc;
                            }
                            [dir="rtl"], .arabic {
                                direction: rtl;
                                font-size: 1.15rem;
                                line-height: 2.1;
                                text-align: right;
                            }
                        `,
                        setup(editor) {
                            editor.on('init', () => {
                                textarea.dataset.editorReady = 'true';
                                window.articleContentEditor = editor;
                            });

                            editor.on('change keyup undo redo', () => {
                                editor.save();
                            });
                        }
                    })
                    .catch((error) => {
                        console.error('TinyMCE initialization error:', error);
                        textarea.classList.add('min-h-[320px]');
                    });

                textarea.closest('form')?.addEventListener('submit', () => {
                    window.tinymce?.triggerSave();
                });
            });
        </script>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const modal = document.querySelector('#article-preview-modal');
                const openButton = document.querySelector('#article-preview-button');
                const closeButtons = document.querySelectorAll('[data-preview-close]');
                const existingBannerUrl = @js($existingBannerUrl);
                const fallbackAuthor = @js($previewAuthor);
                let localBannerUrl = null;

                if (! modal || ! openButton) {
                    return;
                }

                const fields = {
                    title: document.querySelector('#title'),
                    category: document.querySelector('#article_category_id'),
                    excerpt: document.querySelector('#excerpt'),
                    content: document.querySelector('#content-editor'),
                    banner: document.querySelector('#banner_image'),
                    youtube: document.querySelector('#youtube_url'),
                    author: document.querySelector('#managed_by_bidang_id'),
                };

                const targets = {
                    title: document.querySelector('#article-preview-title-text'),
                    category: document.querySelector('#article-preview-category'),
                    excerpt: document.querySelector('#article-preview-excerpt'),
                    content: document.querySelector('#article-preview-content'),
                    author: document.querySelector('#article-preview-author'),
                    date: document.querySelector('#article-preview-date'),
                    reading: document.querySelector('#article-preview-reading'),
                    bannerSection: document.querySelector('#article-preview-banner-section'),
                    bannerImage: document.querySelector('#article-preview-banner-image'),
                    youtubeSection: document.querySelector('#article-preview-youtube-section'),
                    youtubeWrap: document.querySelector('#article-preview-youtube-frame-wrap'),
                    youtubeFrame: document.querySelector('#article-preview-youtube-frame'),
                    youtubeError: document.querySelector('#article-preview-youtube-error'),
                };

                function selectedText(select, fallback) {
                    if (! select || select.selectedIndex < 0) {
                        return fallback;
                    }

                    const text = select.options[select.selectedIndex]?.textContent?.trim();

                    return text && ! text.toLowerCase().includes('tidak ditentukan') && ! text.toLowerCase().includes('tanpa kategori')
                        ? text
                        : fallback;
                }

                function estimateReadingMinutes(html) {
                    const text = html.replace(/<[^>]+>/g, ' ').replace(/\s+/g, ' ').trim();
                    const words = text.match(/[\p{L}\p{N}']+/gu) || [];

                    return Math.max(1, Math.ceil(words.length / 200));
                }

                function sanitizePreviewHtml(html) {
                    const allowedTags = ['P', 'BR', 'STRONG', 'B', 'EM', 'I', 'U', 'UL', 'OL', 'LI', 'BLOCKQUOTE', 'H2', 'H3', 'H4', 'A', 'TABLE', 'THEAD', 'TBODY', 'TR', 'TH', 'TD', 'HR', 'IMG', 'SPAN'];
                    const allowedFontSizes = ['14px', '16px', '18px', '20px', '24px'];
                    const allowedTextAlign = ['left', 'center', 'right'];
                    const template = document.createElement('template');
                    template.innerHTML = html || '<p>Isi kajian belum diisi.</p>';

                    template.content.querySelectorAll('script, iframe, object, embed, style, form, input, button, svg, math').forEach((node) => node.remove());

                    function isSafeImageSrc(src) {
                        if (! src || /^\s*(javascript|data|vbscript):/i.test(src)) {
                            return false;
                        }

                        try {
                            const url = new URL(src, window.location.origin);

                            return url.pathname.startsWith('/storage/article-content/');
                        } catch (error) {
                            return false;
                        }
                    }

                    function sanitizeStyle(value) {
                        return value.split(';').map((declaration) => {
                            const [rawProperty, ...rawValue] = declaration.split(':');
                            const property = (rawProperty || '').trim().toLowerCase();
                            const styleValue = rawValue.join(':').trim();
                            const normalized = styleValue.replace(/\s+/g, ' ').toLowerCase();

                            if (property === 'font-size' && allowedFontSizes.includes(normalized)) {
                                return `font-size: ${normalized}`;
                            }

                            if (property === 'text-align' && allowedTextAlign.includes(normalized)) {
                                return `text-align: ${normalized}`;
                            }

                            if (property === 'font-family') {
                                const font = normalized.replace(/["']/g, '');
                                if (font.includes('system-ui')) {
                                    return 'font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif';
                                }
                                if (font.includes('georgia')) {
                                    return 'font-family: Georgia, serif';
                                }
                                if (font === 'serif' || font.includes(', serif')) {
                                    return 'font-family: serif';
                                }
                            }

                            return null;
                        }).filter(Boolean).join('; ');
                    }

                    template.content.querySelectorAll('*').forEach((node) => {
                        if (! allowedTags.includes(node.tagName)) {
                            node.replaceWith(...Array.from(node.childNodes));
                            return;
                        }

                        [...node.attributes].forEach((attribute) => {
                            const name = attribute.name.toLowerCase();
                            const value = attribute.value.trim();

                            if (name.startsWith('on')) {
                                node.removeAttribute(attribute.name);
                                return;
                            }

                            if (node.tagName === 'A' && name === 'href') {
                                if (/^\s*(javascript|data|vbscript):/i.test(value)) {
                                    node.removeAttribute(attribute.name);
                                } else {
                                    node.setAttribute('rel', 'noopener noreferrer');
                                }
                                return;
                            }

                            if (node.tagName === 'A' && ['target', 'rel'].includes(name)) {
                                return;
                            }

                            if (node.tagName === 'IMG' && name === 'src') {
                                if (! isSafeImageSrc(value)) {
                                    node.removeAttribute(attribute.name);
                                }
                                return;
                            }

                            if (node.tagName === 'IMG' && ['alt', 'width', 'height'].includes(name)) {
                                if (['width', 'height'].includes(name) && (! /^\d+$/.test(value) || Number(value) > 1600)) {
                                    node.removeAttribute(attribute.name);
                                }
                                return;
                            }

                            if (name === 'dir' && ['rtl', 'ltr', 'auto'].includes(value)) {
                                return;
                            }

                            if (['TD', 'TH'].includes(node.tagName) && ['colspan', 'rowspan'].includes(name) && /^\d+$/.test(value)) {
                                return;
                            }

                            if (name === 'class' && value.split(/\s+/).includes('arabic')) {
                                node.setAttribute('class', 'arabic');
                                return;
                            }

                            if (name === 'style') {
                                const style = sanitizeStyle(value);
                                if (style) {
                                    node.setAttribute('style', style);
                                } else {
                                    node.removeAttribute(attribute.name);
                                }
                                return;
                            }

                            node.removeAttribute(attribute.name);
                        });

                        if (node.tagName === 'IMG' && ! node.getAttribute('src')) {
                            node.remove();
                        }
                    });

                    return template.innerHTML.trim() || '<p>Isi kajian belum diisi.</p>';
                }

                function youtubeEmbedUrl(value) {
                    if (! value) {
                        return null;
                    }

                    try {
                        const url = new URL(value);
                        let id = null;

                        if (url.hostname.includes('youtu.be')) {
                            id = url.pathname.split('/').filter(Boolean)[0];
                        } else if (url.hostname.includes('youtube.com')) {
                            id = url.searchParams.get('v') || url.pathname.split('/').filter(Boolean).pop();
                        }

                        return id && /^[a-zA-Z0-9_-]{6,}$/.test(id)
                            ? `https://www.youtube.com/embed/${id}`
                            : null;
                    } catch (error) {
                        return null;
                    }
                }

                function updateBanner(title) {
                    const file = fields.banner?.files?.[0] || null;

                    if (localBannerUrl) {
                        URL.revokeObjectURL(localBannerUrl);
                        localBannerUrl = null;
                    }

                    if (file) {
                        localBannerUrl = URL.createObjectURL(file);
                    }

                    const imageUrl = localBannerUrl || existingBannerUrl || null;

                    if (imageUrl) {
                        targets.bannerImage.src = imageUrl;
                        targets.bannerImage.alt = title;
                        targets.bannerSection.classList.remove('hidden');
                    } else {
                        targets.bannerImage.removeAttribute('src');
                        targets.bannerSection.classList.add('hidden');
                    }
                }

                function openPreview() {
                    window.tinymce?.triggerSave();

                    const editorContent = window.tinymce?.get('content-editor')?.getContent() || fields.content?.value || '';
                    const title = fields.title?.value.trim() || 'Judul Kajian Akan Tampil di Sini';
                    const excerpt = fields.excerpt?.value.trim() || 'Deskripsi singkat kajian akan tampil di sini.';
                    const category = selectedText(fields.category, 'Kajian');
                    const author = selectedText(fields.author, fallbackAuthor);
                    const sanitizedContent = sanitizePreviewHtml(editorContent);
                    const embedUrl = youtubeEmbedUrl(fields.youtube?.value.trim() || '');

                    targets.title.textContent = title;
                    targets.excerpt.textContent = excerpt;
                    targets.category.textContent = category;
                    targets.author.textContent = author;
                    targets.date.textContent = new Date().toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric' });
                    targets.reading.textContent = `${estimateReadingMinutes(sanitizedContent)} menit baca`;
                    targets.content.innerHTML = sanitizedContent;

                    updateBanner(title);

                    if ((fields.youtube?.value || '').trim()) {
                        targets.youtubeSection.classList.remove('hidden');
                        if (embedUrl) {
                            targets.youtubeFrame.src = embedUrl;
                            targets.youtubeWrap.classList.remove('hidden');
                            targets.youtubeError.classList.add('hidden');
                        } else {
                            targets.youtubeFrame.removeAttribute('src');
                            targets.youtubeWrap.classList.add('hidden');
                            targets.youtubeError.classList.remove('hidden');
                        }
                    } else {
                        targets.youtubeSection.classList.add('hidden');
                        targets.youtubeFrame.removeAttribute('src');
                    }

                    modal.classList.remove('hidden');
                    document.documentElement.classList.add('overflow-hidden');
                }

                function closePreview() {
                    modal.classList.add('hidden');
                    document.documentElement.classList.remove('overflow-hidden');
                    targets.youtubeFrame.removeAttribute('src');
                }

                openButton.addEventListener('click', openPreview);
                closeButtons.forEach((button) => button.addEventListener('click', closePreview));
                document.addEventListener('keydown', (event) => {
                    if (event.key === 'Escape' && ! modal.classList.contains('hidden')) {
                        closePreview();
                    }
                });
            });
        </script>
    @endpush
@endonce
