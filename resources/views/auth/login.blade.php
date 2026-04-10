@extends('layouts.auth')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-100 p-4 relative overflow-hidden">
    
    <div class="absolute top-[-10%] left-[-10%] w-96 h-96 bg-blue-400 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-blob"></div>
    <div class="absolute bottom-[-10%] right-[-10%] w-96 h-96 bg-purple-400 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-blob animation-delay-2000"></div>
    <div class="absolute top-[20%] right-[20%] w-72 h-72 bg-pink-400 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-blob animation-delay-4000"></div>

    <div class="relative z-10 w-full max-w-[1000px] bg-white rounded-2xl shadow-2xl overflow-hidden flex flex-col md:flex-row">
        
        <div class="md:w-5/12 bg-slate-900 p-8 flex flex-col justify-between relative overflow-hidden text-white">
            <div class="absolute inset-0 bg-gradient-to-br from-blue-600/20 to-purple-600/20 z-0"></div>
            
            <div class="relative z-10">
                <h1 class="text-3xl font-bold tracking-tight text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-purple-400">
                    AneWP
                </h1>
                <p class="text-slate-400 text-sm mt-1">Content Manager AI</p>
            </div>

            <div class="relative z-10 my-8 md:my-0">
                <h2 class="text-2xl md:text-3xl font-bold leading-tight mb-4">
                    Manage Less,<br>
                    <span class="text-blue-400">Create More.</span>
                </h2>
                <p class="text-slate-400 text-sm leading-relaxed">
                    Access your dashboard to control AI content generation across all your WordPress sites.
                </p>
            </div>

            <div class="relative z-10 flex gap-4 pt-4 border-t border-white/10">
                <div class="text-center">
                    <div class="w-10 h-10 rounded-lg bg-white/10 flex items-center justify-center mb-2 mx-auto">
                        <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                    </div>
                    <span class="text-[10px] uppercase tracking-wider text-slate-400">Fast</span>
                </div>
                <div class="text-center">
                    <div class="w-10 h-10 rounded-lg bg-white/10 flex items-center justify-center mb-2 mx-auto">
                        <svg class="w-5 h-5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.384-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path></svg>
                    </div>
                    <span class="text-[10px] uppercase tracking-wider text-slate-400">AI</span>
                </div>
                <div class="text-center">
                    <div class="w-10 h-10 rounded-lg bg-white/10 flex items-center justify-center mb-2 mx-auto">
                        <svg class="w-5 h-5 text-pink-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                    </div>
                    <span class="text-[10px] uppercase tracking-wider text-slate-400">Secure</span>
                </div>
            </div>
        </div>

        <div class="md:w-7/12 p-8 md:p-12 bg-white flex flex-col justify-center">
            
            <div class="mb-8">
                <h3 class="text-2xl font-bold text-gray-900">Welcome Back! 👋</h3>
                <p class="text-gray-500 text-sm mt-1">Please enter your details to sign in.</p>
            </div>

            @if ($errors->any())
                <div class="mb-6 p-4 rounded-lg bg-red-50 border-l-4 border-red-500 flex items-start">
                    <svg class="h-5 w-5 text-red-400 mt-0.5 mr-3" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                    </svg>
                    <div>
                        <h3 class="text-sm font-medium text-red-800">Login Failed</h3>
                        <ul class="mt-1 list-disc list-inside text-sm text-red-700">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="space-y-6">
                @csrf

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" /></svg>
                        </div>
                        <input 
                            id="email" 
                            type="email" 
                            name="email" 
                            value="{{ old('email') }}" 
                            required 
                            autofocus
                            class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition text-gray-900 placeholder-gray-400"
                            placeholder="you@example.com"
                        >
                    </div>
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                        </div>
                        <input 
                            id="password" 
                            type="password" 
                            name="password" 
                            required 
                            class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition text-gray-900 placeholder-gray-400"
                            placeholder="••••••••"
                        >
                    </div>
                </div>

                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input 
                            id="remember" 
                            name="remember" 
                            type="checkbox" 
                            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded cursor-pointer"
                        >
                        <label for="remember" class="ml-2 block text-sm text-gray-600 cursor-pointer">Remember me</label>
                    </div>
                    <div class="text-sm">
                        <a href="#" class="font-medium text-blue-600 hover:text-blue-500 hover:underline">Forgot Password?</a>
                    </div>
                </div>

                <button 
                    type="submit" 
                    class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-lg text-sm font-bold text-white bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all transform hover:-translate-y-0.5 duration-200"
                >
                    Sign In
                </button>
            </form>

            <div class="mt-8 text-center">
                <p class="text-sm text-gray-600">
                    Don't have an account yet? 
                    <a href="{{ route('register') }}" class="font-semibold text-blue-600 hover:text-blue-500 hover:underline">
                        Create an account
                    </a>
                </p>
            </div>
        </div>
    </div>
</div>

<style>
    @keyframes blob {
        0% { transform: translate(0px, 0px) scale(1); }
        33% { transform: translate(30px, -50px) scale(1.1); }
        66% { transform: translate(-20px, 20px) scale(0.9); }
        100% { transform: translate(0px, 0px) scale(1); }
    }
    .animate-blob {
        animation: blob 7s infinite;
    }
    .animation-delay-2000 {
        animation-delay: 2s;
    }
    .animation-delay-4000 {
        animation-delay: 4s;
    }
</style>
@endsection