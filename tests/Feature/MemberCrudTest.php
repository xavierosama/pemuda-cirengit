<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Member;
use App\Models\Position;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Tests\TestCase;
use ZipArchive;

class MemberCrudTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_authenticated_user_can_manage_members(): void
    {
        $user = User::factory()->create();
        $department = Department::create(['name' => 'Pendidikan', 'status' => 'active']);
        $position = Position::create(['name' => 'Anggota', 'status' => 'active']);

        $this->actingAs($user)->post(route('members.store'), [
            'full_name' => 'Ahmad Cirengit',
            'npa' => 'PC-001',
            'phone' => '08123456789',
            'email' => 'ahmad@example.test',
            'address' => 'Cirengit',
            'joined_at' => '2026-06-10',
            'department_id' => $department->id,
            'position_id' => $position->id,
            'member_status' => 'active',
            'notes' => 'Anggota baru.',
        ])->assertRedirect(route('members.index'));

        $member = Member::where('full_name', 'Ahmad Cirengit')->firstOrFail();

        $this->actingAs($user)
            ->get(route('members.index', [
                'search' => 'PC-001',
                'department_id' => $department->id,
                'position_id' => $position->id,
                'member_status' => 'active',
            ]))
            ->assertOk()
            ->assertSee('Export Excel')
            ->assertSee('Ahmad Cirengit')
            ->assertSee('PC-001')
            ->assertSee('Pendidikan')
            ->assertSee('Anggota');

        $this->actingAs($user)
            ->get(route('members.show', $member))
            ->assertOk()
            ->assertSee('Ahmad Cirengit')
            ->assertSee('ahmad@example.test');

        $this->actingAs($user)->put(route('members.update', $member), [
            'full_name' => 'Ahmad Updated',
            'email' => 'updated@example.test',
            'department_id' => $department->id,
            'position_id' => $position->id,
            'member_status' => 'alumni',
        ])->assertRedirect(route('members.index'));

        $this->assertDatabaseHas('members', [
            'id' => $member->id,
            'full_name' => 'Ahmad Updated',
            'member_status' => 'alumni',
            'npa' => 'PC-001',
        ]);

        $this->actingAs($user)
            ->delete(route('members.destroy', $member))
            ->assertRedirect(route('members.index'));

        $this->assertDatabaseMissing('members', ['id' => $member->id]);
    }

    public function test_member_validation_rejects_invalid_data(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('members.store'), [
            'full_name' => '',
            'email' => 'bukan-email',
            'joined_at' => 'bukan-tanggal',
            'department_id' => 999,
            'position_id' => 999,
            'member_status' => 'unknown',
        ])->assertSessionHasErrors([
            'full_name',
            'email',
            'joined_at',
            'department_id',
            'position_id',
            'member_status',
        ]);
    }

    public function test_npa_is_nullable_and_unique_when_filled(): void
    {
        $user = User::factory()->create();
        $member = Member::create([
            'full_name' => 'Anggota NPA',
            'npa' => 'NPA-001',
            'member_status' => 'active',
        ]);

        Member::create(['full_name' => 'NPA Kosong 1', 'npa' => null, 'member_status' => 'active']);
        Member::create(['full_name' => 'NPA Kosong 2', 'npa' => null, 'member_status' => 'active']);

        $this->actingAs($user)->post(route('members.store'), [
            'full_name' => 'Duplikat NPA',
            'npa' => 'NPA-001',
            'member_status' => 'active',
        ])->assertSessionHasErrors([
            'npa' => 'NPA sudah digunakan oleh anggota lain.',
        ]);

        $this->actingAs($user)->put(route('members.update', $member), [
            'full_name' => 'Anggota NPA Updated',
            'npa' => 'NPA-001',
            'member_status' => 'active',
        ])->assertRedirect(route('members.index'));
    }

    public function test_npa_unique_constraint_exists_in_database(): void
    {
        Member::create([
            'full_name' => 'Anggota NPA',
            'npa' => 'NPA-UNIK',
            'member_status' => 'active',
        ]);

        $this->expectException(QueryException::class);

        Member::create([
            'full_name' => 'Anggota Duplikat',
            'npa' => 'NPA-UNIK',
            'member_status' => 'active',
        ]);
    }

    public function test_internal_user_can_download_member_import_template(): void
    {
        $user = User::factory()->create(['role' => 'secretary']);

        $this->actingAs($user)
            ->get(route('members.import'))
            ->assertOk()
            ->assertSee('Download Template Excel')
            ->assertSee('File Excel')
            ->assertSee('Import Data Anggota')
            ->assertSee('Gunakan template yang tersedia agar format kolom sesuai.')
            ->assertSee('Format tanggal: dd/mm/yyyy.')
            ->assertSee('Pastikan nama bidang dan jabatan sesuai dengan data master.')
            ->assertSee('Status anggota yang tersedia: active, inactive, alumni, moved.');

        $response = $this->actingAs($user)->get(route('members.import.template'));

        $response->assertOk()
            ->assertDownload('template-import-anggota.xlsx');

        $zip = new ZipArchive();
        $zip->open($response->baseResponse->getFile()->getPathname());
        $worksheet = $zip->getFromName('xl/worksheets/sheet1.xml');
        $zip->close();

        foreach (['npa', 'full_name', 'phone', 'email', 'address', 'joined_at', 'department', 'position', 'member_status', 'notes'] as $header) {
            $this->assertStringContainsString($header, $worksheet);
        }

        foreach (['20.0001', 'Ahmad Fulan', '081234567890', 'ahmad.fulan@example.com', 'Kp. Cirengit', '10/06/2026', 'Pendidikan', 'Anggota', 'active', 'Contoh data anggota'] as $value) {
            $this->assertStringContainsString($value, $worksheet);
        }
    }

    public function test_internal_user_can_import_members_from_excel(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        Department::create(['name' => 'Pendidikan', 'status' => 'active']);
        Position::create(['name' => 'Anggota', 'status' => 'active']);
        Member::create([
            'full_name' => 'Nama Lama',
            'npa' => '20.0001',
            'email' => 'lama@example.test',
            'member_status' => 'active',
        ]);

        $file = $this->makeMemberImportFile([
            ['npa', 'full_name', 'phone', 'email', 'address', 'joined_at', 'department', 'position', 'member_status', 'notes'],
            ['20.0001', 'Ahmad Update', '081234567890', 'ahmad.update@example.com', 'Kp. Cirengit', '10/06/2026', 'Pendidikan', 'Anggota', 'active', 'Data update'],
            ['20.0002', 'Budi Baru', '081111111111', 'budi@example.com', 'Cirengit', '11/06/2026', 'Pendidikan', 'Anggota', 'inactive', 'Data baru'],
            ['20.0003', '', '', 'salah-email', '', '31/06/2026', 'Tidak Ada', 'Anggota', 'aktif', 'Data gagal'],
        ]);

        $this->actingAs($user)
            ->from(route('members.import'))
            ->post(route('members.import.store'), ['file' => $file])
            ->assertRedirect(route('members.import'))
            ->assertSessionHas('import_result', fn (array $result) => $result['created'] === 1
                && $result['updated'] === 1
                && $result['failed'] === 1
                && str_contains($result['errors'][0], 'Baris 4:'));

        $this->assertDatabaseHas('members', [
            'npa' => '20.0001',
            'full_name' => 'Ahmad Update',
            'joined_at' => '2026-06-10 00:00:00',
        ]);
        $this->assertDatabaseHas('members', [
            'npa' => '20.0002',
            'full_name' => 'Budi Baru',
            'member_status' => 'inactive',
        ]);
        $this->assertDatabaseMissing('members', ['npa' => '20.0003']);
    }

    public function test_member_import_requires_excel_file(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $this->actingAs($user)
            ->post(route('members.import.store'), ['file' => UploadedFile::fake()->create('anggota.pdf', 12, 'application/pdf')])
            ->assertSessionHasErrors(['file']);
    }

    public function test_internal_user_can_export_filtered_members_to_excel(): void
    {
        Carbon::setTestNow('2026-06-10 09:00:00');
        $user = User::factory()->create(['role' => 'secretary']);
        $education = Department::create(['name' => 'Pendidikan', 'status' => 'active']);
        $dakwah = Department::create(['name' => 'Dakwah', 'status' => 'active']);
        $memberPosition = Position::create(['name' => 'Anggota', 'status' => 'active']);
        $leaderPosition = Position::create(['name' => 'Ketua', 'status' => 'active']);
        $included = Member::create([
            'department_id' => $education->id,
            'position_id' => $memberPosition->id,
            'npa' => '20.0001',
            'full_name' => 'Ahmad Pendidikan',
            'phone' => '081234567890',
            'email' => 'ahmad@example.test',
            'address' => 'Kp. Cirengit',
            'joined_at' => '2026-06-10',
            'member_status' => 'active',
            'notes' => 'Catatan export',
        ]);
        Member::create([
            'department_id' => $dakwah->id,
            'position_id' => $leaderPosition->id,
            'npa' => '30.0001',
            'full_name' => 'Budi Dakwah',
            'phone' => '089999999999',
            'email' => 'budi@example.test',
            'member_status' => 'inactive',
        ]);
        User::factory()->create(['role' => 'member', 'member_id' => $included->id]);

        $response = $this->actingAs($user)->get(route('members.export', [
            'search' => 'Ahmad',
            'department_id' => $education->id,
            'position_id' => $memberPosition->id,
            'member_status' => 'active',
        ]));

        $response->assertOk()
            ->assertDownload('data-anggota-pemuda-cirengit-2026-06-10.xlsx');

        $worksheet = $this->worksheetXmlFromDownload($response);

        foreach (['No', 'NPA', 'Nama Lengkap', 'No HP', 'Email', 'Alamat', 'Tanggal Bergabung', 'Bidang', 'Jabatan', 'Status Anggota', 'Status Akun Login', 'Catatan'] as $header) {
            $this->assertStringContainsString($header, $worksheet);
        }

        foreach (['20.0001', 'Ahmad Pendidikan', '081234567890', 'ahmad@example.test', 'Kp. Cirengit', '10/06/2026', 'Pendidikan', 'Anggota', 'Aktif', 'Sudah Ada', 'Catatan export'] as $value) {
            $this->assertStringContainsString($value, $worksheet);
        }

        $this->assertStringNotContainsString('Budi Dakwah', $worksheet);
        $this->assertStringNotContainsString('30.0001', $worksheet);
    }

    public function test_member_role_cannot_export_members(): void
    {
        $member = Member::create(['full_name' => 'Anggota', 'member_status' => 'active']);
        $user = User::factory()->create(['role' => 'member', 'member_id' => $member->id]);

        $this->actingAs($user)
            ->get(route('members.export'))
            ->assertRedirect(route('member.home'))
            ->assertSessionHas('warning', 'Dashboard admin hanya dapat diakses oleh pengurus.');
    }

    public function test_member_role_cannot_download_member_import_template(): void
    {
        $member = Member::create(['full_name' => 'Anggota', 'member_status' => 'active']);
        $user = User::factory()->create(['role' => 'member', 'member_id' => $member->id]);

        $this->actingAs($user)
            ->get(route('members.import.template'))
            ->assertRedirect(route('member.home'))
            ->assertSessionHas('warning', 'Dashboard admin hanya dapat diakses oleh pengurus.');
    }

    private function makeMemberImportFile(array $rows): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'member-import-test-');
        $zip = new ZipArchive();
        $zip->open($path, ZipArchive::OVERWRITE);
        $zip->addFromString('[Content_Types].xml', <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
    <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
    <Default Extension="xml" ContentType="application/xml"/>
    <Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>
    <Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>
</Types>
XML);
        $zip->addFromString('_rels/.rels', <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
    <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>
</Relationships>
XML);
        $zip->addFromString('xl/workbook.xml', <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
    <sheets><sheet name="Import" sheetId="1" r:id="rId1"/></sheets>
</workbook>
XML);
        $zip->addFromString('xl/_rels/workbook.xml.rels', <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
    <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>
</Relationships>
XML);

        $rowXml = collect($rows)->map(function (array $row, int $rowIndex) {
            $cells = collect($row)->map(function (string $value, int $columnIndex) use ($rowIndex) {
                $columnName = chr(65 + $columnIndex);

                return sprintf(
                    '<c r="%s%d" t="inlineStr"><is><t>%s</t></is></c>',
                    $columnName,
                    $rowIndex + 1,
                    e($value)
                );
            })->implode('');

            return sprintf('<row r="%d">%s</row>', $rowIndex + 1, $cells);
        })->implode('');

        $zip->addFromString('xl/worksheets/sheet1.xml', <<<XML
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
    <sheetData>{$rowXml}</sheetData>
</worksheet>
XML);
        $zip->close();

        return new UploadedFile(
            $path,
            'anggota.xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            null,
            true
        );
    }

    private function worksheetXmlFromDownload($response): string
    {
        $zip = new ZipArchive();
        $zip->open($response->baseResponse->getFile()->getPathname());
        $worksheet = $zip->getFromName('xl/worksheets/sheet1.xml');
        $zip->close();

        return $worksheet;
    }
}
