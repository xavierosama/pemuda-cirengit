<?php

namespace App\Http\Controllers;

use Illuminate\View\View;
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
