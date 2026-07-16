<?php

namespace App\Http\Controllers;

use App\Models\PabPublication;
use Illuminate\Http\Request;

class PabPublicationController extends Controller
{
    public function index()
    {
        return response()->json(PabPublication::orderBy('sort_order')->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string',
            'description' => 'nullable|string',
            'file_name' => 'nullable|string',
            'file_src' => 'nullable|string',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ]);

        $pub = PabPublication::create($validated);
        return response()->json($pub, 201);
    }

    public function show($id)
    {
        $pub = PabPublication::findOrFail($id);
        return response()->json($pub);
    }

    public function update(Request $request, $id)
    {
        $pub = PabPublication::findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string',
            'description' => 'nullable|string',
            'file_name' => 'nullable|string',
            'file_src' => 'nullable|string',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ]);

        $pub->update($validated);
        return response()->json($pub);
    }

    public function destroy($id)
    {
        PabPublication::findOrFail($id)->delete();
        return response()->json(null, 204);
    }
}
