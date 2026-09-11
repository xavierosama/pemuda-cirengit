<?php

namespace Database\Seeders;

use App\Models\MembershipFeeSetting;
use Illuminate\Database\Seeder;

class MembershipFeeSettingSeeder extends Seeder
{
    public function run(): void
    {
        if (MembershipFeeSetting::query()->exists()) {
            return;
        }

        MembershipFeeSetting::query()->create([
            'name' => 'Iuran Bulanan',
            'default_amount' => 10000,
            'effective_from' => now()->startOfMonth()->toDateString(),
            'is_active' => true,
        ]);
    }
}
