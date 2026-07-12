<?php

namespace Tests\Feature;

use App\Models\Position;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PositionCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_manage_positions(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('positions.store'), [
                'name' => 'Koordinator Lapangan',
                'description' => 'Mengkoordinasikan pelaksanaan kegiatan lapangan.',
                'status' => 'active',
            ])
            ->assertRedirect(route('positions.index'));

        $position = Position::where('name', 'Koordinator Lapangan')->firstOrFail();

        $this->actingAs($user)
            ->get(route('positions.index', ['search' => 'Koordinator', 'status' => 'active']))
            ->assertOk()
            ->assertSee('Kelola jabatan dan posisi anggota dalam organisasi.')
            ->assertSee('Total Jabatan')
            ->assertSee('Jabatan Aktif')
            ->assertSee('Tabel Data Jabatan')
            ->assertSee('Cari nama jabatan')
            ->assertSee('Filter')
            ->assertSee('Jumlah Anggota')
            ->assertSee('Koordinator Lapangan');

        $this->actingAs($user)
            ->get(route('positions.create'))
            ->assertOk()
            ->assertSee('Tambah Jabatan')
            ->assertSee('Informasi Jabatan')
            ->assertSee('Batal/Kembali');

        $this->actingAs($user)
            ->get(route('positions.edit', $position))
            ->assertOk()
            ->assertSee('Edit Jabatan')
            ->assertSee('Terakhir diperbarui');

        $this->actingAs($user)
            ->get(route('positions.show', $position))
            ->assertOk()
            ->assertSee('Koordinator Lapangan');

        $this->actingAs($user)
            ->put(route('positions.update', $position), [
                'name' => 'Koordinator Lapangan Updated',
                'description' => 'Deskripsi diperbarui.',
                'status' => 'inactive',
            ])
            ->assertRedirect(route('positions.index'));

        $this->assertDatabaseHas('positions', [
            'id' => $position->id,
            'name' => 'Koordinator Lapangan Updated',
            'status' => 'inactive',
        ]);

        $this->actingAs($user)
            ->delete(route('positions.destroy', $position))
            ->assertRedirect(route('positions.index'));

        $this->assertDatabaseMissing('positions', [
            'id' => $position->id,
        ]);
    }

    public function test_position_name_must_be_unique(): void
    {
        $user = User::factory()->create();

        Position::create([
            'name' => 'Ketua',
            'status' => 'active',
        ]);

        $this->actingAs($user)
            ->post(route('positions.store'), [
                'name' => 'Ketua',
                'status' => 'active',
            ])
            ->assertSessionHasErrors('name');
    }
}
