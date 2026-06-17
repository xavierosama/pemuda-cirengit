<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Support\SystemSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SystemSettingController extends Controller
{
    public function edit(SystemSettings $systemSettings): View
    {
        return view('settings.edit', [
            'settings' => $systemSettings->all(),
            'logoUrls' => [
                'app_logo' => $systemSettings->assetUrl('app_logo'),
                'login_logo' => $systemSettings->assetUrl('login_logo'),
                'favicon' => $systemSettings->assetUrl('favicon'),
            ],
        ]);
    }

    public function update(Request $request, SystemSettings $systemSettings): RedirectResponse
    {
        $validated = $request->validate([
            'app_name' => ['required', 'string', 'max:255'],
            'organization_name' => ['required', 'string', 'max:255'],
            'theme_mode' => ['required', Rule::in(['light', 'dark', 'system'])],
            'app_logo' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,svg', 'max:2048'],
            'login_logo' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,svg', 'max:2048'],
            'favicon' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,svg,ico', 'max:2048'],
            'default_attendance_radius' => ['required', 'integer', 'min:1'],
            'default_attendance_open_minutes_before' => ['required', 'integer', 'min:0'],
            'default_attendance_close_minutes_after' => ['required', 'integer', 'min:0'],
            'default_location_accuracy_tolerance' => ['required', 'integer', 'min:0'],
            'whatsapp_group_reminder_template' => ['nullable', 'string', 'max:5000'],
        ]);

        $systemSettings->set('app_name', $validated['app_name'], 'string');
        $systemSettings->set('organization_name', $validated['organization_name'], 'string');
        $systemSettings->set('theme_mode', $validated['theme_mode'], 'string');
        $systemSettings->set('default_attendance_radius', (string) $validated['default_attendance_radius'], 'integer');
        $systemSettings->set('default_attendance_open_minutes_before', (string) $validated['default_attendance_open_minutes_before'], 'integer');
        $systemSettings->set('default_attendance_close_minutes_after', (string) $validated['default_attendance_close_minutes_after'], 'integer');
        $systemSettings->set('default_location_accuracy_tolerance', (string) $validated['default_location_accuracy_tolerance'], 'integer');
        $systemSettings->set('whatsapp_group_reminder_template', $validated['whatsapp_group_reminder_template'] ?? null, 'text');

        foreach (['app_logo', 'login_logo', 'favicon'] as $fileKey) {
            if (! $request->hasFile($fileKey)) {
                continue;
            }

            $oldPath = Setting::query()->where('key', $fileKey)->value('value');

            if ($oldPath) {
                Storage::disk('public')->delete($oldPath);
            }

            $systemSettings->set(
                $fileKey,
                $request->file($fileKey)->store('settings', 'public'),
                'file',
            );
        }

        return redirect()
            ->route('settings.edit')
            ->with('success', 'Pengaturan sistem berhasil diperbarui.');
    }
}
