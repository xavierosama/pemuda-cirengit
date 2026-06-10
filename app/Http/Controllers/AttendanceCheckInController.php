<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Attendance;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AttendanceCheckInController extends Controller
{
    public function show(Request $request, string $token): View
    {
        $activity = $this->findActivity($token);
        $member = $request->user()->member;
        $attendance = $member
            ? Attendance::where('activity_id', $activity->id)->where('member_id', $member->id)->first()
            : null;

        return view('attendance-check-in.show', [
            'activity' => $activity,
            'member' => $member,
            'attendance' => $attendance,
            'availability' => $this->availability($activity),
            'canRetry' => $this->canRetry($attendance),
        ]);
    }

    public function store(Request $request, string $token): RedirectResponse
    {
        $activity = $this->findActivity($token);
        $member = $request->user()->member;

        if (! $member) {
            return back()->with('error', 'Akun Anda belum terhubung dengan data anggota.');
        }

        $availability = $this->availability($activity);

        if ($availability !== 'open') {
            return back()->with('error', $this->availabilityMessage($availability));
        }

        if ($activity->latitude === null || $activity->longitude === null) {
            return back()->with('error', 'Titik lokasi kegiatan belum dikonfigurasi oleh admin.');
        }

        $validated = $request->validate([
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'location_accuracy' => ['required', 'numeric', 'min:0'],
        ]);

        $attendance = Attendance::where('activity_id', $activity->id)
            ->where('member_id', $member->id)
            ->first();

        if ($attendance && ! $this->canRetry($attendance)) {
            return back()->with('info', 'Presensi Anda untuk kegiatan ini sudah tercatat.');
        }

        $distance = $this->haversineDistance(
            (float) $validated['latitude'],
            (float) $validated['longitude'],
            (float) $activity->latitude,
            (float) $activity->longitude
        );

        $insideRadius = $distance <= $activity->attendance_radius;
        $attendance ??= new Attendance([
            'activity_id' => $activity->id,
            'member_id' => $member->id,
            'created_by' => $request->user()->id,
        ]);

        $attendance->fill([
            'status' => $insideRadius ? 'present' : 'need_verification',
            'attendance_method' => 'link',
            'checked_in_at' => now(),
            'latitude' => $validated['latitude'],
            'longitude' => $validated['longitude'],
            'distance_from_activity' => round($distance, 2),
            'location_accuracy' => $validated['location_accuracy'],
            'verification_status' => $insideRadius ? 'valid' : 'need_verification',
            'verified_by' => null,
            'verified_at' => null,
        ]);
        $attendance->save();

        return redirect()
            ->route('attendance.check-in.show', $token)
            ->with(
                $insideRadius ? 'success' : 'warning',
                $insideRadius
                    ? 'Presensi berhasil. Lokasi Anda berada dalam radius kegiatan.'
                    : 'Presensi tersimpan dan perlu verifikasi admin karena lokasi berada di luar radius.'
            );
    }

    private function findActivity(string $token): Activity
    {
        return Activity::where('attendance_token', $token)->firstOrFail();
    }

    private function availability(Activity $activity): string
    {
        if (! $activity->attendance_enabled) {
            return 'disabled';
        }

        if (! $activity->attendance_open_at || ! $activity->attendance_close_at) {
            return 'not_configured';
        }

        if ($activity->attendance_open_at && now()->lt($activity->attendance_open_at)) {
            return 'not_open';
        }

        if ($activity->attendance_close_at && now()->gt($activity->attendance_close_at)) {
            return 'closed';
        }

        return 'open';
    }

    private function availabilityMessage(string $availability): string
    {
        return match ($availability) {
            'disabled' => 'Presensi belum diaktifkan untuk kegiatan ini.',
            'not_configured' => 'Waktu presensi belum dikonfigurasi oleh admin.',
            'not_open' => 'Presensi belum dibuka.',
            'closed' => 'Presensi sudah ditutup.',
            default => 'Presensi tidak tersedia.',
        };
    }

    private function canRetry(?Attendance $attendance): bool
    {
        if (! $attendance) {
            return true;
        }

        if ($attendance->status === 'absent') {
            return true;
        }

        return $attendance->status === 'need_verification'
            && $attendance->verification_status === 'need_verification'
            && $attendance->verified_by === null
            && $attendance->verified_at === null;
    }

    private function haversineDistance(float $latitude, float $longitude, float $targetLatitude, float $targetLongitude): float
    {
        $earthRadius = 6371000;
        $latitudeDelta = deg2rad($targetLatitude - $latitude);
        $longitudeDelta = deg2rad($targetLongitude - $longitude);

        $a = sin($latitudeDelta / 2) ** 2
            + cos(deg2rad($latitude)) * cos(deg2rad($targetLatitude))
            * sin($longitudeDelta / 2) ** 2;

        return $earthRadius * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }
}
