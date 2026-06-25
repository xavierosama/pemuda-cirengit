<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Support\DateFormatter;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use ZipArchive;

class ActivityAttendanceExportController extends Controller
{
    public function __invoke(Activity $activity): BinaryFileResponse
    {
        $activity->load(['department', 'pic', 'attendances.member.department', 'attendances.member.position']);
        $attendances = $activity->attendances
            ->sortBy('member.full_name', SORT_NATURAL | SORT_FLAG_CASE)
            ->values();
        $summary = $this->summary($attendances);
        $rows = $this->rows($activity, $attendances, $summary);
        $path = tempnam(sys_get_temp_dir(), 'activity-attendance-export-');

        $zip = new ZipArchive();
        $zip->open($path, ZipArchive::OVERWRITE);
        $zip->addFromString('[Content_Types].xml', $this->contentTypesXml());
        $zip->addFromString('_rels/.rels', $this->rootRelationshipsXml());
        $zip->addFromString('xl/workbook.xml', $this->workbookXml());
        $zip->addFromString('xl/_rels/workbook.xml.rels', $this->workbookRelationshipsXml());
        $zip->addFromString('xl/styles.xml', $this->stylesXml());
        $zip->addFromString('xl/worksheets/sheet1.xml', $this->worksheetXml($rows));
        $zip->close();

        $filename = sprintf(
            'rekap-presensi-%s-%s.xlsx',
            Str::slug($activity->title),
            $activity->activity_date->format('Y-m-d')
        );

        return response()
            ->download($path, $filename, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ])
            ->deleteFileAfterSend();
    }

    private function summary($attendances): array
    {
        $present = $attendances->where('status', 'present')->count();
        $permission = $attendances->where('status', 'permission')->count();
        $absent = $attendances->where('status', 'absent')->count();
        $needVerification = $attendances->where('status', 'need_verification')->count();
        $total = $present + $permission + $absent + $needVerification;

        return [
            'present' => $present,
            'permission' => $permission,
            'absent' => $absent,
            'need_verification' => $needVerification,
            'attendance_percentage' => $total > 0 ? round(($present / $total) * 100, 2) : 0,
        ];
    }

    private function rows(Activity $activity, $attendances, array $summary): array
    {
        $statusLabels = [
            'present' => 'Hadir',
            'permission' => 'Izin',
            'absent' => 'Tidak Hadir',
            'need_verification' => 'Perlu Verifikasi',
        ];
        $methodLabels = [
            'manual' => 'Manual',
            'link' => 'Link',
            'qr' => 'QR',
        ];
        $verificationLabels = [
            'valid' => 'Valid',
            'need_verification' => 'Perlu Verifikasi',
            'rejected' => 'Ditolak',
        ];
        $time = trim(($activity->start_time ? substr($activity->start_time, 0, 5) : '').($activity->end_time ? ' - '.substr($activity->end_time, 0, 5) : ''));

        $rows = [
            ['Nama kegiatan', $activity->title],
            ['Tanggal kegiatan', DateFormatter::date($activity->activity_date)],
            ['Waktu kegiatan', $time ?: '-'],
            ['Lokasi kegiatan', $activity->location ?: '-'],
            ['Bidang', $activity->department?->name ?? '-'],
            ['PIC kegiatan', $activity->pic?->full_name ?? '-'],
            ['Total Hadir', (string) $summary['present']],
            ['Total Izin', (string) $summary['permission']],
            ['Total Tidak Hadir', (string) $summary['absent']],
            ['Total Perlu Verifikasi', (string) $summary['need_verification']],
            ['Persentase Kehadiran', number_format($summary['attendance_percentage'], 2).'%'],
            [],
            ['No', 'NPA', 'Nama Anggota', 'Bidang', 'Jabatan', 'Status Kehadiran', 'Metode Presensi', 'Waktu Presensi', 'Jarak dari Lokasi Kegiatan', 'Status Verifikasi', 'Catatan'],
        ];

        foreach ($attendances as $index => $attendance) {
            $rows[] = [
                (string) ($index + 1),
                $attendance->member->npa ?: '',
                $attendance->member->full_name,
                $attendance->member->department?->name ?? '',
                $attendance->member->position?->name ?? '',
                $statusLabels[$attendance->status] ?? $attendance->status,
                $methodLabels[$attendance->attendance_method] ?? $attendance->attendance_method,
                DateFormatter::dateTime($attendance->checked_in_at, ''),
                $attendance->distance_from_activity !== null ? number_format((float) $attendance->distance_from_activity, 2).' m' : '',
                $verificationLabels[$attendance->verification_status] ?? $attendance->verification_status,
                $attendance->notes ?: '',
            ];
        }

        return $rows;
    }

    private function worksheetXml(array $rows): string
    {
        $rowXml = collect($rows)
            ->map(function (array $row, int $rowIndex) {
                $style = in_array($rowIndex, [0, 12], true) ? ' s="1"' : '';
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
        <col min="1" max="1" width="12" customWidth="1"/>
        <col min="2" max="2" width="22" customWidth="1"/>
        <col min="3" max="3" width="28" customWidth="1"/>
        <col min="4" max="5" width="20" customWidth="1"/>
        <col min="6" max="7" width="20" customWidth="1"/>
        <col min="8" max="8" width="22" customWidth="1"/>
        <col min="9" max="9" width="24" customWidth="1"/>
        <col min="10" max="10" width="20" customWidth="1"/>
        <col min="11" max="11" width="30" customWidth="1"/>
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
        <sheet name="Rekap Presensi" sheetId="1" r:id="rId1"/>
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
