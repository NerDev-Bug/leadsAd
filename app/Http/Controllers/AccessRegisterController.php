<?php

namespace App\Http\Controllers;

use App\Models\AccessRegister;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

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
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                'unique:access_registers,email',
                'email:rfc,dns',
                'not_regex:/@12\\.com$/i', // Restrict emails ending with @12.com
            ],
            'password' => 'required|confirmed|min:8',
        ], [
            'email.not_regex' => 'Registration using @12.com emails is not allowed.',
        ]);

        AccessRegister::create([
            'username' => $validated['name'],
            'email' => $validated['email'],
            'password' => bcrypt($validated['password']),
        ]);

        return redirect()->back()->with('success', 'Registration successful!');
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

        $user = AccessRegister::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return back()->withErrors([
                'general' => 'The provided credentials are incorrect.',
            ])->onlyInput('email');
        }

        Auth::login($user);
        $request->session()->regenerate(); // ✅ important

        // Return a 204 No Content response for Inertia
        return redirect()->route('dashboard');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}
