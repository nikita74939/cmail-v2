@extends('layouts.app')

@section('content')
<script>
    tailwind.config = {
        darkMode: 'class'
    }
</script>

<div class="max-w-6xl mx-auto">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex items-center gap-3 mb-2">
            <i class="fa-solid fa-mask text-3xl text-purple-600 dark:text-purple-400"></i>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Secret Messages</h1>
        </div>
        <p class="text-sm text-gray-600 dark:text-gray-400">
            {{ isset($decryptedMessages) ? $decryptedMessages->count() : 0 }} secret messages hidden in images
        </p>
    </div>

    <!-- Info Banner -->
    <div class="mb-6 bg-purple-50 dark:bg-purple-900/30 border border-purple-200 dark:border-purple-800 rounded-lg p-4">
        <div class="flex items-start">
            <i class="fa-solid fa-circle-info text-purple-600 dark:text-purple-400 mt-0.5 mr-3"></i>
            <div class="flex-1">
                <h3 class="text-sm font-semibold text-purple-800 dark:text-purple-300 mb-1">About Secret Messages</h3>
                <p class="text-sm text-purple-700 dark:text-purple-400">
                    Secret messages are hidden inside images using steganography. The message is encrypted and embedded into the image pixels. Only you can extract and decrypt them.
                </p>
            </div>
        </div>
    </div>

    @if(isset($decryptedMessages) && $decryptedMessages->isNotEmpty())
        <!-- Message List -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden transition-colors duration-200">
            @foreach($decryptedMessages as $index => $msg)
                <!-- Message Item (Collapsed) -->
                <div class="message-item border-b border-gray-200 dark:border-gray-700 last:border-b-0 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors duration-150 cursor-pointer" 
                     onclick="toggleMessage({{ $index }})">
                    <div class="flex items-center gap-4 px-6 py-4">
                        <!-- Avatar with Secret Badge -->
                        <div class="flex-shrink-0 relative">
                            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-purple-500 to-purple-600 dark:from-purple-400 dark:to-purple-500 flex items-center justify-center text-white font-semibold text-sm">
                                {{ strtoupper(substr($msg['from'], 0, 1)) }}
                            </div>
                            <div class="absolute -bottom-1 -right-1 w-5 h-5 bg-purple-600 dark:bg-purple-500 rounded-full flex items-center justify-center border-2 border-white dark:border-gray-800">
                                <i class="fa-solid fa-mask text-white text-[8px]"></i>
                            </div>
                        </div>

                        <!-- Message Preview -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-1">
                                <span class="font-semibold text-gray-900 dark:text-white text-sm">
                                    {{ $msg['from'] }}
                                </span>
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-purple-100 dark:bg-purple-900/50 text-purple-700 dark:text-purple-300">
                                    <i class="fa-solid fa-mask mr-1 text-[10px]"></i>
                                    Secret
                                </span>
                                <i class="fa-solid fa-image text-purple-400 dark:text-purple-500 text-xs"></i>
                            </div>
                            <p class="text-sm text-gray-600 dark:text-gray-400 truncate">
                                {{ Str::limit($msg['message'], 100) }}
                            </p>
                        </div>

                        <!-- Time & Arrow -->
                        <div class="flex-shrink-0 flex items-center gap-4">
                            <span class="text-xs text-gray-500 dark:text-gray-400">
                                {{ \Carbon\Carbon::parse($msg['time'])->diffForHumans() }}
                            </span>
                            <i class="fa-solid fa-chevron-down text-gray-400 dark:text-gray-500 text-xs transition-transform duration-200" 
                               id="arrow-{{ $index }}"></i>
                        </div>
                    </div>

                    <!-- Expanded Message Content -->
                    <div id="message-{{ $index }}" class="hidden px-6 pb-6 pt-2 border-t border-gray-100 dark:border-gray-700/50">
                        <!-- Full Message -->
                        <div class="bg-purple-50 dark:bg-purple-900/20 rounded-lg p-4 mb-4 border border-purple-200 dark:border-purple-800">
                            <div class="flex items-start gap-3 mb-4">
                                <div class="w-12 h-12 rounded-full bg-gradient-to-br from-purple-500 to-purple-600 dark:from-purple-400 dark:to-purple-500 flex items-center justify-center text-white font-semibold relative">
                                    {{ strtoupper(substr($msg['from'], 0, 1)) }}
                                    <div class="absolute -bottom-1 -right-1 w-5 h-5 bg-purple-600 dark:bg-purple-500 rounded-full flex items-center justify-center border-2 border-purple-50 dark:border-purple-900/20">
                                        <i class="fa-solid fa-mask text-white text-[8px]"></i>
                                    </div>
                                </div>
                                <div class="flex-1">
                                    <div class="flex items-center justify-between mb-1">
                                        <span class="font-semibold text-gray-900 dark:text-white">
                                            {{ $msg['from'] }}
                                        </span>
                                        <span class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ \Carbon\Carbon::parse($msg['time'])->format('M d, Y h:i A') }}
                                        </span>
                                    </div>
                                    <span class="text-xs text-purple-600 dark:text-purple-400 font-medium">
                                        <i class="fa-solid fa-shield-halved mr-1"></i>
                                        Secret message extracted and decrypted
                                    </span>
                                </div>
                            </div>
                            
                            <div class="text-gray-700 dark:text-gray-300 leading-relaxed whitespace-pre-wrap">
                                {{ $msg['message'] }}
                            </div>
                        </div>

                        <!-- Stego Image -->
                        @if(isset($msg['image_path']) && $msg['image_path'])
                            <div class="mb-4">
                                <h4 class="text-xs font-semibold text-gray-700 dark:text-gray-300 mb-2 uppercase tracking-wide flex items-center gap-2">
                                    <i class="fa-solid fa-image text-purple-600 dark:text-purple-400"></i>
                                    Steganographic Image
                                    <span class="text-[10px] font-normal text-gray-500 dark:text-gray-400">(Message hidden inside)</span>
                                </h4>
                                <div class="border-2 border-purple-200 dark:border-purple-800 rounded-lg overflow-hidden bg-purple-50 dark:bg-purple-900/20 p-2">
                                    <a href="{{ asset('storage/' . $msg['image_path']) }}" 
                                       target="_blank"
                                       class="block rounded-lg overflow-hidden hover:opacity-90 transition-opacity duration-200">
                                        <img src="{{ asset('storage/' . $msg['image_path']) }}" 
                                             alt="Secret Image" 
                                             class="max-w-md w-full h-auto object-cover mx-auto">
                                    </a>
                                    <p class="text-xs text-center text-purple-600 dark:text-purple-400 mt-2">
                                        <i class="fa-solid fa-eye-slash mr-1"></i>
                                        The message is invisibly embedded in this image
                                    </p>
                                </div>
                            </div>
                        @endif

                        <!-- Action Buttons -->
                        <div class="flex items-center gap-2 pt-4 border-t border-purple-100 dark:border-purple-800/50">
                            <a href="{{ route('secret.download', $msg['id']) }}" 
                               class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-purple-700 dark:text-purple-300 bg-purple-50 dark:bg-purple-900/30 border border-purple-200 dark:border-purple-800 rounded-lg hover:bg-purple-100 dark:hover:bg-purple-900/50 transition-colors duration-200">
                                <i class="fa-solid fa-download text-xs"></i>
                                Download Image
                            </a>
                            @if(isset($msg['id']))
                                <form action="{{ route('secret.delete', $msg['id']) }}" method="POST" class="ml-auto" onsubmit="return confirm('Are you sure you want to delete this secret message?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-red-700 dark:text-red-400 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors duration-200">
                                        <i class="fa-solid fa-trash text-xs"></i>
                                        Delete
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <!-- Empty State -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-12 text-center transition-colors duration-200">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-purple-100 dark:bg-purple-900/30 rounded-full mb-4">
                <i class="fa-solid fa-mask text-2xl text-purple-600 dark:text-purple-400"></i>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">No secret messages yet</h3>
            <p class="text-gray-600 dark:text-gray-400 mb-6">You haven't received any secret messages. They will appear here when someone sends you one.</p>
            <a href="{{ route('create') }}" 
               class="inline-flex items-center gap-2 px-6 py-3 bg-purple-600 hover:bg-purple-700 dark:bg-purple-500 dark:hover:bg-purple-600 text-white font-semibold rounded-lg transition-colors duration-200">
                <i class="fa-solid fa-plus"></i>
                <span>Send Secret Message</span>
            </a>
        </div>
    @endif
</div>

<script>
function toggleMessage(index) {
    const messageContent = document.getElementById(`message-${index}`);
    const arrow = document.getElementById(`arrow-${index}`);
    
    if (messageContent.classList.contains('hidden')) {
        // Close all other messages
        document.querySelectorAll('[id^="message-"]').forEach(el => {
            if (el.id !== `message-${index}`) {
                el.classList.add('hidden');
            }
        });
        document.querySelectorAll('[id^="arrow-"]').forEach(el => {
            if (el.id !== `arrow-${index}`) {
                el.style.transform = 'rotate(0deg)';
            }
        });
        
        // Open clicked message
        messageContent.classList.remove('hidden');
        arrow.style.transform = 'rotate(180deg)';
    } else {
        // Close clicked message
        messageContent.classList.add('hidden');
        arrow.style.transform = 'rotate(0deg)';
    }
}
</script>
@endsection