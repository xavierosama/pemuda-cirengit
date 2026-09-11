<?php

namespace Tests\Feature;

use App\Models\FinancialCategory;
use App\Models\FinancialTransaction;
use App\Models\Member;
use App\Models\MemberFeeRecord;
use App\Models\MembershipFeeSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class FinanceModuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_and_bendahara_can_access_finance_dashboard(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $bendahara = User::factory()->create(['role' => 'bendahara']);
        $member = User::factory()->create(['role' => 'member']);

        $this->actingAs($admin)->get(route('finance.dashboard'))->assertOk()->assertSee('Dashboard Bendahara');
        $this->actingAs($bendahara)->get(route('finance.dashboard'))->assertOk()->assertSee('Dashboard Bendahara');
        $this->actingAs($member)->get(route('finance.dashboard'))->assertForbidden();
    }

    public function test_finance_category_and_transaction_can_be_managed(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->post(route('finance.categories.store'), [
                'name' => 'Donasi Test',
                'type' => 'income',
                'is_active' => true,
            ])
            ->assertRedirect(route('finance.categories.index'));

        $category = FinancialCategory::firstWhere('name', 'Donasi Test');
        $this->assertNotNull($category);

        $this->actingAs($admin)
            ->post(route('finance.transactions.store'), [
                'type' => 'income',
                'financial_category_id' => $category->id,
                'amount' => 50000,
                'transaction_date' => '2026-07-17',
                'title' => 'Donasi kas',
                'payment_method' => 'cash',
            ])
            ->assertRedirect(route('finance.transactions.index', ['type' => 'income']));

        $this->assertDatabaseHas('financial_transactions', [
            'title' => 'Donasi kas',
            'type' => 'income',
            'amount' => 50000,
            'created_by' => $admin->id,
        ]);
    }

    public function test_finance_dashboard_falls_back_when_fee_records_table_is_missing(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Schema::drop('member_fee_records');

        $this->actingAs($admin)
            ->get(route('finance.dashboard'))
            ->assertOk()
            ->assertSee('Migration finance belum lengkap')
            ->assertSee('Rp 0');
    }

    public function test_member_fee_index_falls_back_when_fee_records_table_is_missing(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Schema::drop('member_fee_records');

        $this->actingAs($admin)
            ->get(route('finance.member-fees.index'))
            ->assertOk()
            ->assertSee('Tabel iuran belum tersedia')
            ->assertSee('Belum ada data iuran untuk periode ini');
    }

    public function test_fee_migrations_are_idempotent_when_tables_already_exist(): void
    {
        foreach ([
            '2026_07_17_000004_create_membership_fee_settings_table.php',
            '2026_07_17_000005_create_member_fee_settings_table.php',
            '2026_07_17_000006_create_member_fee_records_table.php',
        ] as $migrationFile) {
            $migration = require database_path('migrations/'.$migrationFile);
            $migration->up();
        }

        $this->assertTrue(Schema::hasTable('membership_fee_settings'));
        $this->assertTrue(Schema::hasTable('member_fee_settings'));
        $this->assertTrue(Schema::hasTable('member_fee_records'));
    }

    public function test_member_fee_generation_is_not_duplicated(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        MembershipFeeSetting::create([
            'name' => 'Iuran Bulanan',
            'default_amount' => 10000,
            'effective_from' => '2026-01-01',
            'is_active' => true,
        ]);
        Member::create(['full_name' => 'Anggota Satu', 'member_status' => 'active']);
        Member::create(['full_name' => 'Anggota Dua', 'member_status' => 'active']);
        Member::create(['full_name' => 'Alumni', 'member_status' => 'alumni']);

        $payload = ['year' => 2026, 'month' => 7];

        $this->actingAs($admin)->post(route('finance.member-fees.generate'), $payload)->assertRedirect();
        $this->actingAs($admin)->post(route('finance.member-fees.generate'), $payload)->assertRedirect();

        $this->assertSame(2, MemberFeeRecord::where('year', 2026)->where('month', 7)->count());
    }

    public function test_member_can_only_see_own_tracking_only_fee_page(): void
    {
        $member = Member::create(['full_name' => 'Anggota Iuran', 'member_status' => 'active']);
        $otherMember = Member::create(['full_name' => 'Anggota Lain', 'member_status' => 'active']);
        $user = User::factory()->create(['member_id' => $member->id, 'role' => 'member']);

        MemberFeeRecord::create([
            'member_id' => $member->id,
            'year' => 2026,
            'month' => 7,
            'amount' => 10000,
            'status' => 'paid',
            'paid_at' => '2026-07-17',
        ]);
        MemberFeeRecord::create([
            'member_id' => $otherMember->id,
            'year' => 2026,
            'month' => 7,
            'amount' => 10000,
            'status' => 'unpaid',
        ]);

        $this->actingAs($user)
            ->get(route('member.fees.index', ['year' => 2026]))
            ->assertOk()
            ->assertSee('Iuran Saya')
            ->assertSee('Lunas')
            ->assertDontSee('Anggota Lain')
            ->assertDontSee('Upload Bukti')
            ->assertDontSee('Bayar Sekarang');
    }
}
