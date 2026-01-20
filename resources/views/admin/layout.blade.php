<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'AneWP - Admin Dashboard')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="bg-gray-900 text-gray-100">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <aside class="w-20 bg-gray-800 border-r border-gray-700 flex flex-col items-center py-4">
            <!-- Logo -->
            <div class="mb-8">
                <div class="w-12 h-12 bg-gradient-to-br from-red-600 to-pink-600 rounded-2xl flex items-center justify-center">
                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
            </div>

            <!-- Menu Items -->
            <nav class="flex-1 flex flex-col items-center gap-2 w-full px-2">
                <!-- Dashboard -->
                <a href="{{ route('admin.dashboard') }}"
                   class="w-14 h-14 rounded-2xl flex flex-col items-center justify-center gap-1 hover:bg-gray-700 transition group {{ request()->routeIs('admin.dashboard') ? 'bg-gray-700' : '' }}"
                   title="Dashboard">
                    <x-heroicon-o-home class="w-6 h-6 text-gray-300" />
                    <span class="text-xs text-gray-400">Dashboard</span>
                </a>

                <!-- API Keys -->
                <a href="{{ route('admin.api-keys.index') }}"
                   class="w-14 h-14 rounded-2xl flex flex-col items-center justify-center gap-1 hover:bg-gray-700 transition group {{ request()->routeIs('admin.api-keys.*') ? 'bg-gray-700' : '' }}"
                   title="API Keys">
                    <x-heroicon-o-key class="w-6 h-6 text-gray-300" />
                    <span class="text-xs text-gray-400">API Keys</span>
                </a>

                <!-- OAuth Services -->
                <a href="{{ route('admin.oauth-services.index') }}"
                   class="w-14 h-14 rounded-2xl flex flex-col items-center justify-center gap-1 hover:bg-gray-700 transition group {{ request()->routeIs('admin.oauth-services.*') ? 'bg-gray-700' : '' }}"
                   title="OAuth Services">
                    <x-heroicon-o-shield-check class="w-6 h-6 text-gray-300" />
                    <span class="text-xs text-gray-400">OAuth</span>
                </a>

                <!-- General Settings -->
                <a href="{{ route('admin.settings.index') }}"
                   class="w-14 h-14 rounded-2xl flex flex-col items-center justify-center gap-1 hover:bg-gray-700 transition group {{ request()->routeIs('admin.settings.*') ? 'bg-gray-700' : '' }}"
                   title="General Settings">
                    <x-heroicon-o-cog-6-tooth class="w-6 h-6 text-gray-300" />
                    <span class="text-xs text-gray-400">Settings</span>
                </a>

                <!-- Back to User -->
                <a href="{{ route('dashboard') }}"
                   class="w-14 h-14 rounded-2xl flex flex-col items-center justify-center gap-1 hover:bg-gray-700 transition group mt-4"
                   title="Back to User Dashboard">
                    <x-heroicon-o-arrow-left class="w-6 h-6 text-gray-300" />
                    <span class="text-xs text-gray-400">Back</span>
                </a>
            </nav>

            <!-- User Profile -->
            <div class="mt-auto pt-4 border-t border-gray-700 w-full flex justify-center">
                <div class="relative group">
                    <img src="https://www.gravatar.com/avatar/{{ md5(strtolower(trim(auth()->user()->email))) }}?s=48&d=mp"
                         alt="{{ auth()->user()->name }}"
                         class="w-10 h-10 rounded-full cursor-pointer ring-2 ring-gray-700"
                         onclick="toggleSubmenu(event, 'accountSubmenu')">

                    <!-- Account Submenu -->
                    <div id="accountSubmenu" class="hidden absolute bottom-0 left-full ml-2 w-64 bg-gray-800 rounded-2xl shadow-lg border border-gray-700 p-4 z-50">
                        <div class="flex items-center gap-3 mb-3 pb-3 border-b border-gray-700">
                            <img src="https://www.gravatar.com/avatar/{{ md5(strtolower(trim(auth()->user()->email))) }}?s=40&d=mp"
                                 alt="{{ auth()->user()->name }}"
                                 class="w-10 h-10 rounded-full">
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-100 truncate">{{ auth()->user()->name }}</p>
                                <p class="text-xs text-gray-400">Super Admin</p>
                            </div>
                        </div>

                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit"
                                    class="w-full flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-red-900 transition text-sm text-red-400">
                                <x-heroicon-o-arrow-right-on-rectangle class="w-4 h-4" />
                                Logout
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Overlay untuk close submenu -->
        <div id="submenuOverlay" class="hidden fixed inset-0 z-40" onclick="closeAllSubmenus()"></div>

        <!-- Main Content -->
        <div class="flex-1 overflow-auto">
            <div class="p-8">
                @if (session('success'))
                    <div class="mb-6 p-4 bg-green-900 border border-green-600 rounded-lg text-green-200">
                        {{ session('success') }}
                    </div>
                @endif

                @if (session('error'))
                    <div class="mb-6 p-4 bg-red-900 border border-red-600 rounded-lg text-red-200">
                        {{ session('error') }}
                    </div>
                @endif

                @yield('content')
            </div>
        </div>
    </div>

    @stack('scripts')

    <script>
        function toggleSubmenu(event, submenuId) {
            event.stopPropagation();

            // Close all other submenus
            document.querySelectorAll('[id$="Submenu"]').forEach(menu => {
                if (menu.id !== submenuId) {
                    menu.classList.add('hidden');
                }
            });

            // Toggle current submenu
            const submenu = document.getElementById(submenuId);
            const overlay = document.getElementById('submenuOverlay');

            if (submenu.classList.contains('hidden')) {
                submenu.classList.remove('hidden');
                overlay.classList.remove('hidden');
            } else {
                submenu.classList.add('hidden');
                overlay.classList.add('hidden');
            }
        }

        function closeAllSubmenus() {
            document.querySelectorAll('[id$="Submenu"]').forEach(menu => {
                menu.classList.add('hidden');
            });
            document.getElementById('submenuOverlay').classList.add('hidden');
        }

        // Close submenu when clicking outside
        document.addEventListener('click', function(event) {
            const isSubmenu = event.target.closest('[id$="Submenu"]');
            const isButton = event.target.closest('[onclick*="toggleSubmenu"]');

            if (!isSubmenu && !isButton) {
                closeAllSubmenus();
            }
        });
    </script>
</body>
</html>
