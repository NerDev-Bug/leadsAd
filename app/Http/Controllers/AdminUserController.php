<?php

namespace App\Http\Controllers;

use App\Models\AccessRegister;
use App\Models\UserSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AdminUserController extends Controller
{
    public function index()
    {
        $users = AccessRegister::query()
            ->orderBy('username')
            ->get(['id', 'username', 'email', 'role', 'created_at', 'updated_at']);

        return response()->json([
            'users' => $users,
        ]);
    }

    public function show(AccessRegister $accessRegister)
    {
        return response()->json([
            'user' => $accessRegister->only([
                'id',
                'username',
                'email',
                'role',
                'created_at',
                'updated_at',
            ]),
        ]);
    }

    public function destroy(Request $request, AccessRegister $accessRegister)
    {
        $validated = $request->validate([
            'password' => 'required|string',
        ]);

        $admin = $request->user();

        if (! $admin || ! Hash::check($validated['password'], $admin->password)) {
            throw ValidationException::withMessages([
                'password' => 'The confirmation password is incorrect.',
            ]);
        }

        if ($accessRegister->id === $admin->id) {
            throw ValidationException::withMessages([
                'password' => 'You cannot remove your own account.',
            ]);
        }

        DB::transaction(function () use ($accessRegister) {
            $sessionIds = UserSession::query()
                ->where('access_register_id', $accessRegister->id)
                ->whereNotNull('session_id')
                ->pluck('session_id');

            UserSession::query()
                ->where('access_register_id', $accessRegister->id)
                ->delete();

            if ($sessionIds->isNotEmpty() && config('session.driver') === 'database') {
                DB::table(config('session.table', 'sessions'))
                    ->whereIn('id', $sessionIds)
                    ->delete();
            }

            $accessRegister->delete();
        });

        return response()->json([
            'success' => true,
            'message' => 'Account removed successfully.',
        ]);
    }
}
