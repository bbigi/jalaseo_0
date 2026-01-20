<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class EmailVerificationController extends Controller
{
    /**
     * Show verification notice
     */
    public function notice()
    {
        return view('auth.verify-email');
    }

    /**
     * Handle email verification via signed URL
     */
    public function verify(Request $request, $id, $hash)
    {
        $user = User::findOrFail($id);

        // Verify the hash matches
        if (!hash_equals($hash, hash('sha256', $user->email))) {
            abort(403, 'Invalid verification link');
        }

        // Mark email as verified
        if ($user->hasVerifiedEmail()) {
            return redirect('/dashboard')
                ->with('status', 'Email already verified!');
        }

        $user->markEmailAsVerified();

        return redirect('/dashboard')
            ->with('status', 'Email verified successfully!');
    }

    /**
     * Resend verification email
     */
    public function resend(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect('/dashboard');
        }

        $request->user()->sendEmailVerificationNotification();

        return back()->with('status', 'Verification link sent!');
    }
}
