@extends('layouts.app')

@section('content')
<script>
    tailwind.config = {
        darkMode: 'class'
    }
</script>

<div class="max-w-4xl mx-auto">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Create New Message</h1>
        <p class="text-gray-600 dark:text-gray-400 mt-2">Send a message or secret mail to other users</p>
    </div>

    <!-- Error Messages -->
    @if($errors->any())
        <div class="bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-lg p-4 mb-6">
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

    <!-- Message Type Toggle -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 mb-6 overflow-hidden transition-colors duration-200">
        <div class="grid grid-cols-2">
            <button type="button" onclick="switchMailType('text')" id="btnTextMail" class="mail-type-btn active px-6 py-4 text-center font-semibold transition-all duration-200 border-b-2 border-purple-600 bg-purple-50 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400">
                <i class="fa-solid fa-envelope mr-2"></i>
                <span>Mail Text</span>
            </button>
            <button type="button" onclick="switchMailType('secret')" id="btnSecretMail" class="mail-type-btn px-6 py-4 text-center font-semibold transition-all duration-200 border-b-2 border-transparent text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700/50">
                <i class="fa-solid fa-mask mr-2"></i>
                <span>Secret Mail</span>
            </button>
        </div>
    </div>

    <!-- Form for Text Mail -->
    <form id="textMailForm" action="{{ route('messages.store') }}" method="POST" enctype="multipart/form-data" class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 transition-colors duration-200">
        @csrf
        
        <!-- Hidden field for mail type -->
        <input type="hidden" name="mail_type" value="text">

        <!-- Receiver Selection -->
        <div class="mb-6">
            <label for="receiver_id" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                <i class="fa-solid fa-user mr-2 text-purple-600 dark:text-purple-400"></i>
                Select Receiver
            </label>
            <select name="receiver_id" id="receiver_id" required class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent text-gray-900 dark:text-white transition-colors duration-200">
                <option value="">-- Choose a user --</option>
                @foreach($users as $user)
                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                @endforeach
            </select>
        </div>

        <!-- Message Text -->
        <div class="mb-6">
            <label for="message" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                <i class="fa-solid fa-message mr-2 text-purple-600 dark:text-purple-400"></i>
                Message
            </label>
            <textarea name="message" id="message" rows="6" placeholder="Write your message here..." class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 transition-colors duration-200 resize-none"></textarea>
        </div>

        <!-- File Upload (Text Mail Only) -->
        <div id="fileUploadSection" class="mb-6">
            <label for="file" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                <i class="fa-solid fa-file mr-2 text-purple-600 dark:text-purple-400"></i>
                Attach File <span class="text-gray-500 dark:text-gray-400 font-normal">(Optional)</span>
            </label>
            <div class="relative">
                <input type="file" name="file" id="file" class="hidden" onchange="updateFileName('file', 'fileLabel')">
                <label for="file" class="flex items-center justify-between w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg cursor-pointer hover:border-purple-500 dark:hover:border-purple-400 transition-colors duration-200">
                    <span id="fileLabel" class="text-gray-500 dark:text-gray-400">
                        <i class="fa-solid fa-cloud-arrow-up mr-2"></i>
                        Choose file or drag here
                    </span>
                    <span class="text-xs text-gray-400 dark:text-gray-500">Max 5MB</span>
                </label>
            </div>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">Supported: All file types up to 5MB</p>
        </div>

        <!-- Image Upload (Secret Mail Only) -->
        <div id="imageUploadSection" class="mb-6 hidden">
            <label for="image" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                <i class="fa-solid fa-image mr-2 text-purple-600 dark:text-purple-400"></i>
                Attach Image <span class="text-gray-500 dark:text-gray-400 font-normal">(Required for Secret Mail)</span>
            </label>
            <div class="relative">
                <input type="file" name="image" id="image" accept="image/*" class="hidden" onchange="updateFileName('image', 'imageLabel')">
                <label for="image" class="flex items-center justify-between w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg cursor-pointer hover:border-purple-500 dark:hover:border-purple-400 transition-colors duration-200">
                    <span id="imageLabel" class="text-gray-500 dark:text-gray-400">
                        <i class="fa-solid fa-cloud-arrow-up mr-2"></i>
                        Choose image or drag here
                    </span>
                    <span class="text-xs text-gray-400 dark:text-gray-500">Max 2MB</span>
                </label>
            </div>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">Supported: JPG, PNG, GIF up to 2MB</p>
        </div>

        <!-- Submit Button -->
        <div class="flex items-center justify-between pt-4 border-t border-gray-200 dark:border-gray-700">
            <a href="{{ route('dashboard.inbox') }}" class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white font-medium transition-colors duration-200">
                <i class="fa-solid fa-arrow-left mr-2"></i>
                Cancel
            </a>
            <button type="submit" id="submitBtn" class="flex items-center space-x-2 bg-purple-600 hover:bg-purple-700 dark:bg-purple-500 dark:hover:bg-purple-600 text-white font-semibold px-6 py-3 rounded-lg transition-colors duration-200 shadow-sm hover:shadow-md">
                <i class="fa-solid fa-paper-plane"></i>
                <span id="submitText">Send Message</span>
            </button>
        </div>
    </form>

    <!-- Form for Secret Mail (Hidden by default) -->
    <form id="secretMailForm" action="{{ route('secret.send') }}" method="POST" enctype="multipart/form-data" class="hidden bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 transition-colors duration-200">
        @csrf
        
        <!-- Hidden field for mail type -->
        <input type="hidden" name="mail_type" value="secret">

        <!-- Receiver Selection -->
        <div class="mb-6">
            <label for="receiver_id_secret" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                <i class="fa-solid fa-user mr-2 text-purple-600 dark:text-purple-400"></i>
                Select Receiver
            </label>
            <select name="receiver_id" id="receiver_id_secret" required class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent text-gray-900 dark:text-white transition-colors duration-200">
                <option value="">-- Choose a user --</option>
                @foreach($users as $user)
                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                @endforeach
            </select>
        </div>

        <!-- Message Text -->
        <div class="mb-6">
            <label for="message_secret" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                <i class="fa-solid fa-message mr-2 text-purple-600 dark:text-purple-400"></i>
                Secret Message
            </label>
            <textarea name="message" id="message_secret" rows="6" placeholder="Write your secret message here..." required class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 transition-colors duration-200 resize-none"></textarea>
        </div>

        <!-- Image Upload (Secret Mail) -->
        <div class="mb-6">
            <label for="image_secret" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                <i class="fa-solid fa-image mr-2 text-purple-600 dark:text-purple-400"></i>
                Attach Image <span class="text-red-500">*</span>
            </label>
            <div class="relative">
                <input type="file" name="image" id="image_secret" accept="image/jpeg,image/jpg" required class="hidden" onchange="updateFileName('image_secret', 'imageLabel_secret')">
                <label for="image_secret" class="flex items-center justify-between w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg cursor-pointer hover:border-purple-500 dark:hover:border-purple-400 transition-colors duration-200">
                    <span id="imageLabel_secret" class="text-gray-500 dark:text-gray-400">
                        <i class="fa-solid fa-cloud-arrow-up mr-2"></i>
                        Choose JPEG image to hide message
                    </span>
                    <span class="text-xs text-gray-400 dark:text-gray-500">Max 2MB</span>
                </label>
            </div>
            <p class="text-xs text-purple-600 dark:text-purple-400 mt-2">
                <i class="fa-solid fa-info-circle mr-1"></i>
                Only JPEG format supported for steganography. Your message will be hidden inside this image.
            </p>
        </div>

        <!-- Submit Button -->
        <div class="flex items-center justify-between pt-4 border-t border-gray-200 dark:border-gray-700">
            <a href="{{ route('dashboard.inbox') }}" class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white font-medium transition-colors duration-200">
                <i class="fa-solid fa-arrow-left mr-2"></i>
                Cancel
            </a>
            <button type="submit" class="flex items-center space-x-2 bg-purple-600 hover:bg-purple-700 dark:bg-purple-500 dark:hover:bg-purple-600 text-white font-semibold px-6 py-3 rounded-lg transition-colors duration-200 shadow-sm hover:shadow-md">
                <i class="fa-solid fa-mask"></i>
                <span>Send Secret Message</span>
            </button>
        </div>
    </form>
