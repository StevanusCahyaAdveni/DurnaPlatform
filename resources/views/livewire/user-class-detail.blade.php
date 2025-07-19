<div>
    <flux:heading size="xl">{{ $singleClass->class_name }}</flux:heading>
    <flux:navbar>
        <flux:navbar.item :current="$layoutStatus === 'tasks'" wire:click="changeLayoutStatus('tasks')" icon="book-open">Tasks</flux:navbar.item>
        <flux:navbar.item :current="$layoutStatus === 'chats'" wire:click="changeLayoutStatus('chats')" icon="chat-bubble-left-ellipsis">Chats</flux:navbar.item>
        <flux:navbar.item :current="$layoutStatus === 'exams'" wire:click="changeLayoutStatus('exams')" icon="clipboard-document-check">Exams</flux:navbar.item>
    </flux:navbar>
    <hr>
    @if ($layoutStatus === 'tasks')
    <br>
        <flux:input size="sm" placeholder="Searching..." wire:model.live="taskSearch" class="mb-3" />
        {{-- Show tasks layout --}}
        @foreach($classTask as $task)
        <flux:callout icon="book-open" class="mb-2">
            <flux:callout.heading  class="mb-0">{{ $task->task_name }}</flux:callout.heading>
            <flux:callout.text>
                <flux:text class="text-xs mt-0">{{ $task->class_name }} | {{ $task->name }}</flux:text>
                {!! Str::limit(strip_tags($task->task_description), 100) !!}...
                <div class="flex" >
                    <flux:spacer />
                    <flux:button size="sm" wire:navigate class="mt-3 mb-0" variant="ghost" href="/user-task-answer/{{ $task->id }}" variant="primary">Do Assignment</flux:button>
                </div>
            </flux:callout.text>
        </flux:callout>
        @endforeach
    @elseif ($layoutStatus === 'chats')
        <div class="">
            {{-- Replace <flux:heading> with standard HTML element and utility classes --}}    
            <div class="flex flex-col h-[75vh] rounded-lg overflow-hidden"> {{-- Fixed height 80vh --}}
                {{-- Chat Container with Alpine.js for Infinite Scroll --}}
                <div x-data="{
                        scrollContainer: null,
                        oldScrollHeight: 0, // Store old scroll height before loading more
                        isAtBottom: true, // New: Track if user is currently at the bottom
                        init() {
                            this.scrollContainer = this.$refs.chatMessages;
    
                            // Scroll to bottom on initial load, ensuring DOM is ready
                            this.$nextTick(() => {
                                this.scrollChatToBottom();
                            });
    
                            // Listener for Livewire 'messageSent' event (new message at bottom)
                            Livewire.on('messageSent', () => {
                                // Ensure scroll happens AFTER DOM update for new message
                                // ONLY scroll to bottom if the user was already at the bottom
                                this.$nextTick(() => {
                                    if (this.isAtBottom) {
                                        this.scrollChatToBottom();
                                    }
                                });
                            });
    
                            // Listener for scroll event to detect when to load more and update isAtBottom
                            this.scrollContainer.addEventListener('scroll', () => {
                                // Update isAtBottom state
                                // Check if scroll position is at the very bottom (with a small buffer)
                                this.isAtBottom = (this.scrollContainer.scrollTop + this.scrollContainer.clientHeight >= this.scrollContainer.scrollHeight - 10);
    
                                // Detect scroll to top to load more
                                if (this.scrollContainer.scrollTop === 0 && !@js($this->allMessagesLoaded) && !@js($this->isLoadingMore)) {
                                    // Store current scroll height BEFORE Livewire updates the DOM
                                    this.oldScrollHeight = this.scrollContainer.scrollHeight;
    
                                    // Call Livewire method to load more
                                    @this.dispatch('loadMore');
                                }
                            });
    
                            // Watch for isLoadingMore to change from true to false
                            // This indicates that new messages have been loaded from the server (older messages)
                            this.$watch('isLoadingMore', (newValue, oldValue) => {
                                if (oldValue === true && newValue === false) { // Loading just finished
                                    this.$nextTick(() => {
                                        const newScrollHeight = this.scrollContainer.scrollHeight;
                                        const addedHeight = newScrollHeight - this.oldScrollHeight;
                                        this.scrollContainer.scrollTop = addedHeight; // Adjust scroll position to maintain view
                                    });
                                }
                            });
                        }, // End of init()
                        scrollChatToBottom() {
                            if (this.scrollContainer) {
                                this.scrollContainer.scrollTop = this.scrollContainer.scrollHeight;
                            }
                        }
                    }"
                    class="flex-1 overflow-y-auto py-1 space-y-4"
                    x-ref="chatMessages" {{-- Reference for the chat container --}}
                    x-bind:wire:poll="isAtBottom ? '2000ms' : ''" {{-- Conditional wire:poll --}}
                >
                    {{-- Loading Indicator When Loading More --}}
                    <div x-show="@js($this->isLoadingMore)" class="text-center text-gray-500 py-2">
                        Loading old Message
                    </div>
    
                    {{-- Indicator If All Messages Have Been Loaded --}}
                    <div x-show="@js($this->allMessagesLoaded) && @js($classChat->isNotEmpty())" class="text-center text-gray-500 text-sm py-2">
                        All messages have been loaded
                    </div>
    
                    @foreach($classChat as $chatData)
                        <div class="flex items-start {{ $chatData->user_id === Auth::id() ? 'justify-end' : 'justify-start' }}">
                            @if($chatData->user_id !== Auth::id())
                                {{-- Other user's avatar --}}
                                <div class="flex-shrink-0 h-8 w-8 rounded-full bg-gray-300 flex items-center justify-center text-xs text-gray-600">
                                    {{ Str::upper(substr($chatData->user->name, 0, 2)) }}
                                </div>
                            @endif
                            <div class="ml-3 mr-3 max-w-[70%] {{ $chatData->user_id === Auth::id() ? 'bg-blue-500 text-white rounded-bl-xl rounded-tr-xl rounded-tl-xl' : 'bg-gray-100 text-gray-800 rounded-br-xl rounded-tr-xl rounded-tl-xl' }} p-3 shadow-sm">
                                <div class="text-sm font-medium {{ $chatData->user_id === Auth::id() ? 'text-white' : 'text-gray-900' }}">
                                    {{ $chatData->user->name }}
                                    <span class="text-xs {{ $chatData->user_id === Auth::id() ? 'text-blue-200' : 'text-gray-500' }} ml-2">{{ $chatData->created_at->diffForHumans() }}</span>
                                </div>
                                <div class="mt-1 text-sm {{ $chatData->user_id === Auth::id() ? 'text-white' : 'text-gray-700' }}">{{ $chatData->chat_text }}</div>
                            </div>
                            @if($chatData->user_id === Auth::id())
                                {{-- Current user's avatar --}}
                                <div class="flex-shrink-0 h-8 w-8 rounded-full bg-blue-600 flex items-center justify-center text-xs text-white">
                                    {{ Str::upper(substr($chatData->user->name, 0, 2)) }}
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
    
                <div class="border-t border-gray-200 p-2">
                    <form wire:submit.prevent="sendMessage">
                        <div class="flex items-center">
                            <div class="flex-grow">
                                {{-- Replace <flux:input> with standard HTML element and utility classes --}}
                                <input type="text"
                                       wire:model="newMessage"
                                       placeholder="Message..."
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                       x-ref="messageInput"> {{-- Add x-ref for auto-focus --}}
                            </div>
                            {{-- Replace <flux:button> with standard HTML element and utility classes --}}
                            <button type="submit" wire:loading.attr="disabled"
                                    class="ml-2 px-6 py-2 bg-blue-600 text-white font-semibold rounded-lg shadow-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                <i class="bi bi-send"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
    
            {{-- Ensure Alpine.js is loaded before this script if not already in app.js --}}
            {{-- <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script> --}}
    
            <script>
                // Alpine.js init handles scroll. No need for separate Livewire.on('messageSent') listener here.
                // The x-data init function in the chat container handles both initial scroll and scroll on new messages.
            </script>
        </div>
    @endif
</div>
