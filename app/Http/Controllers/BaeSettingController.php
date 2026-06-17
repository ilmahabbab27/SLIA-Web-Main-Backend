<?php

namespace App\Http\Controllers;

use App\Models\BaeSetting;
use Illuminate\Http\Request;

class BaeSettingController extends Controller
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
        return BaeSetting::firstOrCreate(
            ['id' => 1],
            ['introduction' => "The Board of Architectural Education supports the Institute's education and examination related work, including application processes, notices, reference material, and member resources."]
        );
    }
}
