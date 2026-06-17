<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminUserController extends Controller
{
    private function authorizeSuperAdmin(Request $request)
    {
        if (!$request->user() || $request->user()->role !== 'super_admin') {
            abort(response()->json(['message' => 'Only the main admin can manage admin accounts.'], 403));
        }
    }

    public function index(Request $request)
    {
        $this->authorizeSuperAdmin($request);

        return response()->json(
            User::orderBy('role')
                ->orderBy('board_key')
                ->get(['id', 'name', 'email', 'role', 'board_key', 'updated_at'])
        );
    }

    public function update(Request $request, User $user)
    {
        $this->authorizeSuperAdmin($request);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('users', 'name')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8'],
        ]);

        $user->name = $validated['name'];

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
            $user->api_token = null;
        }

        $user->save();

        return response()->json($user->only(['id', 'name', 'email', 'role', 'board_key', 'updated_at']));
    }
}
