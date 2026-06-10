<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Member;
use App\Models\Position;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use ZipArchive;

class MemberImportTemplateController extends Controller
{
    public function show(): View
    {
        return view('members.import');
    }

    public function download(): BinaryFileResponse
    {
        $path = tempnam(sys_get_temp_dir(), 'member-import-template-');

        $zip = new ZipArchive();
        $zip->open($path, ZipArchive::OVERWRITE);
        $zip->addFromString('[Content_Types].xml', $this->contentTypesXml());
        $zip->addFromString('_rels/.rels', $this->rootRelationshipsXml());
        $zip->addFromString('xl/workbook.xml', $this->workbookXml());
        $zip->addFromString('xl/_rels/workbook.xml.rels', $this->workbookRelationshipsXml());
        $zip->addFromString('xl/styles.xml', $this->stylesXml());
        $zip->addFromString('xl/worksheets/sheet1.xml', $this->worksheetXml());
        $zip->close();

        return response()
            ->download($path, 'template-import-anggota.xlsx', [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ])
            ->deleteFileAfterSend();
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls', 'max:5120'],
        ], [
            'file.required' => 'File Excel wajib diunggah.',
            'file.mimes' => 'File harus berformat .xlsx atau .xls.',
            'file.max' => 'Ukuran file maksimal 5MB.',
        ]);

        $file = $validated['file'];
        $result = [
            'created' => 0,
            'updated' => 0,
            'failed' => 0,
            'errors' => [],
        ];

        if ($file->getClientOriginalExtension() === 'xls') {
            return back()
                ->withInput()
                ->with('import_result', [
                    ...$result,
                    'failed' => 1,
                    'errors' => ['File .xls lama belum dapat diproses. Simpan ulang file sebagai .xlsx lalu import kembali.'],
                ]);
        }

        try {
            $rows = $this->readRowsFromXlsx($file->getRealPath());
        } catch (\Throwable $exception) {
            return back()
                ->withInput()
                ->with('import_result', [
                    ...$result,
                    'failed' => 1,
                    'errors' => ['File Excel tidak dapat dibaca. Pastikan menggunakan template yang tersedia.'],
                ]);
        }

        if (count($rows) < 2) {
            return back()
                ->withInput()
                ->with('import_result', [
                    ...$result,
                    'failed' => 1,
                    'errors' => ['File Excel belum berisi data anggota.'],
                ]);
        }

        $headers = array_map(fn ($header) => str($header)->trim()->lower()->toString(), $rows[0]);
        $expectedHeaders = ['npa', 'full_name', 'phone', 'email', 'address', 'joined_at', 'department', 'position', 'member_status', 'notes'];

        if ($headers !== $expectedHeaders) {
            return back()
                ->withInput()
                ->with('import_result', [
                    ...$result,
                    'failed' => count($rows) - 1,
                    'errors' => ['Header kolom tidak sesuai template. Gunakan template yang tersedia.'],
                ]);
        }

        foreach (array_slice($rows, 1) as $index => $row) {
            $rowNumber = $index + 2;
            $row = array_pad($row, count($headers), null);
            $data = array_combine($headers, array_slice($row, 0, count($headers)));
            $data = collect($data)->map(fn ($value) => is_string($value) ? trim($value) : $value)->all();

            if (collect($data)->filter(fn ($value) => filled($value))->isEmpty()) {
                continue;
            }

            $department = $this->findDepartment($data['department'] ?? null);
            $position = $this->findPosition($data['position'] ?? null);
            $existingMember = $this->findExistingMember($data['npa'] ?? null, $data['email'] ?? null);
            $joinedAt = $this->parseDate($data['joined_at'] ?? null);

            $validator = Validator::make([
                ...$data,
                'department_id' => $department?->id,
                'position_id' => $position?->id,
                'joined_at_parsed' => $joinedAt,
            ], [
                'full_name' => ['required', 'string', 'max:255'],
                'npa' => [
                    'nullable',
                    'string',
                    'max:255',
                    Rule::unique('members', 'npa')->ignore($existingMember),
                ],
                'phone' => ['nullable', 'string', 'max:50'],
                'email' => ['nullable', 'email', 'max:255'],
                'address' => ['nullable', 'string'],
                'joined_at_parsed' => ['nullable', 'date'],
                'department_id' => filled($data['department'] ?? null) ? ['required'] : ['nullable'],
                'position_id' => filled($data['position'] ?? null) ? ['required'] : ['nullable'],
                'member_status' => ['required', Rule::in(['active', 'inactive', 'alumni', 'moved'])],
                'notes' => ['nullable', 'string'],
            ], [
                'full_name.required' => 'Nama anggota wajib diisi.',
                'npa.unique' => 'NPA sudah digunakan oleh anggota lain.',
                'email.email' => 'Email tidak valid.',
                'joined_at_parsed.date' => 'Tanggal bergabung harus format dd/mm/yyyy.',
                'department_id.required' => 'Nama bidang tidak ditemukan di data master.',
                'position_id.required' => 'Nama jabatan tidak ditemukan di data master.',
                'member_status.required' => 'Status anggota wajib diisi.',
                'member_status.in' => 'Status anggota harus active, inactive, alumni, atau moved.',
            ]);

            if ($validator->fails()) {
                $result['failed']++;
                $result['errors'][] = 'Baris '.$rowNumber.': '.$validator->errors()->first();
                continue;
            }

            $payload = [
                'npa' => $data['npa'] ?: null,
                'full_name' => $data['full_name'],
                'phone' => $data['phone'] ?: null,
                'email' => $data['email'] ?: null,
                'address' => $data['address'] ?: null,
                'joined_at' => $joinedAt,
                'department_id' => $department?->id,
                'position_id' => $position?->id,
                'member_status' => $data['member_status'],
                'notes' => $data['notes'] ?: null,
            ];

            if ($existingMember) {
                $existingMember->update($payload);
                $result['updated']++;
            } else {
                Member::create($payload);
                $result['created']++;
            }
        }

        return redirect()->route('members.import')->with('import_result', $result);
    }

    private function readRowsFromXlsx(string $path): array
    {
        $zip = new ZipArchive();

        if ($zip->open($path) !== true) {
            throw new \RuntimeException('Cannot open xlsx file.');
        }

        $sharedStrings = $this->readSharedStrings($zip);
        $worksheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        $zip->close();

        if ($worksheetXml === false) {
            throw new \RuntimeException('Worksheet not found.');
        }

        $document = new \DOMDocument();
        $document->loadXML($worksheetXml);
        $xpath = new \DOMXPath($document);

        $rows = [];
        foreach ($xpath->query('//*[local-name()="sheetData"]/*[local-name()="row"]') as $row) {
            $cells = [];

            foreach ($xpath->query('./*[local-name()="c"]', $row) as $cell) {
                $reference = $cell->getAttribute('r');
                preg_match('/[A-Z]+/', $reference, $matches);
                $columnIndex = $this->columnIndex($matches[0] ?? 'A');
                $cells[$columnIndex] = $this->cellValue($cell, $sharedStrings, $xpath);
            }

            if ($cells !== []) {
                ksort($cells);
                $rows[] = $this->normalizeRow($cells);
            }
        }

        return $rows;
    }

    private function readSharedStrings(ZipArchive $zip): array
    {
        $sharedStringsXml = $zip->getFromName('xl/sharedStrings.xml');

        if ($sharedStringsXml === false) {
            return [];
        }

        $sharedStrings = simplexml_load_string($sharedStringsXml);
        $values = [];

        foreach ($sharedStrings->children('http://schemas.openxmlformats.org/spreadsheetml/2006/main')->si as $stringItem) {
            $text = '';
            $stringItemChildren = $stringItem->children('http://schemas.openxmlformats.org/spreadsheetml/2006/main');

            if (isset($stringItemChildren->t)) {
                $text = (string) $stringItemChildren->t;
            } else {
                foreach ($stringItemChildren->r as $run) {
                    $text .= (string) $run->children('http://schemas.openxmlformats.org/spreadsheetml/2006/main')->t;
                }
            }

            $values[] = $text;
        }

        return $values;
    }

    private function cellValue(\DOMElement $cell, array $sharedStrings, \DOMXPath $xpath): ?string
    {
        $type = $cell->getAttribute('t');

        return match ($type) {
            'inlineStr' => $this->inlineStringValue($cell, $xpath),
            's' => $sharedStrings[(int) $this->directChildText($cell, 'v', $xpath)] ?? null,
            default => $this->directChildText($cell, 'v', $xpath),
        };
    }

    private function inlineStringValue(\DOMElement $cell, \DOMXPath $xpath): string
    {
        $value = '';

        foreach ($xpath->query('.//*[local-name()="t"]', $cell) as $textNode) {
            $value .= $textNode->textContent;
        }

        return $value;
    }

    private function directChildText(\DOMElement $cell, string $childName, \DOMXPath $xpath): ?string
    {
        $node = $xpath->query('./*[local-name()="'.$childName.'"]', $cell)->item(0);

        return $node?->textContent;
    }

    private function normalizeRow(array $cells): array
    {
        $row = [];
        $maxColumn = max(array_keys($cells));

        for ($column = 1; $column <= $maxColumn; $column++) {
            $row[] = $cells[$column] ?? null;
        }

        return $row;
    }

    private function columnIndex(string $columnName): int
    {
        $index = 0;

        foreach (str_split($columnName) as $character) {
            $index = ($index * 26) + (ord($character) - 64);
        }

        return $index;
    }

    private function findDepartment(?string $name): ?Department
    {
        if (blank($name)) {
            return null;
        }

        return Department::whereRaw('LOWER(name) = ?', [strtolower(trim($name))])->first();
    }

    private function findPosition(?string $name): ?Position
    {
        if (blank($name)) {
            return null;
        }

        return Position::whereRaw('LOWER(name) = ?', [strtolower(trim($name))])->first();
    }

    private function findExistingMember(?string $npa, ?string $email): ?Member
    {
        if (filled($npa)) {
            return Member::where('npa', $npa)->first();
        }

        if (filled($email)) {
            return Member::where('email', $email)->first();
        }

        return null;
    }

    private function parseDate(?string $date): ?string
    {
        if (blank($date)) {
            return null;
        }

        try {
            return Carbon::createFromFormat('d/m/Y', trim($date))->format('Y-m-d');
        } catch (\Throwable) {
            return 'invalid-date';
        }
    }

    private function worksheetXml(): string
    {
        $rows = [
            ['npa', 'full_name', 'phone', 'email', 'address', 'joined_at', 'department', 'position', 'member_status', 'notes'],
            ['20.0001', 'Ahmad Fulan', '081234567890', 'ahmad.fulan@example.com', 'Kp. Cirengit', '10/06/2026', 'Pendidikan', 'Anggota', 'active', 'Contoh data anggota'],
        ];

        $rowXml = collect($rows)
            ->map(function (array $row, int $rowIndex) {
                $style = $rowIndex === 0 ? ' s="1"' : '';
                $cells = collect($row)
                    ->map(fn (string $value, int $columnIndex) => sprintf(
                        '<c r="%s%d" t="inlineStr"%s><is><t>%s</t></is></c>',
                        $this->columnName($columnIndex + 1),
                        $rowIndex + 1,
                        $style,
                        e($value)
                    ))
                    ->implode('');

                return sprintf('<row r="%d">%s</row>', $rowIndex + 1, $cells);
            })
            ->implode('');

        return <<<XML
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
    <cols>
        <col min="1" max="1" width="14" customWidth="1"/>
        <col min="2" max="2" width="24" customWidth="1"/>
        <col min="3" max="3" width="18" customWidth="1"/>
        <col min="4" max="4" width="28" customWidth="1"/>
        <col min="5" max="5" width="22" customWidth="1"/>
        <col min="6" max="6" width="14" customWidth="1"/>
        <col min="7" max="8" width="18" customWidth="1"/>
        <col min="9" max="9" width="16" customWidth="1"/>
        <col min="10" max="10" width="26" customWidth="1"/>
    </cols>
    <sheetData>{$rowXml}</sheetData>
</worksheet>
XML;
    }

    private function columnName(int $columnNumber): string
    {
        $columnName = '';

        while ($columnNumber > 0) {
            $modulo = ($columnNumber - 1) % 26;
            $columnName = chr(65 + $modulo).$columnName;
            $columnNumber = intdiv($columnNumber - $modulo, 26);
        }

        return $columnName;
    }

    private function contentTypesXml(): string
    {
        return <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
    <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
    <Default Extension="xml" ContentType="application/xml"/>
    <Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>
    <Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>
    <Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>
</Types>
XML;
    }

    private function rootRelationshipsXml(): string
    {
        return <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
    <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>
</Relationships>
XML;
    }

    private function workbookXml(): string
    {
        return <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
    <sheets>
        <sheet name="Template Import Anggota" sheetId="1" r:id="rId1"/>
    </sheets>
</workbook>
XML;
    }

    private function workbookRelationshipsXml(): string
    {
        return <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
    <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>
    <Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>
</Relationships>
XML;
    }

    private function stylesXml(): string
    {
        return <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
    <fonts count="2">
        <font><sz val="11"/><name val="Calibri"/></font>
        <font><b/><sz val="11"/><name val="Calibri"/></font>
    </fonts>
    <fills count="1"><fill><patternFill patternType="none"/></fill></fills>
    <borders count="1"><border><left/><right/><top/><bottom/><diagonal/></border></borders>
    <cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>
    <cellXfs count="2">
        <xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>
        <xf numFmtId="0" fontId="1" fillId="0" borderId="0" xfId="0" applyFont="1"/>
    </cellXfs>
</styleSheet>
XML;
    }
}
