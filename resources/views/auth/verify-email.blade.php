@extends('layouts.auth')

@section('content')
<div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0">
    <!-- Logo / Brand -->
    <div class="mb-8 text-center">
        <h1 class="text-4xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-purple-600">
            AneWP
        </h1>
        <p class="text-gray-600 mt-2">Verify Your Email</p>
    </div>

    <div class="w-full sm:max-w-md mt-6 px-6 py-8 bg-white shadow-lg rounded-lg">
        <!-- Status Messages -->
        @if (session('status') == 'verification-link-sent')
            <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg">
                <p class="text-green-700 text-sm">
                    A fresh verification link has been sent to your email address.
                </p>
            </div>
        @endif

        <!-- Verification Notice -->
        <div class="mb-6">
            <div class="flex justify-center mb-4">
                <svg class="h-12 w-12 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
            </div>
            <p class="text-center text-gray-700 font-semibold mb-2">
                Verify Your Email Address
            </p>
            <p class="text-center text-gray-600 text-sm mb-4">
                We've sent a verification link to your email. Please check your inbox and click the link to verify your account.
            </p>
        </div>

        <!-- Resend Form -->
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf

            <button
                type="submit"
                class="w-full bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white font-semibold py-2 rounded-lg transition transform hover:scale-105 duration-200"
            >
                Resend Verification Email
            </button>
        </form>

        <!-- Logout Link -->
        <div class="mt-6 text-center">
            <form method="POST" action="{{ route('logout') }}" class="inline">
                @csrf
                <button
                    type="submit"
                    class="text-blue-600 hover:underline text-sm"
                >
                    Use Different Account
                </button>
            </form>
        </div>

        <!-- Tips -->
        <div class="mt-8 p-4 bg-blue-50 rounded-lg border border-blue-200">
            <h4 class="font-semibold text-blue-900 mb-2">Didn't receive the email?</h4>
            <ul class="text-sm text-blue-800 space-y-2">
                <li>✓ Check your spam/junk folder</li>
                <li>✓ Check email forwarding rules</li>
                <li>✓ Use a different email if necessary</li>
            </ul>
        </div>
    </div>
</div>
@endsection
