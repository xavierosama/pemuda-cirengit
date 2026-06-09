<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $departments = [
            'Pendidikan',
            'Dakwah',
            'Kaderisasi',
            'Sosial',
            'Ekonomi',
            'Publikasi',
            'Olahraga',
            'Urusan Rumah Tangga',
        ];

        foreach ($departments as $department) {
            Department::updateOrCreate(
                ['name' => $department],
                ['status' => 'active']
            );
        }
    }
}
