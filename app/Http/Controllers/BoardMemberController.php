<?php

namespace App\Http\Controllers;

use App\Models\BoardMember;
use Illuminate\Http\Request;

class BoardMemberController extends Controller
{
    public function index(Request $request)
    {
        $query = BoardMember::query();

        if ($request->filled('board_key')) {
            $query->where('board_key', $request->query('board_key'));
        }

        return response()->json($query->orderBy('sort_order')->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'board_key'   => 'required|string|in:trustees,management,bae,pab,bap',
            'image'       => 'nullable|string',
            'name'        => 'required|string|max:255',
            'designation' => 'required|string|max:255',
            'is_active'   => 'nullable|boolean',
            'sort_order'  => 'nullable|integer|min:0',
        ]);

        if ($request->user() && $request->user()->role === 'board_admin') {
            $validated['board_key'] = $request->user()->board_key;
        }

        $member = BoardMember::create($validated);
        return response()->json($member, 201);
    }

    public function show(BoardMember $boardMember)
    {
        return response()->json($boardMember);
    }

    public function update(Request $request, BoardMember $boardMember)
    {
        $validated = $request->validate([
            'board_key'   => 'nullable|string|in:trustees,management,bae,pab,bap',
            'image'       => 'nullable|string',
            'name'        => 'nullable|string|max:255',
            'designation' => 'nullable|string|max:255',
            'is_active'   => 'nullable|boolean',
            'sort_order'  => 'nullable|integer|min:0',
        ]);

        if ($request->user() && $request->user()->role === 'board_admin') {
            if ($boardMember->board_key !== $request->user()->board_key) {
                return response()->json(['message' => 'You can only manage your own board members.'], 403);
            }

            unset($validated['board_key']);
        }

        $boardMember->update($validated);
        return response()->json($boardMember->fresh());
    }

    public function destroy(BoardMember $boardMember)
    {
        if (request()->user() && request()->user()->role === 'board_admin' && $boardMember->board_key !== request()->user()->board_key) {
            return response()->json(['message' => 'You can only manage your own board members.'], 403);
        }

        $boardMember->delete();
        return response()->json(null, 204);
    }
}
