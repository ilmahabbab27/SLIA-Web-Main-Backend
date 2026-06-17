<?php

namespace App\Http\Controllers;

use App\Models\AwardSetting;
use Illuminate\Http\Request;

class AwardSettingController extends Controller
{
    /** GET /api/award-settings */
    public function show()
    {
        return response()->json($this->settings());
    }

    /** PUT /api/award-settings */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'year' => 'required|string|max:20',
            'general_guideline' => 'required|string',
        ]);

        $settings = $this->settings();
        $settings->update($validated);

        return response()->json($settings->fresh());
    }

    private function settings()
    {
        return AwardSetting::firstOrCreate(
            ['id' => 1],
            [
                'year' => date('Y'),
                'general_guideline' => '',
            ]
        );
    }
}
