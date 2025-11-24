<!DOCTYPE html>
<html lang="en" class="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>C-Mail</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class'
        }
    </script>
    <script src="https://kit.fontawesome.com/00d4801eb6.js" crossorigin="anonymous"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script>
        // Dark mode toggle script
        if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark')
        } else {
            document.documentElement.classList.remove('dark')
        }
        
        function toggleDarkMode() {
            if (document.documentElement.classList.contains('dark')) {
                document.documentElement.classList.remove('dark')
                localStorage.theme = 'light'
            } else {
                document.documentElement.classList.add('dark')
                localStorage.theme = 'dark'
            }
        }
    </script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>
<body class="bg-gray-50 dark:bg-gray-900 transition-colors duration-200">

    <!-- Navbar -->
    <nav class="fixed top-0 left-0 right-0 h-16 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 z-30 transition-colors duration-200">
        <div class="flex items-center justify-between h-full px-6">
            <div class="flex items-center space-x-3">
                <img src="{{ asset('img/logo-light.png') }}" class="w-8 h-8 dark:hidden">
                <img src="{{ asset('img/logo-dark.png') }}" class="w-8 h-8 hidden dark:block">
                <h1 class="text-xl font-bold text-gray-900 dark:text-white">C-Mail</h1>
            </div>
            
            <div class="flex items-center space-x-4">
                <!-- Dark Mode Toggle -->
                <button onclick="toggleDarkMode()" class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-200">
                    <i class="fa-solid fa-moon text-gray-600 dark:text-gray-300 dark:hidden"></i>
                    <i class="fa-solid fa-sun text-yellow-400 hidden dark:inline"></i>
                </button>
                
                <!-- User Greeting -->
                <div class="text-sm text-gray-700 dark:text-gray-300">
                    Hi, <span class="font-semibold">{{ $user->name ?? 'User' }}</span>
                </div>
            </div>
        </div>
    </nav>

    <!-- Sidebar -->
    <aside class="fixed left-0 top-16 bottom-0 w-64 bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 transition-colors duration-200 z-20">
        <div class="flex flex-col h-full p-4">
            <!-- Create Button -->
            <a href="{{ route('create') }}" class="flex items-center justify-center space-x-2 bg-purple-600 hover:bg-purple-700 dark:bg-purple-500 dark:hover:bg-purple-600 text-white font-semibold py-3 px-4 rounded-lg mb-6 transition-colors duration-200 shadow-sm hover:shadow-md">
                <i class="fa-solid fa-plus"></i>
                <span>Create</span>
            </a>
            
            <!-- Navigation Links -->
            <nav class="flex-1 space-y-1">
                <a href="{{ route('dashboard.inbox') }}" class="flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-200 {{ request()->routeIs('dashboard.inbox') ? 'bg-purple-50 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400 font-medium' : '' }}">
                    <i class="fa-solid fa-inbox w-5"></i>
                    <span>Inbox</span>
                </a>
                
                <a href="{{ route('sent') }}" class="flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-200 {{ request()->routeIs('message.sent') ? 'bg-purple-50 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400 font-medium' : '' }}">
                    <i class="fa-solid fa-paper-plane w-5"></i>
                    <span>Sent</span>
                </a>
                
                <a href="{{ route('secret.index') }}" class="flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-200 {{ request()->routeIs('secret') ? 'bg-purple-50 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400 font-medium' : '' }}">
                    <i class="fa-solid fa-mask w-5"></i>
                    <span>Secret Message</span>
                </a>
            </nav>
            
            <!-- Logout Button at Bottom -->
            <div class="pt-4 border-t border-gray-200 dark:border-gray-700">
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="w-full flex items-center space-x-3 px-4 py-3 rounded-lg text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors duration-200 font-medium">
                        <i class="fa-solid fa-right-from-bracket w-5"></i>
                        <span>Logout</span>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="ml-64 mt-16 p-6 min-h-screen">
        <div class="max-w-7xl mx-auto">
            @yield('content')
        </div>
    </main>

</body>
</html>