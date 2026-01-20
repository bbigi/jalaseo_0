<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class RegisterController extends Controller
{
    /**
     * Show registration form
     */
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    /**
     * Handle registration
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:ane_users,email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // Create user
        $user = User::create([
            'username' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'author',
            'subscription_plan' => 'free',
            'subscription_status' => 'inactive',
            'api_quota_limit' => 100, // Free plan: 100 API calls/month
        ]);

        // Fire registered event (sends verification email)
        event(new Registered($user));

        // Log user in
        auth()->login($user);

        return redirect('/email-verification-notice')
            ->with('status', 'Registration successful! Please verify your email.');
    }
}
