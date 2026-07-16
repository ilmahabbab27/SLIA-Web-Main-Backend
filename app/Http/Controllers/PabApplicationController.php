<?php

namespace App\Http\Controllers;

use App\Models\PabApplication;
use Illuminate\Http\Request;

class PabApplicationController extends Controller
{
    public function index()
    {
        return response()->json(PabApplication::orderBy('sort_order')->get());
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

        $app = PabApplication::create($validated);
        return response()->json($app, 201);
    }

    public function show($id)
    {
        $app = PabApplication::findOrFail($id);
        return response()->json($app);
    }

    public function update(Request $request, $id)
    {
        $app = PabApplication::findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string',
            'description' => 'nullable|string',
            'file_name' => 'nullable|string',
            'file_src' => 'nullable|string',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ]);

        $app->update($validated);
        return response()->json($app);
    }

    public function destroy($id)
    {
        PabApplication::findOrFail($id)->delete();
        return response()->json(null, 204);
    }
}
