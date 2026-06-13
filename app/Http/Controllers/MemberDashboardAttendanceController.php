<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Attendance;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class MemberDashboardAttendanceController extends Controller
{
    public function store(Request $request, Activity $activity): RedirectResponse
    {
        $member = $request->user()->member;

        if (! $member) {
            return back()->with('error', 'Akun Anda belum terhubung dengan data anggota.');
        }

        if ($this->availability($activity) !== 'open') {
            return back()->with('error', 'Presensi kegiatan ini tidak sedang dibuka.');
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

        if ($attendance && $attendance->status !== 'absent') {
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
            ->route('member.home')
            ->with(
                $insideRadius ? 'success' : 'warning',
                $insideRadius
                    ? 'Presensi berhasil. Lokasi Anda berada dalam radius kegiatan.'
                    : 'Presensi tersimpan dan perlu verifikasi admin karena lokasi berada di luar radius.'
            );
    }

    private function availability(Activity $activity): string
    {
        if (! $activity->attendance_enabled) {
            return 'disabled';
        }

        if ($activity->status === 'cancelled' || $activity->status === 'holiday') {
            return 'disabled';
        }

        if (! $activity->attendance_open_at || ! $activity->attendance_close_at) {
            return 'not_configured';
        }

        if (now()->lt($activity->attendance_open_at)) {
            return 'not_open';
        }

        if (now()->gt($activity->attendance_close_at)) {
            return 'closed';
        }

        return 'open';
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
