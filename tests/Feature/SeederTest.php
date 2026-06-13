<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Member;
use App\Models\Position;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\DemoMemberSeeder;
use Database\Seeders\DepartmentSeeder;
use Database\Seeders\PositionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_seeder_creates_default_admin_departments_and_positions_without_duplicates(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->seed(DatabaseSeeder::class);

        $admin = User::where('email', 'admin@pemudacirengit.test')->firstOrFail();

        $this->assertSame('Admin', $admin->name);
        $this->assertSame('admin', $admin->role);
        $this->assertNull($admin->member_id);
        $this->assertTrue(Hash::check('password', $admin->password));
        $this->assertSame(1, User::where('email', 'admin@pemudacirengit.test')->count());

        foreach (['Pendidikan', 'Dakwah', 'Kaderisasi', 'Sosial', 'Ekonomi', 'Publikasi', 'Olahraga', 'Urusan Rumah Tangga'] as $department) {
            $this->assertDatabaseHas('departments', [
                'name' => $department,
                'status' => 'active',
            ]);
            $this->assertSame(1, Department::where('name', $department)->count());
        }

        foreach (['Ketua', 'Wakil Ketua', 'Sekretaris', 'Bendahara', 'Ketua Bidang', 'Anggota Bidang', 'Anggota'] as $position) {
            $this->assertDatabaseHas('positions', [
                'name' => $position,
                'status' => 'active',
            ]);
            $this->assertSame(1, Position::where('name', $position)->count());
        }

        $this->assertSame(0, Member::count());
    }

    public function test_demo_member_seeder_can_be_run_manually_without_duplicates(): void
    {
        $this->seed([
            DepartmentSeeder::class,
            PositionSeeder::class,
            DemoMemberSeeder::class,
            DemoMemberSeeder::class,
        ]);

        foreach (['20.0001', '20.0002', '20.0003'] as $npa) {
            $this->assertSame(1, Member::where('npa', $npa)->count());
        }

        $this->assertDatabaseHas('members', [
            'npa' => '20.0001',
            'full_name' => 'Ahmad Fulan',
            'phone' => '081234567890',
            'email' => 'ahmad.fulan@example.com',
            'member_status' => 'active',
        ]);
    }
}
