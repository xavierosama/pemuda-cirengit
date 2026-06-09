<?php

namespace Database\Seeders;

use App\Models\Position;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PositionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $positions = [
            'Ketua',
            'Wakil Ketua',
            'Sekretaris',
            'Bendahara',
            'Ketua Bidang',
            'Anggota Bidang',
            'Anggota',
        ];

        foreach ($positions as $position) {
            Position::updateOrCreate(
                ['name' => $position],
                ['status' => 'active']
            );
        }
    }
}
