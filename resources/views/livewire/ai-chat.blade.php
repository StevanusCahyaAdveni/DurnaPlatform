<div>
    <div class="">
        <flux:heading size="xl" class="text-center mb-0">Si Durna AI</flux:heading>
        <flux:text class="mb-4 mt-0 text-center text-xs">The AI is made using the DeepSeekV3 model and will continue to develop</flux:text>

        {{-- Info User --}}
        <div class="mb-1 p-2 border border-blue-200 rounded-lg text-center">
            <small class="">
                Hai {{ Auth::user()->name }} ({{ Auth::user()->id }})
            </small>
        </div>

        {{-- Chat Controls --}}
        <div class="mb-1 flex justify-between items-center">
            {{-- <flux:text class="text-sm text-gray-600">
                Total pesan: {{ count($chatHistory) }}
            </flux:text> --}}
            @if(count($chatHistory) > 0)
            <flux:spacer></flux:spacer>
                <flux:button wire:click="clearChatHistory" 
                           wire:confirm="Yakin ingin menghapus semua riwayat chat?"
                           variant="ghost" 
                           size="sm"
                           icon="trash"
                           class="text-red-800 hover:text-red-800">
                    Delete All Chats
                </flux:button>
            @endif
        </div>

        {{-- Error Messages --}}
        @if (session()->has('error_message'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                {{ session('error_message') }}
            </div>
        @endif

        {{-- Chat Area --}}
        <div class="flex flex-col h-[60vh] border mb-0 mt-0 rounded-lg overflow-hidden shadow-sm">
            <div class="flex-1 overflow-y-auto p-4 space-y-4 " id="chatContainer">
                @if(empty($chatHistory))
                    <div class="text-center  py-8">
                        <p>No conversation yet. Start chatting with AI!</p>
                        <p class="text-xs mt-2">Messages will be saved until you log out</p>
                    </div>
                @endif

                @foreach($chatHistory as $message)
                    <div class="{{ $message['role'] === 'user' ? 'flex justify-end' : 'flex justify-start' }}">
                        <div class="{{ $message['role'] === 'user' ? 'bg-blue-500 text-white' : 'bg-white text-gray-800 border border-gray-200' }} max-w-[70%] p-3 rounded-lg shadow-sm">
                            <div class="text-xs font-semibold {{ $message['role'] === 'user' ? 'text-blue-100' : 'text-gray-600' }}">
                                {{ $message['role'] === 'user' ? 'Anda' : 'AI' }}
                            </div>
                            <div class="mt-1 text-sm">{!! nl2br(e($message['content'])) !!}</div>
                            <div class="text-right text-xs {{ $message['role'] === 'user' ? 'text-blue-200' : 'text-gray-500' }} mt-2">
                                {{ date('H:i', $message['timestamp']) }}
                            </div>
                        </div>
                    </div>
                @endforeach

                {{-- Loading Indicator --}}
                @if($isLoading)
                    <div class="flex justify-start">
                        <div class="bg-white border border-gray-200 text-gray-700 p-3 rounded-lg shadow-sm">
                            <div class="text-xs font-semibold text-gray-600 mb-1">AI</div>
                            <div class="flex items-center space-x-2">
                                <div class="flex space-x-1">
                                    <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce"></div>
                                    <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0.1s"></div>
                                    <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0.2s"></div>
                                </div>
                                <span class="text-sm text-gray-600">sedang mengetik...</span>
                            </div>
                        </div>
                    </div>
                @endif
                
                {{-- Invisible element to mark the bottom --}}
                <div id="chat-bottom"></div>
            </div>

            {{-- Input Area --}}
            <div class="border-t text-black border-gray-200 p-4 ">
                <form wire:submit.prevent="sendMessage">
                    <div class="flex items-center space-x-2">
                        <div class="flex-grow">
                            <flux:input type="text" 
                                      wire:model.defer="newMessage" 
                                      placeholder="Ketik pesan Anda..." 
                                      class="w-full text-black"
                                      wire:keydown.enter.prevent="sendMessage"
                                      required />
                        </div>
                        <flux:button type="submit" 
                                   wire:loading.attr="disabled" 
                                   variant="primary" 
                                   icon="paper-airplane"
                                   class="px-4 py-2">
                        </flux:button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Function to scroll to bottom smoothly
        function scrollToBottom() {
            const container = document.getElementById('chatContainer');
            const bottom = document.getElementById('chat-bottom');
            if (container && bottom) {
                // Use smooth scrolling
                bottom.scrollIntoView({ 
                    behavior: 'smooth', 
                    block: 'end' 
                });
            }
        }

        // Scroll to bottom on page load
        document.addEventListener('DOMContentLoaded', function() {
            // Delay to ensure content is rendered
            setTimeout(scrollToBottom, 100);
        });

        // Scroll to bottom after Livewire updates
        document.addEventListener('livewire:updated', function () {
            // Small delay to ensure DOM is updated
            setTimeout(scrollToBottom, 100);
        });

        // Scroll to bottom when loading state changes
        Livewire.hook('message.processed', (message, component) => {
            if (component.name === 'ai-chat') {
                setTimeout(scrollToBottom, 50);
            }
        });

        // Auto-focus input field after sending message
        document.addEventListener('livewire:updated', function () {
            const input = document.querySelector('input[wire\\:model\\.defer="newMessage"]');
            if (input && !document.querySelector('[wire\\:loading]')) {
                input.focus();
            }
        });

        // Handle Enter key press
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                const input = document.querySelector('input[wire\\:model\\.defer="newMessage"]');
                if (input && document.activeElement === input) {
                    e.preventDefault();
                    const form = input.closest('form');
                    if (form) {
                        form.dispatchEvent(new Event('submit', { bubbles: true, cancelable: true }));
                    }
                }
            }
        });
    </script>

    <style>
        /* Custom scrollbar for chat container */
        #chatContainer::-webkit-scrollbar {
            width: 6px;
        }
        
        #chatContainer::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 3px;
        }
        
        #chatContainer::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 3px;
        }
        
        #chatContainer::-webkit-scrollbar-thumb:hover {
            background: #a8a8a8;
        }

        /* Smooth animations for new messages */
        .space-y-4 > div {
            animation: fadeInUp 0.3s ease-out;
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</div>
