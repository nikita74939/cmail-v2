@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto">
    <!-- Header -->
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Inbox</h1>
        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
            {{ isset($decryptedMessages) ? $decryptedMessages->count() : 0 }} messages
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
                            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-purple-500 to-purple-600 dark:from-purple-400 dark:to-purple-500 flex items-center justify-center text-white font-semibold text-sm">
                                {{ strtoupper(substr($msg['from'], 0, 1)) }}
                            </div>
                        </div>

                        <!-- Message Preview -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-1">
                                <span class="font-semibold text-gray-900 dark:text-white text-sm">
                                    {{ $msg['from'] }}
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
                                <div class="w-12 h-12 rounded-full bg-gradient-to-br from-purple-500 to-purple-600 dark:from-purple-400 dark:to-purple-500 flex items-center justify-center text-white font-semibold">
                                    {{ strtoupper(substr($msg['from'], 0, 1)) }}
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
                                    <span class="text-xs text-gray-500 dark:text-gray-400">
                                        to me
                                    </span>
                                </div>
                            </div>
                            
                            <div class="text-gray-700 dark:text-gray-300 leading-relaxed whitespace-pre-wrap">
                                {{ $msg['message'] }}
                            </div>
                        </div>

                        <!-- Attachments -->
                        @if(isset($msg['file_path']) && $msg['file_path'])
                            <div class="mb-4">
                                <h4 class="text-xs font-semibold text-gray-700 dark:text-gray-300 mb-2 uppercase tracking-wide">
                                    Attachments
                                </h4>
                                <div class="flex flex-wrap gap-2">
                                    <!-- File Attachment -->
                                    @if(isset($msg['file_name']))
                                        <a href="{{ asset('storage/' . $msg['file_path']) }}" 
                                           download="{{ $msg['file_name'] }}"
                                           class="flex items-center gap-3 px-4 py-3 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-lg hover:border-purple-500 dark:hover:border-purple-400 transition-colors duration-200 group">
                                            <div class="flex-shrink-0 w-10 h-10 bg-purple-100 dark:bg-purple-900/30 rounded-lg flex items-center justify-center">
                                                <i class="fa-solid fa-file text-purple-600 dark:text-purple-400"></i>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                                    {{ $msg['file_name'] }}
                                                </p>
                                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                                    {{ isset($msg['file_size']) ? $msg['file_size'] : 'File' }}
                                                </p>
                                            </div>
                                            <i class="fa-solid fa-download text-gray-400 group-hover:text-purple-600 dark:group-hover:text-purple-400 transition-colors duration-200"></i>
                                        </a>
                                    @endif

                                    <!-- Image Attachment -->
                                    @if(isset($msg['image_path']) && $msg['image_path'])
                                        <div class="w-full">
                                            <a href="{{ asset('storage/' . $msg['image_path']) }}" 
                                               target="_blank"
                                               class="block rounded-lg overflow-hidden border border-gray-200 dark:border-gray-600 hover:border-purple-500 dark:hover:border-purple-400 transition-colors duration-200">
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
                            <button onclick="replyMessage('{{ $msg['from'] }}')" 
                                    class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200">
                                <i class="fa-solid fa-reply text-xs"></i>
                                Reply
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
                <i class="fa-solid fa-inbox text-2xl text-gray-400 dark:text-gray-500"></i>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">No messages yet</h3>
            <p class="text-gray-600 dark:text-gray-400 mb-6">Your inbox is empty. Messages will appear here when you receive them.</p>
            <a href="{{ route('create') }}" 
               class="inline-flex items-center gap-2 px-6 py-3 bg-purple-600 hover:bg-purple-700 dark:bg-purple-500 dark:hover:bg-purple-600 text-white font-semibold rounded-lg transition-colors duration-200">
                <i class="fa-solid fa-plus"></i>
                <span>Create New Message</span>
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

function replyMessage(sender) {
    // Redirect to create message page with sender pre-filled
    window.location.href = "{{ route('create') }}?reply_to=" + encodeURIComponent(sender);
}

</script>
@endsection