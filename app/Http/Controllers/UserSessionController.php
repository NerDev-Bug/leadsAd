<?php

namespace App\Http\Controllers;

use App\Models\UserSession;
use App\Services\UserSessionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserSessionController extends Controller
{
    public function index(Request $request)
    {
        $currentSessionId = $request->session()->getId();

        $sessions = UserSession::query()
            ->where('access_register_id', $request->user()->id)
            ->whereNull('logged_out_at')
            ->orderByDesc('last_activity')
            ->get()
            ->map(fn (UserSession $session) => UserSessionService::serialize($session, $currentSessionId));

        return response()->json(['sessions' => $sessions]);
    }

    public function destroy(Request $request, UserSession $userSession)
    {
        if ($userSession->access_register_id !== $request->user()->id) {
            abort(403);
        }

        if ($userSession->logged_out_at !== null) {
            return response()->json(['success' => true]);
        }

        if ($userSession->session_id === $request->session()->getId()) {
            UserSessionService::logoutCurrent($request);
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login');
        }

        UserSessionService::revoke($userSession);

        return response()->json(['success' => true]);
    }

    public function logoutAll(Request $request)
    {
        $user = $request->user();
        UserSessionService::logoutAllForUser($user);

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
