<?php

namespace App\Http\Controllers;

use App\Models\BomSetting;
use Illuminate\Http\Request;

class BomSettingController extends Controller
{
    public function show()
    {
        return response()->json($this->settings());
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'introduction_title' => 'required|string|max:255',
            'introduction' => 'required|string',
            'focus_points' => 'nullable|array',
            'focus_points.*' => 'nullable|string|max:255',
        ]);

        $settings = $this->settings();
        $settings->update($validated);

        return response()->json($settings->fresh());
    }

    private function settings()
    {
        return BomSetting::firstOrCreate(
            ['id' => 1],
            [
                'introduction_title' => 'Board of Management',
                'introduction' => 'The Board of Management oversees strategic direction, programs, and professional associations.',
                'focus_points' => [
                    'Governance and institutional strategy',
                    'Professional development programs',
                    'Collaboration with associations',
                    'Operational oversight and planning',
                ],
            ]
        );
    }
}
