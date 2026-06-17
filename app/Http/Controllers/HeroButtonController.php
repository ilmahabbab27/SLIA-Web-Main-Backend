<?php

namespace App\Http\Controllers;

use App\Models\HeroButton;
use Illuminate\Http\Request;

class HeroButtonController extends Controller
{
    /** GET /api/hero-buttons — return all buttons ordered by sort_order */
    public function index()
    {
        return response()->json(HeroButton::orderBy('sort_order')->get());
    }

    /** POST /api/hero-buttons */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'label'      => 'required|string|max:255',
            'link'       => 'nullable|string|max:500',
            'is_active'  => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $btn = HeroButton::create($validated);
        return response()->json($btn, 201);
    }

    /** GET /api/hero-buttons/{heroButton} */
    public function show(HeroButton $heroButton)
    {
        return response()->json($heroButton);
    }

    /** PUT /api/hero-buttons/{heroButton} */
    public function update(Request $request, HeroButton $heroButton)
    {
        $validated = $request->validate([
            'label'      => 'nullable|string|max:255',
            'link'       => 'nullable|string|max:500',
            'is_active'  => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $heroButton->update($validated);
        return response()->json($heroButton->fresh());
    }

    /** DELETE /api/hero-buttons/{heroButton} */
    public function destroy(HeroButton $heroButton)
    {
        $heroButton->delete();
        return response()->json(null, 204);
    }
}
