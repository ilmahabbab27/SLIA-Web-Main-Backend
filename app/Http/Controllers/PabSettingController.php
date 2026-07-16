<?php

namespace App\Http\Controllers;

use App\Models\PabSetting;
use Illuminate\Http\Request;

class PabSettingController extends Controller
{
    public function show()
    {
        $setting = PabSetting::first();
        if (!$setting) {
            $setting = PabSetting::create(['introduction' => '']);
        }
        return response()->json($setting);
    }

    public function update(Request $request)
    {
        $setting = PabSetting::first();
        if (!$setting) {
            $setting = PabSetting::create(['introduction' => '']);
        }

        $setting->update($request->only('introduction'));
        return response()->json($setting);
    }
}
