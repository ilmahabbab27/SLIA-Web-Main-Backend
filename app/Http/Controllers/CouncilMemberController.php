<?php

namespace App\Http\Controllers;

use App\Models\CouncilMember;
use Illuminate\Http\Request;

class CouncilMemberController extends Controller
{
    public function index()
    {
        return response()->json(CouncilMember::orderBy('sort_order')->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'image'       => 'nullable|string',
            'name'        => 'required|string|max:255',
            'designation' => 'required|string|max:255',
            'is_active'   => 'nullable|boolean',
            'sort_order'  => 'nullable|integer|min:0',
        ]);

        $member = CouncilMember::create($validated);
        return response()->json($member, 201);
    }

    public function show(CouncilMember $councilMember)
    {
        return response()->json($councilMember);
    }

    public function update(Request $request, CouncilMember $councilMember)
    {
        $validated = $request->validate([
            'image'       => 'nullable|string',
            'name'        => 'nullable|string|max:255',
            'designation' => 'nullable|string|max:255',
            'is_active'   => 'nullable|boolean',
            'sort_order'  => 'nullable|integer|min:0',
        ]);

        $councilMember->update($validated);
        return response()->json($councilMember->fresh());
    }

    public function destroy(CouncilMember $councilMember)
    {
        $councilMember->delete();
        return response()->json(null, 204);
    }
}
