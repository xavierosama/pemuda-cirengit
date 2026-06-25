<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Member;
use App\Models\Position;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/profile');

        $response->assertOk();
    }

    public function test_member_profile_uses_member_specific_edit_page(): void
    {
        $department = Department::create(['name' => 'Pendidikan', 'status' => 'active']);
        $position = Position::create(['name' => 'Anggota', 'status' => 'active']);
        $member = Member::create([
            'department_id' => $department->id,
            'position_id' => $position->id,
            'full_name' => 'Ahmad Anggota',
            'npa' => '20.0001',
            'phone' => '081234567890',
            'birth_date' => '1998-01-09',
            'member_status' => 'active',
        ]);
        $memberUser = User::factory()->create([
            'member_id' => $member->id,
            'role' => 'member',
        ]);
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($memberUser)
            ->get('/profile')
            ->assertRedirect(route('member.profile.edit'));

        $this->actingAs($memberUser)
            ->get(route('member.profile.edit'))
            ->assertOk()
            ->assertDontSee('Kembali ke Dashboard Anggota')
            ->assertSee('Edit Profil Anggota')
            ->assertSee('Profil Anggota')
            ->assertSee('Ahmad Anggota')
            ->assertSee('20.0001')
            ->assertSee('Pendidikan')
            ->assertSee('Anggota')
            ->assertSee('js-date-picker', false)
            ->assertSee('aria-label="Buka menu akun"', false)
            ->assertSee('title="Buka menu akun"', false)
            ->assertSee('Lihat Profile')
            ->assertSee('Edit Profile')
            ->assertSee('NPA: 20.0001')
            ->assertSee('action="'.route('logout').'"', false)
            ->assertSee('value="1998-01-09"', false);

        $this->actingAs($admin)
            ->get('/profile')
            ->assertOk()
            ->assertSee('Kembali ke Dashboard Admin');
    }

    public function test_profile_information_can_be_updated(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $user->refresh();

        $this->assertSame('Test User', $user->name);
        $this->assertSame('test@example.com', $user->email);
        $this->assertNull($user->email_verified_at);
    }

    public function test_member_can_update_personal_profile_and_replace_profile_photo(): void
    {
        Storage::fake('public');

        $department = Department::create(['name' => 'Pendidikan', 'status' => 'active']);
        $position = Position::create(['name' => 'Anggota', 'status' => 'active']);
        $member = Member::create([
            'department_id' => $department->id,
            'position_id' => $position->id,
            'full_name' => 'Ahmad Anggota',
            'npa' => '20.0001',
            'phone' => '0800',
            'address' => 'Alamat lama',
            'birth_date' => '1998-01-09',
            'member_status' => 'active',
        ]);
        $user = User::factory()->create([
            'member_id' => $member->id,
            'role' => 'member',
            'email' => 'ahmad@example.test',
        ]);

        $this->actingAs($user)
            ->patch(route('member.profile.update'), [
                'phone' => '081234567890',
                'address' => 'Alamat baru',
                'birth_date' => '10/01/1998',
                'npa' => 'NPA-TIDAK-BOLEH-BERUBAH',
                'member_status' => 'inactive',
                'profile_photo' => $this->fakePng('avatar.png'),
            ])
            ->assertRedirect(route('member.profile.edit'))
            ->assertSessionHas('status', 'member-profile-updated');

        $member->refresh();

        $this->assertSame('081234567890', $member->phone);
        $this->assertSame('Alamat baru', $member->address);
        $this->assertSame('1998-01-10', $member->birth_date->format('Y-m-d'));
        $this->assertSame('20.0001', $member->npa);
        $this->assertSame('active', $member->member_status);
        $this->assertNotNull($member->profile_photo);
        Storage::disk('public')->assertExists($member->profile_photo);

        $oldPhotoPath = $member->profile_photo;

        $this->actingAs($user)
            ->patch(route('member.profile.update'), [
                'phone' => '081234567891',
                'address' => 'Alamat kedua',
                'birth_date' => '1998-01-11',
                'profile_photo' => $this->fakePng('avatar-baru.png'),
            ])
            ->assertRedirect(route('member.profile.edit'))
            ->assertSessionHas('status', 'member-profile-updated');

        $member->refresh();

        $this->assertSame('081234567891', $member->phone);
        $this->assertSame('Alamat kedua', $member->address);
        $this->assertSame('1998-01-11', $member->birth_date->format('Y-m-d'));
        $this->assertNotSame($oldPhotoPath, $member->profile_photo);
        Storage::disk('public')->assertMissing($oldPhotoPath);
        Storage::disk('public')->assertExists($member->profile_photo);
    }

    public function test_member_profile_photo_upload_must_be_an_image(): void
    {
        Storage::fake('public');

        $member = Member::create([
            'full_name' => 'Ahmad Anggota',
            'member_status' => 'active',
        ]);
        $user = User::factory()->create([
            'member_id' => $member->id,
            'role' => 'member',
        ]);

        $this->actingAs($user)
            ->patch(route('member.profile.update'), [
                'phone' => '081234567890',
                'address' => 'Alamat baru',
                'profile_photo' => UploadedFile::fake()->create('avatar.pdf', 100, 'application/pdf'),
            ])
            ->assertSessionHasErrors('profile_photo');
    }

    public function test_internal_user_member_profile_update_redirects_to_admin_profile(): void
    {
        $member = Member::create([
            'full_name' => 'Admin Terhubung',
            'member_status' => 'active',
        ]);
        $admin = User::factory()->create([
            'member_id' => $member->id,
            'role' => 'admin',
        ]);

        $this->actingAs($admin)
            ->patch(route('profile.member.update'), [
                'phone' => '0812',
                'birth_date' => '12/01/1998',
            ])
            ->assertRedirect(route('profile.edit'))
            ->assertSessionHas('status', 'member-profile-updated');

        $member->refresh();

        $this->assertSame('0812', $member->phone);
        $this->assertSame('1998-01-12', $member->birth_date->format('Y-m-d'));
    }

    private function fakePng(string $name): UploadedFile
    {
        $onePixelPng = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/p9sAAAAASUVORK5CYII=');

        return UploadedFile::fake()->createWithContent($name, $onePixelPng);
    }

    public function test_email_verification_status_is_unchanged_when_the_email_address_is_unchanged(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test User',
                'email' => $user->email,
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $this->assertNotNull($user->refresh()->email_verified_at);
    }

    public function test_user_can_delete_their_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->delete('/profile', [
                'password' => 'password',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/');

        $this->assertGuest();
        $this->assertNull($user->fresh());
    }

    public function test_correct_password_must_be_provided_to_delete_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from('/profile')
            ->delete('/profile', [
                'password' => 'wrong-password',
            ]);

        $response
            ->assertSessionHasErrorsIn('userDeletion', 'password')
            ->assertRedirect('/profile');

        $this->assertNotNull($user->fresh());
    }
}
