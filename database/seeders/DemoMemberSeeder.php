<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Member;
use App\Models\Position;
use Illuminate\Database\Seeder;

class DemoMemberSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $members = [
            [
                'npa' => '20.0001',
                'full_name' => 'Ahmad Fulan',
                'phone' => '081234567890',
                'email' => 'ahmad.fulan@example.com',
                'department' => 'Pendidikan',
                'position' => 'Anggota',
            ],
            [
                'npa' => '20.0002',
                'full_name' => 'Budi Rahman',
                'phone' => '081234567891',
                'email' => 'budi.rahman@example.com',
                'department' => 'Dakwah',
                'position' => 'Anggota Bidang',
            ],
            [
                'npa' => '20.0003',
                'full_name' => 'Cecep Hidayat',
                'phone' => '081234567892',
                'email' => 'cecep.hidayat@example.com',
                'department' => 'Kaderisasi',
                'position' => 'Ketua Bidang',
            ],
        ];

        foreach ($members as $member) {
            $department = Department::where('name', $member['department'])->first();
            $position = Position::where('name', $member['position'])->first();

            Member::updateOrCreate(
                ['npa' => $member['npa']],
                [
                    'full_name' => $member['full_name'],
                    'phone' => $member['phone'],
                    'email' => $member['email'],
                    'department_id' => $department?->id,
                    'position_id' => $position?->id,
                    'member_status' => 'active',
                ]
            );
        }
    }
}
