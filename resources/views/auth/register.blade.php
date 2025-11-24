<!DOCTYPE html>
<html lang="en" class="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - C-Mail</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class'
        }
    </script>
    <script src="https://kit.fontawesome.com/00d4801eb6.js" crossorigin="anonymous"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
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
        .password-strength-bar {
            height: 4px;
            border-radius: 2px;
            transition: all 0.3s ease;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-purple-50 via-white to-purple-50 dark:from-gray-900 dark:via-gray-800 dark:to-gray-900 min-h-screen flex items-center justify-center py-12 transition-colors duration-200">
    
    <!-- Dark Mode Toggle (Top Right) -->
    <button onclick="toggleDarkMode()" class="fixed top-6 right-6 p-3 rounded-full bg-white dark:bg-gray-800 shadow-lg hover:shadow-xl transition-all duration-200 border border-gray-200 dark:border-gray-700 z-50">
        <i class="fa-solid fa-moon text-gray-600 dark:text-gray-300 dark:hidden"></i>
        <i class="fa-solid fa-sun text-yellow-400 hidden dark:inline"></i>
    </button>

    <div class="w-full max-w-md px-6">
        <!-- Logo & Title -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 mt-4 mb-4">
                <img src="{{ asset('img/logo-light.png') }}" class="dark:hidden">
                <img src="{{ asset('img/logo-dark.png') }}" class="hidden dark:block">
            </div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">Create Account</h1>
            <p class="text-gray-600 dark:text-gray-400">Join C-Mail and start messaging securely</p>
        </div>

        <!-- Register Card -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 p-8 transition-colors duration-200">
            
            <!-- Session Error -->
            @if(session('error'))
                <div class="mb-6 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-lg p-4">
                    <div class="flex items-start">
                        <i class="fa-solid fa-circle-exclamation text-red-600 dark:text-red-400 mt-0.5 mr-3"></i>
                        <div class="flex-1">
                            <h3 class="text-sm font-semibold text-red-800 dark:text-red-300 mb-1">Registration Failed</h3>
                            <p class="text-sm text-red-700 dark:text-red-400">{{ session('error') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Validation Errors -->
            @if($errors->any())
                <div class="mb-6 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-lg p-4">
                    <div class="flex items-start">
                        <i class="fa-solid fa-circle-exclamation text-red-600 dark:text-red-400 mt-0.5 mr-3"></i>
                        <div class="flex-1">
                            <h3 class="text-sm font-semibold text-red-800 dark:text-red-300 mb-2">Please fix the following errors:</h3>
                            <ul class="list-disc list-inside space-y-1 text-sm text-red-700 dark:text-red-400">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif

            <form method="POST" action="{{ route('register.process') }}" class="space-y-5" id="registerForm">
                @csrf

                <!-- Name Field -->
                <div>
                    <label for="name" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                        <i class="fa-solid fa-user mr-2 text-purple-600 dark:text-purple-400"></i>
                        Full Name
                    </label>
                    <input 
                        type="text" 
                        id="name"
                        name="name" 
                        value="{{ old('name') }}"
                        class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border @error('name') border-red-500 dark:border-red-600 @else border-gray-300 dark:border-gray-600 @enderror rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 transition-colors duration-200"
                        placeholder="John Doe"
                        required
                        autofocus
                        autocomplete="name"
                        minlength="3"
                    >
                    @error('name')
                        <p class="mt-2 text-sm text-red-600 dark:text-red-400">
                            <i class="fa-solid fa-circle-exclamation mr-1"></i>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- Email Field -->
                <div>
                    <label for="email" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                        <i class="fa-solid fa-envelope mr-2 text-purple-600 dark:text-purple-400"></i>
                        Email Address
                    </label>
                    <input 
                        type="email" 
                        id="email"
                        name="email" 
                        value="{{ old('email') }}"
                        class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border @error('email') border-red-500 dark:border-red-600 @else border-gray-300 dark:border-gray-600 @enderror rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 transition-colors duration-200"
                        placeholder="you@example.com"
                        required
                        autocomplete="email"
                    >
                    @error('email')
                        <p class="mt-2 text-sm text-red-600 dark:text-red-400">
                            <i class="fa-solid fa-circle-exclamation mr-1"></i>
                            {{ $message }}
                        </p>
                    @else
                        <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                            <i class="fa-solid fa-circle-info mr-1"></i>
                            We'll never share your email with anyone else.
                        </p>
                    @enderror
                </div>

                <!-- Password Field -->
                <div>
                    <label for="password" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                        <i class="fa-solid fa-lock mr-2 text-purple-600 dark:text-purple-400"></i>
                        Password
                    </label>
                    <div class="relative">
                        <input 
                            type="password" 
                            id="password"
                            name="password" 
                            class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border @error('password') border-red-500 dark:border-red-600 @else border-gray-300 dark:border-gray-600 @enderror rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 transition-colors duration-200 pr-12"
                            placeholder="••••••••"
                            required
                            autocomplete="new-password"
                            minlength="8"
                            oninput="checkPasswordStrength()"
                        >
                        <button 
                            type="button" 
                            onclick="togglePassword('password', 'eye-icon')"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 transition-colors duration-200"
                        >
                            <i class="fa-solid fa-eye" id="eye-icon"></i>
                        </button>
                    </div>
                    
                    <!-- Password Strength Indicator -->
                    <div class="mt-2">
                        <div class="flex gap-1" id="strengthBars">
                            <div class="flex-1 password-strength-bar bg-gray-200 dark:bg-gray-700"></div>
                            <div class="flex-1 password-strength-bar bg-gray-200 dark:bg-gray-700"></div>
                            <div class="flex-1 password-strength-bar bg-gray-200 dark:bg-gray-700"></div>
                            <div class="flex-1 password-strength-bar bg-gray-200 dark:bg-gray-700"></div>
                        </div>
                        <p class="mt-1 text-xs" id="strengthText">
                            <span class="text-gray-500 dark:text-gray-400">Password strength: <span id="strengthLabel">None</span></span>
                        </p>
                    </div>
                    
                    @error('password')
                        <p class="mt-2 text-sm text-red-600 dark:text-red-400">
                            <i class="fa-solid fa-circle-exclamation mr-1"></i>
                            {{ $message }}
                        </p>
                    @else
                        <ul class="mt-2 space-y-1 text-xs text-gray-500 dark:text-gray-400">
                            <li class="flex items-center" id="check-length">
                                <i class="fa-solid fa-circle text-[6px] mr-2"></i>
                                At least 8 characters
                            </li>
                            <li class="flex items-center" id="check-uppercase">
                                <i class="fa-solid fa-circle text-[6px] mr-2"></i>
                                One uppercase letter
                            </li>
                            <li class="flex items-center" id="check-lowercase">
                                <i class="fa-solid fa-circle text-[6px] mr-2"></i>
                                One lowercase letter
                            </li>
                            <li class="flex items-center" id="check-number">
                                <i class="fa-solid fa-circle text-[6px] mr-2"></i>
                                One number
                            </li>
                        </ul>
                    @enderror
                </div>

                <!-- Password Confirmation Field -->
                <div>
                    <label for="password_confirmation" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                        <i class="fa-solid fa-lock mr-2 text-purple-600 dark:text-purple-400"></i>
                        Confirm Password
                    </label>
                    <div class="relative">
                        <input 
                            type="password" 
                            id="password_confirmation"
                            name="password_confirmation" 
                            class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border @error('password_confirmation') border-red-500 dark:border-red-600 @else border-gray-300 dark:border-gray-600 @enderror rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 transition-colors duration-200 pr-12"
                            placeholder="••••••••"
                            required
                            autocomplete="new-password"
                            oninput="checkPasswordMatch()"
                        >
                        <button 
                            type="button" 
                            onclick="togglePassword('password_confirmation', 'eye-icon-confirm')"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 transition-colors duration-200"
                        >
                            <i class="fa-solid fa-eye" id="eye-icon-confirm"></i>
                        </button>
                    </div>
                    <p class="mt-2 text-xs hidden" id="matchMessage"></p>
                    @error('password_confirmation')
                        <p class="mt-2 text-sm text-red-600 dark:text-red-400">
                            <i class="fa-solid fa-circle-exclamation mr-1"></i>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- Submit Button -->
                <button 
                    type="submit" 
                    class="w-full flex items-center justify-center gap-2 bg-gradient-to-r from-purple-600 to-purple-700 dark:from-purple-500 dark:to-purple-600 hover:from-purple-700 hover:to-purple-800 dark:hover:from-purple-600 dark:hover:to-purple-700 text-white font-semibold px-6 py-3.5 rounded-lg transition-all duration-200 shadow-lg hover:shadow-xl"
                >
                    <i class="fa-solid fa-user-plus"></i>
                    <span>Create Account</span>
                </button>
            </form>

            <!-- Divider -->
            <div class="relative my-6">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-gray-300 dark:border-gray-600"></div>
                </div>
                <div class="relative flex justify-center text-sm">
                    <span class="px-4 bg-white dark:bg-gray-800 text-gray-500 dark:text-gray-400">or</span>
                </div>
            </div>

            <!-- Login Link -->
            <div class="text-center">
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Already have an account? 
                    <a href="{{ route('login') }}" class="font-semibold text-purple-600 dark:text-purple-400 hover:text-purple-700 dark:hover:text-purple-300 transition-colors duration-200">
                        Sign in here
                    </a>
                </p>
            </div>
        </div>

        <!-- Footer -->
        <div class="text-center mt-8">
            <p class="text-sm text-gray-500 dark:text-gray-400">
                © 2025 C-Mail. Secure email messaging platform.
            </p>
        </div>
    </div>

    <script>
        function togglePassword(inputId, iconId) {
            const passwordInput = document.getElementById(inputId);
            const eyeIcon = document.getElementById(iconId);
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.classList.remove('fa-eye');
                eyeIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                eyeIcon.classList.remove('fa-eye-slash');
                eyeIcon.classList.add('fa-eye');
            }
        }

        function checkPasswordStrength() {
            const password = document.getElementById('password').value;
            const bars = document.querySelectorAll('.password-strength-bar');
            const strengthLabel = document.getElementById('strengthLabel');
            
            // Check requirements
            const hasLength = password.length >= 8;
            const hasUpper = /[A-Z]/.test(password);
            const hasLower = /[a-z]/.test(password);
            const hasNumber = /[0-9]/.test(password);
            const hasSpecial = /[!@#$%^&*(),.?":{}|<>]/.test(password);
            
            // Update requirement checkmarks
            updateCheck('check-length', hasLength);
            updateCheck('check-uppercase', hasUpper);
            updateCheck('check-lowercase', hasLower);
            updateCheck('check-number', hasNumber);
            
            // Calculate strength
            let strength = 0;
            if (hasLength) strength++;
            if (hasUpper) strength++;
            if (hasLower) strength++;
            if (hasNumber) strength++;
            if (hasSpecial) strength++;
            
            // Reset bars
            bars.forEach(bar => {
                bar.className = 'flex-1 password-strength-bar bg-gray-200 dark:bg-gray-700';
            });
            
            // Update bars and label
            if (password.length === 0) {
                strengthLabel.textContent = 'None';
                strengthLabel.className = 'text-gray-500 dark:text-gray-400';
            } else if (strength <= 2) {
                bars[0].className = 'flex-1 password-strength-bar bg-red-500';
                strengthLabel.textContent = 'Weak';
                strengthLabel.className = 'text-red-600 dark:text-red-400 font-semibold';
            } else if (strength === 3) {
                bars[0].className = 'flex-1 password-strength-bar bg-yellow-500';
                bars[1].className = 'flex-1 password-strength-bar bg-yellow-500';
                strengthLabel.textContent = 'Fair';
                strengthLabel.className = 'text-yellow-600 dark:text-yellow-400 font-semibold';
            } else if (strength === 4) {
                bars[0].className = 'flex-1 password-strength-bar bg-blue-500';
                bars[1].className = 'flex-1 password-strength-bar bg-blue-500';
                bars[2].className = 'flex-1 password-strength-bar bg-blue-500';
                strengthLabel.textContent = 'Good';
                strengthLabel.className = 'text-blue-600 dark:text-blue-400 font-semibold';
            } else {
                bars.forEach(bar => {
                    bar.className = 'flex-1 password-strength-bar bg-green-500';
                });
                strengthLabel.textContent = 'Strong';
                strengthLabel.className = 'text-green-600 dark:text-green-400 font-semibold';
            }
        }

        function updateCheck(id, passed) {
            const element = document.getElementById(id);
            if (!element) return;
            
            const icon = element.querySelector('i');
            if (passed) {
                element.className = 'flex items-center text-green-600 dark:text-green-400';
                icon.className = 'fa-solid fa-check-circle text-sm mr-2';
            } else {
                element.className = 'flex items-center text-gray-500 dark:text-gray-400';
                icon.className = 'fa-solid fa-circle text-[6px] mr-2';
            }
        }

        function checkPasswordMatch() {
            const password = document.getElementById('password').value;
            const confirmation = document.getElementById('password_confirmation').value;
            const matchMessage = document.getElementById('matchMessage');
            
            if (confirmation.length === 0) {
                matchMessage.classList.add('hidden');
                return;
            }
            
            matchMessage.classList.remove('hidden');
            
            if (password === confirmation) {
                matchMessage.className = 'mt-2 text-xs text-green-600 dark:text-green-400';
                matchMessage.innerHTML = '<i class="fa-solid fa-check-circle mr-1"></i>Passwords match!';
            } else {
                matchMessage.className = 'mt-2 text-xs text-red-600 dark:text-red-400';
                matchMessage.innerHTML = '<i class="fa-solid fa-circle-exclamation mr-1"></i>Passwords do not match';
            }
        }

        // Auto-hide alerts after 5 seconds
        setTimeout(() => {
            const alerts = document.querySelectorAll('[class*="bg-red-50"]');
            alerts.forEach(alert => {
                alert.style.transition = 'opacity 0.5s';
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 500);
            });
        }, 5000);
    </script>
</body>
</html>