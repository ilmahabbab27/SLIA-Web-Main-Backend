<?php

namespace App\Http\Controllers;

use App\Models\Board;
use Illuminate\Http\Request;

class BoardController extends Controller
{
    public function index()
    {
        return response()->json(Board::orderBy('sort_order')->get());
    }

    public function store(Request $request)
    {
        return response()->json(['message' => 'Governance boards are fixed. Only descriptions and logos can be updated.'], 405);
    }

    public function show(Board $board)
    {
        return response()->json($board);
    }

    public function update(Request $request, Board $board)
    {
        $validated = $request->validate([
            'description' => 'nullable|string',
            'icon' => 'nullable|string',
        ]);

        $board->update($validated);
        return response()->json($board->fresh());
    }

    public function destroy(Board $board)
    {
        return response()->json(['message' => 'Governance boards are fixed and cannot be deleted.'], 405);
    }
}
