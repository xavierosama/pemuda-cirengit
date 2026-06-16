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
            'availability' => $activity->attendanceAvailability(),
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

        $availability = $activity->attendanceAvailability();

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

        $redirect = $request->user()->role === 'member'
            ? redirect()->route('member.home')
            : redirect()->route('attendance.check-in.show', $token);

        return $redirect->with(
                $insideRadius ? 'success' : 'warning',
                $insideRadius
                    ? 'Presensi berhasil. Lokasi Anda berada dalam radius kegiatan.'
                    : 'Presensi tersimpan dan perlu verifikasi admin karena lokasi berada di luar radius.'
            );
    }

    public function permission(Request $request, string $token): RedirectResponse
    {
        $activity = $this->findActivity($token);
        $member = $request->user()->member;

        if (! $member) {
            return back()->with('error', 'Akun Anda belum terhubung dengan data anggota.');
        }

        $availability = $activity->attendanceAvailability();

        if ($availability !== 'open') {
            return back()->with('error', 'Pengajuan izin hanya tersedia saat presensi kegiatan sedang dibuka.');
        }

        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:500'],
        ]);

        $attendance = Attendance::where('activity_id', $activity->id)
            ->where('member_id', $member->id)
            ->first();

        if ($attendance && $attendance->status !== 'absent') {
            return back()->with('info', 'Presensi Anda untuk kegiatan ini sudah tercatat.');
        }

        $attendance ??= new Attendance([
            'activity_id' => $activity->id,
            'member_id' => $member->id,
            'created_by' => $request->user()->id,
        ]);

        $attendance->fill([
            'status' => 'permission',
            'attendance_method' => 'link',
            'checked_in_at' => null,
            'latitude' => null,
            'longitude' => null,
            'distance_from_activity' => null,
            'location_accuracy' => null,
            'verification_status' => 'valid',
            'verified_by' => null,
            'verified_at' => null,
            'notes' => $validated['reason'],
        ]);
        $attendance->save();

        $redirect = $request->user()->role === 'member'
            ? redirect()->route('member.home')
            : redirect()->route('attendance.check-in.show', $token);

        return $redirect->with('success', 'Pengajuan izin berhasil dikirim.');
    }

    private function findActivity(string $token): Activity
    {
        return Activity::where('attendance_token', $token)->firstOrFail();
    }

    private function availabilityMessage(string $availability): string
    {
        return match ($availability) {
            'not_available' => 'Presensi tidak tersedia untuk kegiatan ini.',
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