</div>

<script>
function switchMailType(type) {
    const textMailBtn = document.getElementById('btnTextMail');
    const secretMailBtn = document.getElementById('btnSecretMail');
    const textMailForm = document.getElementById('textMailForm');
    const secretMailForm = document.getElementById('secretMailForm');
    
    if (type === 'text') {
        // Update buttons
        textMailBtn.classList.add('active', 'border-purple-600', 'bg-purple-50', 'dark:bg-purple-900/30', 'text-purple-600', 'dark:text-purple-400');
        textMailBtn.classList.remove('border-transparent', 'text-gray-600', 'dark:text-gray-400');
        secretMailBtn.classList.remove('active', 'border-purple-600', 'bg-purple-50', 'dark:bg-purple-900/30', 'text-purple-600', 'dark:text-purple-400');
        secretMailBtn.classList.add('border-transparent', 'text-gray-600', 'dark:text-gray-400');
        
        // Show/hide forms
        textMailForm.classList.remove('hidden');
        secretMailForm.classList.add('hidden');
        
    } else {
        // Update buttons
        secretMailBtn.classList.add('active', 'border-purple-600', 'bg-purple-50', 'dark:bg-purple-900/30', 'text-purple-600', 'dark:text-purple-400');
        secretMailBtn.classList.remove('border-transparent', 'text-gray-600', 'dark:text-gray-400');
        textMailBtn.classList.remove('active', 'border-purple-600', 'bg-purple-50', 'dark:bg-purple-900/30', 'text-purple-600', 'dark:text-purple-400');
        textMailBtn.classList.add('border-transparent', 'text-gray-600', 'dark:text-gray-400');
        
        // Show/hide forms
        textMailForm.classList.add('hidden');
        secretMailForm.classList.remove('hidden');
    }
}

function updateFileName(inputId, labelId) {
    const input = document.getElementById(inputId);
    const label = document.getElementById(labelId);
    const icon = '<i class="fa-solid fa-check-circle mr-2 text-green-500"></i>';
    
    if (input.files.length > 0) {
        const fileName = input.files[0].name;
        const fileSize = (input.files[0].size / 1024 / 1024).toFixed(2); // Convert to MB
        label.innerHTML = `${icon}${fileName} <span class="text-xs text-gray-500">(${fileSize} MB)</span>`;
    }
}
</script>
@endsection