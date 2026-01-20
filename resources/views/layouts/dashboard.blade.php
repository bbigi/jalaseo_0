<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'AneWP - Dashboard')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;700;900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @stack('styles')
</head>
<body class="bg-gray-50 text-gray-900">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <aside class="w-20 bg-white border-r border-gray-200 flex flex-col items-center py-4">
            <!-- Logo -->
            <div class="mb-8">
                <div class="w-12 h-12 bg-indigo-600 rounded-2xl flex items-center justify-center">
                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
            </div>

            <!-- Menu Items -->
            <nav class="flex-1 flex flex-col items-center gap-2 w-full px-2">
                <!-- Dashboard -->
                <a href="{{ route('dashboard') }}"
                   class="w-14 h-14 rounded-2xl flex flex-col items-center justify-center gap-1 hover:bg-gray-50 transition group {{ request()->routeIs('dashboard') ? 'bg-gray-100' : '' }}"
                   title="Dashboard">
                    <x-heroicon-o-home class="w-6 h-6 text-gray-600" />
                    <span class="text-xs text-gray-500">Dashboard</span>
                </a>

                <!-- Articles (dengan submenu) -->
                <div class="relative group/articles w-full flex justify-center">
                    <button
                       class="w-14 h-14 rounded-2xl flex flex-col items-center justify-center gap-1 hover:bg-gray-50 transition {{ request()->routeIs('posts.*') || request()->routeIs('generate.*') ? 'bg-gray-100' : '' }}"
                       onclick="toggleSubmenu(event, 'articlesSubmenu')">
                        <x-heroicon-o-document-text class="w-6 h-6 text-gray-600" />
                        <span class="text-xs text-gray-500">Articles</span>
                    </button>

                    <!-- Submenu -->
                    <div id="articlesSubmenu" class="hidden absolute left-full top-0 ml-2 w-72 bg-white rounded-2xl shadow-lg border border-gray-200 p-3 z-50">
                        <div class="space-y-1">
                            <a href="{{ route('posts.index') }}"
                               class="flex items-start gap-3 p-3 rounded-xl hover:bg-gray-50 transition group">
                                <div class="w-10 h-10 bg-purple-50 rounded-lg flex items-center justify-center flex-shrink-0 group-hover:bg-purple-100 transition">
                                    <x-heroicon-o-document-duplicate class="w-5 h-5 text-purple-600" />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900">All Articles</p>
                                    <p class="text-xs text-gray-500 mt-0.5">View all your articles</p>
                                </div>
                            </a>

                            <a href="{{ route('posts.create') }}"
                               class="flex items-start gap-3 p-3 rounded-xl hover:bg-gray-50 transition group">
                                <div class="w-10 h-10 bg-red-50 rounded-lg flex items-center justify-center flex-shrink-0 group-hover:bg-red-100 transition">
                                    <x-heroicon-o-pencil class="w-5 h-5 text-red-500" />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900">Create Manual Post</p>
                                    <p class="text-xs text-gray-500 mt-0.5">Write article manually</p>
                                </div>
                            </a>

                            <a href="{{ route('generate.index') }}"
                               class="flex items-start gap-3 p-3 rounded-xl hover:bg-gray-50 transition group">
                                <div class="w-10 h-10 bg-yellow-50 rounded-lg flex items-center justify-center flex-shrink-0 group-hover:bg-yellow-100 transition">
                                    <x-heroicon-o-sparkles class="w-5 h-5 text-yellow-600" />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900">AI Article Generator</p>
                                    <p class="text-xs text-gray-500 mt-0.5">Generate with AI</p>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Insights (dengan submenu) -->
                <div class="relative group/insights w-full flex justify-center">
                    <button
                       class="w-14 h-14 rounded-2xl flex flex-col items-center justify-center gap-1 hover:bg-gray-50 transition {{ request()->routeIs('insights.*') ? 'bg-gray-100' : '' }}"
                       onclick="toggleSubmenu(event, 'insightsSubmenu')">
                        <x-heroicon-o-light-bulb class="w-6 h-6 text-gray-600" />
                        <span class="text-xs text-gray-500">Insights</span>
                    </button>

                    <!-- Submenu -->
                    <div id="insightsSubmenu" class="hidden absolute left-full top-0 ml-2 w-72 bg-white rounded-2xl shadow-lg border border-gray-200 p-3 z-50">
                        <div class="space-y-1">
                            <a href="{{ route('insights.index') }}"
                               class="flex items-start gap-3 p-3 rounded-xl hover:bg-gray-50 transition group">
                                <div class="w-10 h-10 bg-blue-50 rounded-lg flex items-center justify-center flex-shrink-0 group-hover:bg-blue-100 transition">
                                    <x-heroicon-o-light-bulb class="w-5 h-5 text-blue-600" />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900">All Insights</p>
                                    <p class="text-xs text-gray-500 mt-0.5">View all content ideas</p>
                                </div>
                            </a>

                            <a href="{{ route('insights.create') }}"
                               class="flex items-start gap-3 p-3 rounded-xl hover:bg-gray-50 transition group">
                                <div class="w-10 h-10 bg-green-50 rounded-lg flex items-center justify-center flex-shrink-0 group-hover:bg-green-100 transition">
                                    <x-heroicon-o-plus class="w-5 h-5 text-green-600" />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900">Add New Insight</p>
                                    <p class="text-xs text-gray-500 mt-0.5">Create content idea</p>
                                </div>
                            </a>

                            <a href="{{ route('insights.news.index') }}"
                               class="flex items-start gap-3 p-3 rounded-xl hover:bg-gray-50 transition group">
                                <div class="w-10 h-10 bg-purple-50 rounded-lg flex items-center justify-center flex-shrink-0 group-hover:bg-purple-100 transition">
                                    <x-heroicon-o-newspaper class="w-5 h-5 text-purple-600" />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900">News Insight</p>
                                    <p class="text-xs text-gray-500 mt-0.5">Search trending news</p>
                                </div>
                            </a>

                            <a href="{{ route('insights.social.index') }}"
                               class="flex items-start gap-3 p-3 rounded-xl hover:bg-gray-50 transition group">
                                <div class="w-10 h-10 bg-orange-50 rounded-lg flex items-center justify-center flex-shrink-0 group-hover:bg-orange-100 transition">
                                    <x-heroicon-o-fire class="w-5 h-5 text-orange-600" />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900">Social Insights</p>
                                    <p class="text-xs text-gray-500 mt-0.5">Trending social media</p>
                                </div>
                            </a>

                            <a href="{{ route('deep-research.index') }}"
                               class="flex items-start gap-3 p-3 rounded-xl hover:bg-gray-50 transition group">
                                <div class="w-10 h-10 bg-indigo-50 rounded-lg flex items-center justify-center flex-shrink-0 group-hover:bg-indigo-100 transition">
                                    <x-heroicon-o-magnifying-glass-circle class="w-5 h-5 text-indigo-600" />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900">Deep Research</p>
                                    <p class="text-xs text-gray-500 mt-0.5">Keyword research + AI</p>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Media (dengan submenu) -->
                <div class="relative group/media w-full flex justify-center">
                    <button
                       class="w-14 h-14 rounded-2xl flex flex-col items-center justify-center gap-1 hover:bg-gray-50 transition {{ request()->routeIs('media.*') || request()->routeIs('image-generation.*') ? 'bg-gray-100' : '' }}"
                       onclick="toggleSubmenu(event, 'mediaSubmenu')">
                        <x-heroicon-o-photo class="w-6 h-6 text-gray-600" />
                        <span class="text-xs text-gray-500">Media</span>
                    </button>

                    <!-- Submenu -->
                    <div id="mediaSubmenu" class="hidden absolute left-full top-0 ml-2 w-72 bg-white rounded-2xl shadow-lg border border-gray-200 p-3 z-50">
                        <div class="space-y-1">
                            <a href="{{ route('media.index') }}"
                               class="flex items-start gap-3 p-3 rounded-xl hover:bg-gray-50 transition group">
                                <div class="w-10 h-10 bg-blue-50 rounded-lg flex items-center justify-center flex-shrink-0 group-hover:bg-blue-100 transition">
                                    <x-heroicon-o-photo class="w-5 h-5 text-blue-600" />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900">All Media</p>
                                    <p class="text-xs text-gray-500 mt-0.5">Browse media library</p>
                                </div>
                            </a>

                            <a href="{{ route('image-generation.create') }}"
                               class="flex items-start gap-3 p-3 rounded-xl hover:bg-gray-50 transition group">
                                <div class="w-10 h-10 bg-purple-50 rounded-lg flex items-center justify-center flex-shrink-0 group-hover:bg-purple-100 transition">
                                    <x-heroicon-o-sparkles class="w-5 h-5 text-purple-600" />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900">Generate Image</p>
                                    <p class="text-xs text-gray-500 mt-0.5">AI image generator</p>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Calendar -->
                <a href="{{ route('calendar.index') }}"
                   class="w-14 h-14 rounded-2xl flex flex-col items-center justify-center gap-1 hover:bg-gray-50 transition {{ request()->routeIs('calendar.*') ? 'bg-gray-100' : '' }}"
                   title="Calendar">
                    <x-heroicon-o-calendar-days class="w-6 h-6 text-gray-600" />
                    <span class="text-xs text-gray-500">Calendar</span>
                </a>

                <!-- Social -->
                <a href="{{ route('social-accounts.index') }}"
                   class="w-14 h-14 rounded-2xl flex flex-col items-center justify-center gap-1 hover:bg-gray-50 transition {{ request()->routeIs('social-accounts.*') ? 'bg-gray-100' : '' }}"
                   title="Social Accounts">
                    <x-heroicon-o-share class="w-6 h-6 text-gray-600" />
                    <span class="text-xs text-gray-500">Social</span>
                </a>

                <!-- Asset Library (with submenu) -->
                <div class="relative group/assets w-full flex justify-center">
                    <button
                       class="w-14 h-14 rounded-2xl flex flex-col items-center justify-center gap-1 hover:bg-gray-50 transition {{ request()->routeIs('assets.*') || request()->routeIs('brandings.*') || request()->routeIs('templates.*') ? 'bg-gray-100' : '' }}"
                       onclick="toggleSubmenu(event, 'assetsSubmenu')">
                        <x-heroicon-o-squares-2x2 class="w-6 h-6 text-gray-600" />
                        <span class="text-xs text-gray-500">Assets</span>
                    </button>

                    <!-- Submenu -->
                    <div id="assetsSubmenu" class="hidden absolute left-full top-0 ml-2 w-72 bg-white rounded-2xl shadow-lg border border-gray-200 p-3 z-50">
                        <div class="space-y-1">
                            <a href="{{ route('brandings.index') }}"
                               class="flex items-start gap-3 p-3 rounded-xl hover:bg-gray-50 transition group">
                                <div class="w-10 h-10 bg-purple-50 rounded-lg flex items-center justify-center flex-shrink-0 group-hover:bg-purple-100 transition">
                                    <x-heroicon-o-paint-brush class="w-5 h-5 text-purple-600" />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900">Branding</p>
                                    <p class="text-xs text-gray-500 mt-0.5">Manage brand presets</p>
                                </div>
                            </a>

                            <a href="{{ route('templates.index') }}"
                               class="flex items-start gap-3 p-3 rounded-xl hover:bg-gray-50 transition group">
                                <div class="w-10 h-10 bg-blue-50 rounded-lg flex items-center justify-center flex-shrink-0 group-hover:bg-blue-100 transition">
                                    <x-heroicon-o-rectangle-stack class="w-5 h-5 text-blue-600" />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900">Templates</p>
                                    <p class="text-xs text-gray-500 mt-0.5">Manage design templates</p>
                                </div>
                            </a>

                            <a href="{{ route('assets.index') }}"
                               class="flex items-start gap-3 p-3 rounded-xl hover:bg-gray-50 transition group">
                                <div class="w-10 h-10 bg-orange-50 rounded-lg flex items-center justify-center flex-shrink-0 group-hover:bg-orange-100 transition">
                                    <x-heroicon-o-photo class="w-5 h-5 text-orange-600" />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900">All Assets</p>
                                    <p class="text-xs text-gray-500 mt-0.5">View all social media assets</p>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Websites (dengan submenu) -->
                <div class="relative group/websites w-full flex justify-center">
                    <button
                       class="w-14 h-14 rounded-2xl flex flex-col items-center justify-center gap-1 hover:bg-gray-50 transition {{ request()->routeIs('websites.*') || request()->routeIs('competitors.*') || request()->routeIs('website-analytics.*') ? 'bg-gray-100' : '' }}"
                       onclick="toggleSubmenu(event, 'websitesSubmenu')">
                        <x-heroicon-o-globe-alt class="w-6 h-6 text-gray-600" />
                        <span class="text-xs text-gray-500">Websites</span>
                    </button>

                    <!-- Submenu -->
                    <div id="websitesSubmenu" class="hidden absolute left-full top-0 ml-2 w-72 bg-white rounded-2xl shadow-lg border border-gray-200 p-3 z-50">
                        <div class="space-y-1">
                            <a href="{{ route('websites.index') }}"
                               class="flex items-start gap-3 p-3 rounded-xl hover:bg-gray-50 transition group">
                                <div class="w-10 h-10 bg-green-50 rounded-lg flex items-center justify-center flex-shrink-0 group-hover:bg-green-100 transition">
                                    <x-heroicon-o-globe-alt class="w-5 h-5 text-green-600" />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900">Your Websites</p>
                                    <p class="text-xs text-gray-500 mt-0.5">Manage WordPress sites</p>
                                </div>
                            </a>

                            <a href="{{ route('competitors.index') }}"
                               class="flex items-start gap-3 p-3 rounded-xl hover:bg-gray-50 transition group">
                                <div class="w-10 h-10 bg-blue-50 rounded-lg flex items-center justify-center flex-shrink-0 group-hover:bg-blue-100 transition">
                                    <x-heroicon-o-users class="w-5 h-5 text-blue-600" />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900">Competitors</p>
                                    <p class="text-xs text-gray-500 mt-0.5">Analyze & benchmark rivals</p>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Image Editor (POC) -->
                <a href="{{ route('image-editor.index') }}"
                   class="w-14 h-14 rounded-2xl flex flex-col items-center justify-center gap-1 hover:bg-gray-50 transition {{ request()->routeIs('image-editor.*') ? 'bg-gray-100' : '' }}"
                   title="Image Editor">
                    <x-heroicon-o-photo class="w-6 h-6 text-gray-600" />
                    <span class="text-xs text-gray-500">Editor</span>
                </a>
            </nav>

            <!-- User Profile -->
            @auth
            <div class="mt-auto pt-4 border-t border-gray-100 w-full flex justify-center">
                <div class="relative group">
                    <img src="https://www.gravatar.com/avatar/{{ md5(strtolower(trim(auth()->user()->email))) }}?s=48&d=mp"
                         alt="{{ auth()->user()->name }}"
                         class="w-10 h-10 rounded-full cursor-pointer ring-2 ring-gray-100"
                         onclick="toggleSubmenu(event, 'accountSubmenu')">

                    <!-- Account Submenu -->
                    <div id="accountSubmenu" class="hidden absolute bottom-0 left-full ml-2 w-64 bg-white rounded-2xl shadow-lg border border-gray-200 p-4 z-50">
                        <div class="flex items-center gap-3 mb-3 pb-3 border-b border-gray-100">
                            <img src="https://www.gravatar.com/avatar/{{ md5(strtolower(trim(auth()->user()->email))) }}?s=40&d=mp"
                                 alt="{{ auth()->user()->name }}"
                                 class="w-10 h-10 rounded-full">
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 truncate">{{ auth()->user()->name }}</p>
                                <p class="text-xs text-gray-500 capitalize">{{ auth()->user()->subscription_plan }}</p>
                            </div>
                        </div>

                        <a href="{{ route('ai-templates.index') }}"
                           class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-gray-50 transition text-sm text-gray-700">
                            <x-heroicon-o-cpu-chip class="w-4 h-4" />
                            AI Templates
                        </a>

                        <a href="{{ route('profile.edit') }}"
                           class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-gray-50 transition text-sm text-gray-700">
                            <x-heroicon-o-user-circle class="w-4 h-4" />
                            Profile & Locale
                        </a>

                        @if(auth()->user()->role === 'super_admin')
                        <a href="{{ route('admin.dashboard') }}"
                           class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-gray-50 transition text-sm text-gray-700">
                            <x-heroicon-o-cog-6-tooth class="w-4 h-4" />
                            Admin Panel
                        </a>
                        @endif

                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit"
                                    class="w-full flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-red-50 transition text-sm text-red-600">
                                <x-heroicon-o-arrow-right-on-rectangle class="w-4 h-4" />
                                Logout
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            @endauth
        </aside>

        <!-- Overlay untuk close submenu -->
        <div id="submenuOverlay" class="hidden fixed inset-0 z-40" onclick="closeAllSubmenus()"></div>

        <!-- Main Content -->
        <div class="flex-1 overflow-auto">
            @yield('content')
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

    <!-- Global Media Library Component -->
    <x-media-library />

</body>
</html>
