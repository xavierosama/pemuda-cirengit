<?php

namespace Database\Seeders;

use App\Models\ArticleCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ArticleCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'Kajian',
            'Umum',
            'Pendidikan',
            'Organisasi',
            'Kepemudaan',
            'Pengumuman',
            'Aqidah',
            'Fiqih',
            'Akhlak',
            'Sirah',
            'Pemikiran Islam',
            'Kajian Umum',
        ];

        foreach ($categories as $index => $name) {
            ArticleCategory::updateOrCreate(
                ['slug' => Str::slug($name)],
                [
                    'name' => $name,
                    'is_active' => true,
                    'sort_order' => $index + 1,
                ]
            );
        }
    }
}
