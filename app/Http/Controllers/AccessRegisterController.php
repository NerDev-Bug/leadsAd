<?php

namespace App\Http\Controllers;

use App\Models\AccessRegister;
use App\Services\UserSessionService;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class AccessRegisterController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        abort(403, 'Public registration is disabled.');
    }

    /**
     * Display the specified resource.
     */
    public function show(AccessRegister $accessRegister)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(AccessRegister $accessRegister)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, AccessRegister $accessRegister)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AccessRegister $accessRegister)
    {
        //
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $this->ensureLoginIsNotRateLimited($request);

        $user = AccessRegister::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            RateLimiter::hit($this->loginThrottleKey($request));

            return back()->withErrors([
                'general' => 'The provided credentials are incorrect.',
            ])->onlyInput('email');
        }

        RateLimiter::clear($this->loginThrottleKey($request));

        Auth::login($user);
        $request->session()->regenerate();
        UserSessionService::recordLogin($user, $request);

        return redirect()->route('dashboard');
    }

    /**
     * Block further login attempts after too many failures (5 per email+IP).
     */
    protected function ensureLoginIsNotRateLimited(Request $request): void
    {
        if (! RateLimiter::tooManyAttempts($this->loginThrottleKey($request), 5)) {
            return;
        }

        event(new Lockout($request));

        $seconds = RateLimiter::availableIn($this->loginThrottleKey($request));

        throw ValidationException::withMessages([
            'general' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    protected function loginThrottleKey(Request $request): string
    {
        return Str::transliterate(Str::lower($request->string('email')).'|'.$request->ip());
    }

    public function logout(Request $request)
    {
        UserSessionService::logoutCurrent($request);
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }

    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => 'required',
            'password' => 'required|confirmed|min:8',
        ]);

        $user = $request->user();

        if (!$user || !Hash::check($validated['current_password'], $user->password)) {
            return back()->withErrors([
                'current_password' => 'The current password is incorrect.',
            ]);
        }

        $user->update([
            'password' => bcrypt($validated['password']),
        ]);

        return back();
    }
}
