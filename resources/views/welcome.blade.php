<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Jalaseo</title>

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Space+Grotesk:wght@500;700&display=swap" rel="stylesheet">
        <link rel="icon" href="{{ asset('images/favicon1.jpg') }}" type="image/x-icon">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @else
            <script src="https://cdn.tailwindcss.com"></script>
            <script>
                tailwind.config = {
                    darkMode: 'class',
                    theme: {
                        extend: {
                            fontFamily: {
                                sans: ['Inter', 'sans-serif'],
                                display: ['Space Grotesk', 'sans-serif'],
                            },
                            colors: {
                                brand: {
                                    50: '#f0fdfa',
                                    100: '#ccfbf1',
                                    500: '#14b8a6', // Teal
                                    600: '#0d9488',
                                    900: '#134e4a',
                                },
                                accent: {
                                    500: '#8b5cf6', // Violet
                                    600: '#7c3aed',
                                }
                            },
                            animation: {
                                'float': 'float 6s ease-in-out infinite',
                                'pulse-slow': 'pulse 4s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                                'blob': 'blob 7s infinite',
                                'slide-left': 'slideLeft 20s linear infinite',
                                'bar-grow': 'barGrow 2s ease-out infinite alternate',
                            },
                            keyframes: {
                                float: {
                                    '0%, 100%': { transform: 'translateY(0)' },
                                    '50%': { transform: 'translateY(-20px)' },
                                },
                                blob: {
                                    '0%': { transform: 'translate(0px, 0px) scale(1)' },
                                    '33%': { transform: 'translate(30px, -50px) scale(1.1)' },
                                    '66%': { transform: 'translate(-20px, 20px) scale(0.9)' },
                                    '100%': { transform: 'translate(0px, 0px) scale(1)' },
                                },
                                slideLeft: {
                                    '0%': { transform: 'translateX(0)' },
                                    '100%': { transform: 'translateX(-50%)' },
                                },
                                barGrow: {
                                    '0%': { height: '20%' },
                                    '100%': { height: '80%' },
                                }
                            }
                        }
                    }
                }
            </script>
            <style>
                /* Custom Glassmorphism & Utilities */
                body { background-color: #0f172a; color: #f8fafc; overflow-x: hidden; }
                .glass {
                    background: rgba(255, 255, 255, 0.05);
                    backdrop-filter: blur(10px);
                    border: 1px solid rgba(255, 255, 255, 0.1);
                }
                .glass-card:hover {
                    background: rgba(255, 255, 255, 0.1);
                    border: 1px solid rgba(139, 92, 246, 0.5);
                    transform: translateY(-5px);
                    box-shadow: 0 10px 30px -10px rgba(139, 92, 246, 0.3);
                }
                .text-gradient {
                    background: linear-gradient(to right, #2dd4bf, #a78bfa, #f472b6);
                    -webkit-background-clip: text;
                    -webkit-text-fill-color: transparent;
                }
                .bg-mesh {
                    background-image: 
                        radial-gradient(at 0% 0%, rgba(45, 212, 191, 0.15) 0px, transparent 50%),
                        radial-gradient(at 100% 0%, rgba(167, 139, 250, 0.15) 0px, transparent 50%),
                        radial-gradient(at 100% 100%, rgba(244, 114, 182, 0.15) 0px, transparent 50%);
                }
                /* Hide scrollbar for ticker */
                .no-scrollbar::-webkit-scrollbar { display: none; }
                .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
            </style>
        @endif
    </head>
    <body class="antialiased bg-slate-900 text-white selection:bg-brand-500 selection:text-white">

        <div class="fixed inset-0 pointer-events-none z-0 overflow-hidden">
            <div class="absolute top-0 left-1/4 w-96 h-96 bg-brand-500 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-blob"></div>
            <div class="absolute top-0 right-1/4 w-96 h-96 bg-accent-500 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-blob animation-delay-2000"></div>
            <div class="absolute -bottom-32 left-1/3 w-96 h-96 bg-pink-500 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-blob animation-delay-4000"></div>
        </div>

        <nav class="fixed w-full z-50 glass border-b border-white/10 transition-all duration-300" id="navbar">
            <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
                <div class="flex items-center gap-2 group cursor-pointer">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-brand-500 to-accent-600 flex items-center justify-center text-white font-bold text-xl shadow-lg group-hover:rotate-12 transition-transform">
                        J
                    </div>
                    <span class="font-display font-bold text-2xl tracking-tight">Jalaseo</span>
                </div>

                @if (Route::has('login'))
                    <div class="flex items-center gap-6">
                        @auth
                            <a href="{{ url('/dashboard') }}" class="px-6 py-2.5 rounded-full font-medium bg-white/10 hover:bg-white/20 transition border border-white/10">
                                Dashboard
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="hidden md:block text-slate-300 hover:text-white transition font-medium">Log in</a>
                            
                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" class="px-6 py-2.5 rounded-full font-bold bg-gradient-to-r from-brand-500 to-accent-600 hover:from-brand-400 hover:to-accent-500 text-white shadow-lg shadow-brand-500/25 transition transform hover:scale-105">
                                    Get Started
                                </a>
                            @endif
                        @endauth
                    </div>
                @endif
            </div>
        </nav>

        <section class="relative z-10 pt-32 pb-20 lg:pt-48 lg:pb-32 px-6">
            <div class="max-w-7xl mx-auto grid lg:grid-cols-2 gap-12 items-center">
                
                <div class="text-center lg:text-left">
                    <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full glass border border-brand-500/30 text-brand-300 text-sm font-semibold mb-6 animate-float">
                        <span class="relative flex h-3 w-3">
                          <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-brand-400 opacity-75"></span>
                          <span class="relative inline-flex rounded-full h-3 w-3 bg-brand-500"></span>
                        </span>
                        AI-Powered Trend Automation
                    </div>
                    
                    <h1 class="font-display text-5xl lg:text-7xl font-bold leading-tight mb-6">
                        Dominate SEO with <br>
                        <span class="text-gradient">Automated Intelligence</span>
                    </h1>
                    
                    <p class="text-lg text-slate-400 mb-8 leading-relaxed max-w-2xl mx-auto lg:mx-0">
                        Jalaseo bukan sekadar tools. Ini adalah mesin otonom yang memantau tren, membuat konten artikel & gambar, lalu mempostingnya ke seluruh aset digital Anda secara otomatis.
                    </p>
                    
                    <div class="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start">
                        <a href="{{ route('register') }}" class="px-8 py-4 rounded-xl font-bold bg-white text-slate-900 hover:bg-slate-200 transition shadow-xl flex items-center justify-center gap-2 group">
                            Coba Gratis Sekarang
                            <i class="fa-solid fa-arrow-right group-hover:translate-x-1 transition-transform"></i>
                        </a>
                        <a href="#features" class="px-8 py-4 rounded-xl font-bold glass hover:bg-white/10 transition flex items-center justify-center gap-2">
                            <i class="fa-solid fa-play text-brand-400"></i> Demo Video
                        </a>
                    </div>
                </div>

                <div class="relative group perspective-1000">
                    <div class="absolute -inset-1 bg-gradient-to-r from-brand-500 to-accent-600 rounded-2xl blur opacity-30 group-hover:opacity-70 transition duration-1000"></div>
                    
                    <div class="relative bg-slate-900 border border-white/10 rounded-2xl p-4 shadow-2xl transform rotate-y-12 rotate-x-6 group-hover:rotate-0 transition duration-700 ease-out">
                        <div class="flex items-center justify-between mb-4 border-b border-white/5 pb-4">
                            <div class="flex gap-2">
                                <div class="w-3 h-3 rounded-full bg-red-500"></div>
                                <div class="w-3 h-3 rounded-full bg-yellow-500"></div>
                                <div class="w-3 h-3 rounded-full bg-green-500"></div>
                            </div>
                            <div class="h-2 w-20 bg-slate-700 rounded-full"></div>
                        </div>

                        <div class="grid grid-cols-3 gap-4">
                            <div class="col-span-1 space-y-3">
                                <div class="h-8 bg-slate-800 rounded-lg w-full animate-pulse"></div>
                                <div class="h-8 bg-slate-800/50 rounded-lg w-full"></div>
                                <div class="h-8 bg-slate-800/50 rounded-lg w-full"></div>
                                
                                <div class="mt-8 bg-slate-800 rounded-lg p-3 h-32 flex items-end justify-between gap-1">
                                    <div class="w-full bg-brand-500/50 rounded-t animate-bar-grow" style="animation-delay: 0s"></div>
                                    <div class="w-full bg-brand-500/70 rounded-t animate-bar-grow" style="animation-delay: 0.2s"></div>
                                    <div class="w-full bg-accent-500/60 rounded-t animate-bar-grow" style="animation-delay: 0.4s"></div>
                                    <div class="w-full bg-accent-500 rounded-t animate-bar-grow" style="animation-delay: 0.6s"></div>
                                </div>
                                <p class="text-xs text-center mt-2 text-brand-300 font-mono">Trend Detected!</p>
                            </div>

                            <div class="col-span-2 space-y-4">
                                <div class="bg-slate-800/50 rounded-xl p-4 border border-brand-500/20">
                                    <div class="flex items-center gap-3 mb-2">
                                        <div class="w-8 h-8 rounded-full bg-brand-500 flex items-center justify-center">
                                            <i class="fa-solid fa-robot text-white text-xs"></i>
                                        </div>
                                        <div class="text-xs text-slate-300">
                                            Creating Content for: <span class="text-brand-400 font-bold">"AI Trends 2026"</span>
                                        </div>
                                    </div>
                                    <div class="space-y-2">
                                        <div class="h-2 bg-slate-700 rounded w-full"></div>
                                        <div class="h-2 bg-slate-700 rounded w-5/6"></div>
                                        <div class="h-2 bg-slate-700 rounded w-4/6"></div>
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-3">
                                    <div class="bg-slate-800 rounded-lg aspect-square flex items-center justify-center relative overflow-hidden">
                                        <div class="absolute inset-0 bg-gradient-to-br from-indigo-500 to-pink-500 opacity-50"></div>
                                        <i class="fa-brands fa-instagram text-4xl text-white relative z-10"></i>
                                        <div class="absolute bottom-2 right-2 text-[10px] bg-black/50 px-2 py-0.5 rounded text-white backdrop-blur">
                                            Scheduled 10:00 AM
                                        </div>
                                    </div>
                                    <div class="bg-slate-800 rounded-lg aspect-square flex items-center justify-center relative">
                                        <i class="fa-brands fa-wordpress text-4xl text-blue-400"></i>
                                         <div class="absolute bottom-2 right-2 text-[10px] bg-green-500/20 text-green-400 px-2 py-0.5 rounded border border-green-500/30">
                                            Published
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <div class="relative bg-slate-950 border-y border-white/5 py-4 overflow-hidden transform -skew-y-2">
            <div class="absolute inset-y-0 left-0 w-32 bg-gradient-to-r from-slate-950 to-transparent z-10"></div>
            <div class="absolute inset-y-0 right-0 w-32 bg-gradient-to-l from-slate-950 to-transparent z-10"></div>
            
            <div class="flex whitespace-nowrap animate-slide-left gap-8 items-center">
                @for ($i = 0; $i < 2; $i++)
                    <div class="flex gap-8 items-center text-slate-400 font-mono text-sm uppercase tracking-widest">
                        <span class="text-brand-400"><i class="fa-solid fa-arrow-trend-up"></i> Trending Now:</span>
                        <span class="px-3 py-1 border border-white/10 rounded bg-white/5 text-white">#ArtificialIntelligence</span>
                        <span>•</span>
                        <span class="px-3 py-1 border border-white/10 rounded bg-white/5 text-white">#CryptoBullRun</span>
                        <span>•</span>
                        <span class="px-3 py-1 border border-white/10 rounded bg-white/5 text-white">#SaaSMarketing</span>
                        <span>•</span>
                        <span class="px-3 py-1 border border-white/10 rounded bg-white/5 text-white">#Laravel12</span>
                        <span>•</span>
                        <span class="px-3 py-1 border border-white/10 rounded bg-white/5 text-white">#WebDev</span>
                        <span>•</span>
                         <span class="px-3 py-1 border border-white/10 rounded bg-white/5 text-white">#SustainableTech</span>
                        <span>•</span>
                        <span class="px-3 py-1 border border-white/10 rounded bg-white/5 text-white">#DigitalNomad</span>
                         <span>•</span>
                    </div>
                @endfor
            </div>
        </div>

        <section id="features" class="py-24 relative z-10 bg-mesh">
            <div class="max-w-7xl mx-auto px-6">
                <div class="text-center mb-16">
                    <h2 class="font-display text-4xl font-bold mb-4">Fitur Cerdas untuk <span class="text-brand-400">Pertumbuhan Otomatis</span></h2>
                    <p class="text-slate-400 max-w-2xl mx-auto">Sistem yang bekerja 24/7 menggantikan tim konten Anda. Lebih cepat, lebih murah, dan berbasis data.</p>
                </div>

                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div class="glass-card p-8 rounded-2xl transition duration-300">
                        <div class="w-14 h-14 rounded-xl bg-blue-500/20 flex items-center justify-center text-blue-400 text-2xl mb-6">
                            <i class="fa-solid fa-chart-line"></i>
                        </div>
                        <h3 class="font-display text-xl font-bold mb-3">Analisis Tren Real-time</h3>
                        <p class="text-slate-400 text-sm leading-relaxed">
                            Algoritma kami memindai Google Trends & Social Signals untuk menemukan topik panas sebelum kompetitor Anda menyadarinya.
                        </p>
                    </div>

                    <div class="glass-card p-8 rounded-2xl transition duration-300">
                        <div class="w-14 h-14 rounded-xl bg-purple-500/20 flex items-center justify-center text-purple-400 text-2xl mb-6">
                            <i class="fa-solid fa-wand-magic-sparkles"></i>
                        </div>
                        <h3 class="font-display text-xl font-bold mb-3">AI Image & Caption</h3>
                        <p class="text-slate-400 text-sm leading-relaxed">
                            Generate gambar visual HD dan caption copywriting yang persuasif dalam hitungan detik. Tidak perlu skill desain.
                        </p>
                    </div>

                    <div class="glass-card p-8 rounded-2xl transition duration-300">
                        <div class="w-14 h-14 rounded-xl bg-pink-500/20 flex items-center justify-center text-pink-400 text-2xl mb-6">
                            <i class="fa-solid fa-rocket"></i>
                        </div>
                        <h3 class="font-display text-xl font-bold mb-3">Multi-Platform Posting</h3>
                        <p class="text-slate-400 text-sm leading-relaxed">
                            Sekali klik, konten terkirim ke WordPress, Instagram, dan Facebook Fanpage Anda secara bersamaan.
                        </p>
                    </div>

                    <div class="glass-card p-8 rounded-2xl transition duration-300">
                        <div class="w-14 h-14 rounded-xl bg-orange-500/20 flex items-center justify-center text-orange-400 text-2xl mb-6">
                            <i class="fa-regular fa-calendar-check"></i>
                        </div>
                        <h3 class="font-display text-xl font-bold mb-3">Smart Scheduling</h3>
                        <p class="text-slate-400 text-sm leading-relaxed">
                            Jadwalkan kampanye sebulan penuh. Sistem akan memilih waktu posting terbaik (prime time) untuk engagement maksimal.
                        </p>
                    </div>

                    <div class="glass-card p-8 rounded-2xl transition duration-300">
                        <div class="w-14 h-14 rounded-xl bg-teal-500/20 flex items-center justify-center text-teal-400 text-2xl mb-6">
                            <i class="fa-solid fa-link"></i>
                        </div>
                        <h3 class="font-display text-xl font-bold mb-3">Seamless Integration</h3>
                        <p class="text-slate-400 text-sm leading-relaxed">
                            Cukup tautkan API Website dan akun Sosmed sekali saja. Dashboard Jalaseo yang user-friendly (No-Code) akan menangani sisanya.
                        </p>
                    </div>

                     <div class="glass-card p-8 rounded-2xl transition duration-300">
                        <div class="w-14 h-14 rounded-xl bg-yellow-500/20 flex items-center justify-center text-yellow-400 text-2xl mb-6">
                            <i class="fa-solid fa-magnifying-glass-chart"></i>
                        </div>
                        <h3 class="font-display text-xl font-bold mb-3">Performance Tracker</h3>
                        <p class="text-slate-400 text-sm leading-relaxed">
                            Lihat artikel mana yang ranking dan postingan mana yang viral langsung dari satu dashboard terpusat.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <section class="py-20 border-t border-white/5">
            <div class="max-w-7xl mx-auto px-6">
                 <div class="grid lg:grid-cols-2 gap-16 items-center">
                    <div class="relative">
                        <div class="absolute left-8 top-8 bottom-8 w-0.5 bg-gradient-to-b from-brand-500 to-accent-600 opacity-30"></div>
                        
                        <div class="space-y-12">
                            <div class="relative flex gap-6 group">
                                <div class="w-16 h-16 rounded-full bg-slate-800 border-2 border-brand-500 flex items-center justify-center text-xl font-bold z-10 shadow-lg shadow-brand-500/20 group-hover:scale-110 transition">1</div>
                                <div>
                                    <h3 class="text-xl font-bold text-white mb-2">Connect Your Accounts</h3>
                                    <p class="text-slate-400">Hubungkan WordPress, Instagram Business, dan Facebook Page Anda dalam hitungan detik.</p>
                                </div>
                            </div>
                            <div class="relative flex gap-6 group">
                                <div class="w-16 h-16 rounded-full bg-slate-800 border-2 border-white/20 group-hover:border-accent-500 flex items-center justify-center text-xl font-bold z-10 transition">2</div>
                                <div>
                                    <h3 class="text-xl font-bold text-white mb-2">Select Trends & Topics</h3>
                                    <p class="text-slate-400">Pilih topik yang disarankan AI berdasarkan data tren real-time atau input manual.</p>
                                </div>
                            </div>
                            <div class="relative flex gap-6 group">
                                <div class="w-16 h-16 rounded-full bg-slate-800 border-2 border-white/20 group-hover:border-pink-500 flex items-center justify-center text-xl font-bold z-10 transition">3</div>
                                <div>
                                    <h3 class="text-xl font-bold text-white mb-2">Automate & Relax</h3>
                                    <p class="text-slate-400">Jalaseo akan membuat konten, mengedit gambar, dan mempostingnya sesuai jadwal Anda.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="relative rounded-2xl overflow-hidden border border-white/10 shadow-2xl">
                         <div class="absolute inset-0 bg-brand-500/10 mix-blend-overlay z-10"></div>
                         <div class="bg-slate-900 p-8 h-full flex flex-col justify-center items-center text-center">
                            <div class="w-24 h-24 bg-gradient-to-tr from-brand-400 to-accent-500 rounded-full animate-pulse-slow flex items-center justify-center mb-6 blur-sm absolute"></div>
                            <div class="w-24 h-24 bg-gradient-to-tr from-brand-400 to-accent-500 rounded-full animate-pulse-slow flex items-center justify-center mb-6 relative z-10 shadow-xl">
                                <i class="fa-solid fa-bolt text-4xl text-white"></i>
                            </div>
                            <h3 class="text-2xl font-bold mb-2">System Active</h3>
                            <p class="text-brand-300 font-mono">Processing 1,240 keywords...</p>
                         </div>
                    </div>
                 </div>
            </div>
        </section>

        <section class="py-20 px-6">
            <div class="max-w-5xl mx-auto relative rounded-3xl overflow-hidden p-12 lg:p-20 text-center">
                <div class="absolute inset-0 bg-gradient-to-r from-brand-600 to-accent-700"></div>
                <div class="absolute inset-0 bg-mesh opacity-50"></div>
                
                <div class="relative z-10">
                    <h2 class="font-display text-4xl lg:text-5xl font-bold mb-6">Stop Wasting Time on Content</h2>
                    <p class="text-white/80 text-lg mb-10 max-w-2xl mx-auto">Bergabunglah dengan kreator dan pebisnis cerdas yang menggunakan Jalaseo untuk mendominasi pasar digital secara otomatis.</p>
                    
                    <a href="{{ route('register') }}" class="inline-block px-10 py-5 rounded-full bg-white text-brand-600 font-bold text-xl hover:bg-slate-100 transition shadow-xl transform hover:scale-105">
                        Start Free Trial
                    </a>
                    <p class="mt-6 text-sm text-white/60">No credit card required • Cancel anytime</p>
                </div>
            </div>
        </section>

        <footer class="border-t border-white/10 bg-slate-950 pt-16 pb-8">
            <div class="max-w-7xl mx-auto px-6">
                <div class="flex flex-col md:flex-row justify-between items-center gap-8 mb-12">
                    <div class="flex items-center gap-2">
                         <div class="w-8 h-8 rounded-lg bg-gradient-to-tr from-brand-500 to-accent-600 flex items-center justify-center text-white font-bold shadow-lg">J</div>
                         <span class="font-display font-bold text-xl">Jalaseo</span>
                    </div>
                    <div class="flex gap-8 text-slate-400 text-sm">
                        <a href="#" class="hover:text-white transition">Features</a>
                        <a href="#" class="hover:text-white transition">Pricing</a>
                        <a href="#" class="hover:text-white transition">Blog</a>
                        <a href="#" class="hover:text-white transition">Login</a>
                    </div>
                    <div class="flex gap-4">
                        <a href="#" class="w-10 h-10 rounded-full glass flex items-center justify-center text-slate-400 hover:text-white hover:bg-brand-500 transition"><i class="fa-brands fa-twitter"></i></a>
                        <a href="#" class="w-10 h-10 rounded-full glass flex items-center justify-center text-slate-400 hover:text-white hover:bg-pink-600 transition"><i class="fa-brands fa-instagram"></i></a>
                        <a href="#" class="w-10 h-10 rounded-full glass flex items-center justify-center text-slate-400 hover:text-white hover:bg-blue-600 transition"><i class="fa-brands fa-facebook-f"></i></a>
                    </div>
                </div>
                <div class="text-center text-slate-600 text-sm">
                    &copy; {{ date('Y') }} Jalaseo Automation. Built with <i class="fa-solid fa-heart text-red-500"></i> for Creators.
                </div>
            </div>
        </footer>

    </body>
</html>