<?php

namespace Database\Seeders;

use App\Models\FinancialCategory;
use Illuminate\Database\Seeder;

class FinancialCategorySeeder extends Seeder
{
    public function run(): void
    {
        $sort = 1;

        foreach ([
            ['Iuran Anggota', 'income', 'Pemasukan rutin dari iuran anggota.'],
            ['Donasi', 'income', 'Donasi umum untuk kas organisasi.'],
            ['Infaq', 'income', 'Infaq dari anggota atau simpatisan.'],
            ['Kas Kegiatan', 'income', 'Pemasukan khusus kegiatan.'],
            ['Sponsor', 'income', 'Dukungan sponsor kegiatan.'],
            ['Lain-lain', 'income', 'Pemasukan lain di luar kategori utama.'],
            ['Konsumsi', 'expense', 'Pengeluaran konsumsi kegiatan.'],
            ['Operasional', 'expense', 'Pengeluaran operasional organisasi.'],
            ['Kegiatan', 'expense', 'Pengeluaran pelaksanaan kegiatan.'],
            ['Transport', 'expense', 'Pengeluaran transportasi.'],
            ['Sosial', 'expense', 'Pengeluaran sosial dan santunan.'],
            ['Perlengkapan', 'expense', 'Pengeluaran perlengkapan organisasi.'],
            ['Dokumentasi', 'expense', 'Pengeluaran dokumentasi kegiatan.'],
            ['Lain-lain', 'expense', 'Pengeluaran lain di luar kategori utama.'],
        ] as [$name, $type, $description]) {
            FinancialCategory::query()->updateOrCreate(
                ['slug' => str($type.'-'.$name)->slug()->toString()],
                [
                    'name' => $name,
                    'type' => $type,
                    'description' => $description,
                    'is_active' => true,
                    'sort_order' => $sort++,
                ]
            );
        }
    }
}
