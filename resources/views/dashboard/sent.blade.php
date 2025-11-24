@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto">
    <!-- Header -->
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Sent</h1>
        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
            {{ isset($decryptedMessages) ? $decryptedMessages->count() : 0 }} messages sent
        </p>
    </div>

    @if(isset($decryptedMessages) && $decryptedMessages->isNotEmpty())
        <!-- Message List -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden transition-colors duration-200">
            @foreach($decryptedMessages as $index => $msg)
                <!-- Message Item (Collapsed) -->
                <div class="message-item border-b border-gray-200 dark:border-gray-700 last:border-b-0 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors duration-150 cursor-pointer" 
                     onclick="toggleMessage({{ $index }})">
                    <div class="flex items-center gap-4 px-6 py-4">
                        <!-- Avatar -->
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-green-500 to-green-600 dark:from-green-400 dark:to-green-500 flex items-center justify-center text-white font-semibold text-sm">
                                {{ strtoupper(substr($msg['to'], 0, 1)) }}
                            </div>
                        </div>

                        <!-- Message Preview -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-1">
                                <span class="text-xs text-gray-500 dark:text-gray-400 mr-1">To:</span>
                                <span class="font-semibold text-gray-900 dark:text-white text-sm">
                                    {{ $msg['to'] }}
                                </span>
                                @if(isset($msg['is_secret']) && $msg['is_secret'])
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-purple-100 dark:bg-purple-900/50 text-purple-700 dark:text-purple-300">
                                        <i class="fa-solid fa-mask mr-1 text-[10px]"></i>
                                        Secret
                                    </span>
                                @endif
                                @if(isset($msg['has_file']) && $msg['has_file'])
                                    <i class="fa-solid fa-paperclip text-gray-400 dark:text-gray-500 text-xs"></i>
                                @endif
                                @if(isset($msg['status']))
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium 
                                        {{ $msg['status'] === 'read' ? 'bg-blue-100 dark:bg-blue-900/50 text-blue-700 dark:text-blue-300' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300' }}">
                                        <i class="fa-solid {{ $msg['status'] === 'read' ? 'fa-check-double' : 'fa-check' }} mr-1 text-[10px]"></i>
                                        {{ ucfirst($msg['status']) }}
                                    </span>
                                @endif
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
                        <div class="bg-gray-50 dark:bg-gray-900/50 rounded-lg p-4 mb-4">
                            <div class="flex items-start gap-3 mb-4">
                                <div class="w-12 h-12 rounded-full bg-gradient-to-br from-green-500 to-green-600 dark:from-green-400 dark:to-green-500 flex items-center justify-center text-white font-semibold">
                                    {{ strtoupper(substr($msg['to'], 0, 1)) }}
                                </div>
                                <div class="flex-1">
                                    <div class="flex items-center justify-between mb-1">
                                        <div>
                                            <span class="text-xs text-gray-500 dark:text-gray-400">To: </span>
                                            <span class="font-semibold text-gray-900 dark:text-white">
                                                {{ $msg['to'] }}
                                            </span>
                                        </div>
                                        <span class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ \Carbon\Carbon::parse($msg['time'])->format('M d, Y h:i A') }}
                                        </span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs text-gray-500 dark:text-gray-400">from me</span>
                                        @if(isset($msg['status']))
                                            <span class="inline-flex items-center text-xs 
                                                {{ $msg['status'] === 'read' ? 'text-blue-600 dark:text-blue-400' : 'text-gray-500 dark:text-gray-400' }}">
                                                <i class="fa-solid {{ $msg['status'] === 'read' ? 'fa-check-double' : 'fa-check' }} mr-1"></i>
                                                {{ $msg['status'] === 'read' ? 'Read' : 'Delivered' }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            
                            <div class="text-gray-700 dark:text-gray-300 leading-relaxed whitespace-pre-wrap">
                                {{ $msg['message'] }}
                            </div>
                        </div>

                        <!-- Attachments -->
                        @if(isset($msg['file_path']) && $msg['file_path'] || isset($msg['image_path']) && $msg['image_path'])
                            <div class="mb-4">
                                <h4 class="text-xs font-semibold text-gray-700 dark:text-gray-300 mb-2 uppercase tracking-wide">
                                    Attachments
                                </h4>
                                <div class="flex flex-wrap gap-2">
                                    <!-- File Attachment -->
                                    @if(isset($msg['file_path']) && $msg['file_path'] && isset($msg['file_name']))
                                        <a href="{{ asset('storage/' . $msg['file_path']) }}" 
                                           download="{{ $msg['file_name'] }}"
                                           class="flex items-center gap-3 px-4 py-3 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-lg hover:border-green-500 dark:hover:border-green-400 transition-colors duration-200 group">
                                            <div class="flex-shrink-0 w-10 h-10 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center">
                                                @php
                                                    $extension = pathinfo($msg['file_name'], PATHINFO_EXTENSION);
                                                    $iconClass = match(strtolower($extension)) {
                                                        'pdf' => 'fa-file-pdf text-red-600 dark:text-red-400',
                                                        'doc', 'docx' => 'fa-file-word text-blue-600 dark:text-blue-400',
                                                        'xls', 'xlsx' => 'fa-file-excel text-green-600 dark:text-green-400',
                                                        'zip', 'rar' => 'fa-file-zipper text-yellow-600 dark:text-yellow-400',
                                                        'jpg', 'jpeg', 'png', 'gif' => 'fa-file-image text-purple-600 dark:text-purple-400',
                                                        default => 'fa-file text-gray-600 dark:text-gray-400'
                                                    };
                                                @endphp
                                                <i class="fa-solid {{ $iconClass }}"></i>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                                    {{ $msg['file_name'] }}
                                                </p>
                                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                                    {{ isset($msg['file_size']) ? $msg['file_size'] : '' }}
                                                </p>
                                            </div>
                                            <i class="fa-solid fa-download text-gray-400 group-hover:text-green-600 dark:group-hover:text-green-400 transition-colors duration-200"></i>
                                        </a>
                                    @endif

                                    <!-- Image Attachment -->
                                    @if(isset($msg['image_path']) && $msg['image_path'])
                                        <div class="w-full">
                                            <a href="{{ asset('storage/' . $msg['image_path']) }}" 
                                               target="_blank"
                                               class="block rounded-lg overflow-hidden border border-gray-200 dark:border-gray-600 hover:border-green-500 dark:hover:border-green-400 transition-colors duration-200">
                                                <img src="{{ asset('storage/' . $msg['image_path']) }}" 
                                                     alt="Attachment" 
                                                     class="max-w-md w-full h-auto object-cover">
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif

                        <!-- Action Buttons -->
                        <div class="flex items-center gap-2 pt-4 border-t border-gray-100 dark:border-gray-700/50">
                            <button onclick="resendMessage({{ $index }})" 
                                    class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200">
                                <i class="fa-solid fa-rotate-right text-xs"></i>
                                Resend
                            </button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <!-- Empty State -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-12 text-center transition-colors duration-200">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded-full mb-4">
                <i class="fa-solid fa-paper-plane text-2xl text-gray-400 dark:text-gray-500"></i>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">No sent messages</h3>
            <p class="text-gray-600 dark:text-gray-400 mb-6">Messages you send will appear here.</p>
            <a href="{{ route('create') }}" 
               class="inline-flex items-center gap-2 px-6 py-3 bg-purple-600 hover:bg-purple-700 dark:bg-purple-500 dark:hover:bg-purple-600 text-white font-semibold rounded-lg transition-colors duration-200">
                <i class="fa-solid fa-plus"></i>
                <span>Send Your First Message</span>
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

function resendMessage(index) {
    alert('Resend functionality - Message index: ' + index);
    // Implement resend logic here
}

</script>
@endsection