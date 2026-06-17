<?php

namespace App\Http\Controllers;

use App\Models\BapSetting;
use Illuminate\Http\Request;

class BapSettingController extends Controller
{
    public function show()
    {
        return response()->json($this->settings());
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'introduction' => 'required|string',
        ]);

        $settings = $this->settings();
        $settings->update($validated);

        return response()->json($settings->fresh());
    }

    private function settings()
    {
        return BapSetting::firstOrCreate(
            ['id' => 1],
            ['introduction' => 'Browse SLIA publications and archives. Use the Publications tab to download issues and reference material.']
        );
    }
}
