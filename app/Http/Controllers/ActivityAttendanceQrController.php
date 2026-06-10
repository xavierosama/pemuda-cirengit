<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\SvgWriter;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ActivityAttendanceQrController extends Controller
{
    public function show(Activity $activity): View
    {
        if (! $activity->attendance_enabled) {
            return view('activities.attendance-qr', [
                'activity' => $activity,
                'attendanceUrl' => null,
                'qrCode' => null,
            ]);
        }

        if (! $activity->attendance_token) {
            $activity->update([
                'attendance_token' => $this->generateUniqueToken(),
            ]);
        }

        $attendanceUrl = route('attendance.check-in.show', $activity->attendance_token, true);
        $result = (new Builder(
            writer: new SvgWriter(),
            writerOptions: [
                SvgWriter::WRITER_OPTION_EXCLUDE_XML_DECLARATION => true,
            ],
            validateResult: false,
            data: $attendanceUrl,
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: ErrorCorrectionLevel::High,
            size: 420,
            margin: 16,
            roundBlockSizeMode: RoundBlockSizeMode::Margin,
        ))->build();

        return view('activities.attendance-qr', [
            'activity' => $activity,
            'attendanceUrl' => $attendanceUrl,
            'qrCode' => $result->getDataUri(),
        ]);
    }

    private function generateUniqueToken(): string
    {
        do {
            $token = Str::random(40);
        } while (Activity::where('attendance_token', $token)->exists());

        return $token;
    }
}
