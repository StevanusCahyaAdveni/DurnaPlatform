<div>
    <flux:heading size="xl">{{ $exam->exam_name }}</flux:heading>
    <flux:badge variant="pill" color="blue" size="sm">{{ $exam->classGroup->class_name }}</flux:badge>
    <flux:badge variant="pill" color="red" size="sm" icon="calendar">{{ \Carbon\Carbon::parse($exam->exam_deadline)->format('d M Y H:i') }}</flux:badge>
    <flux:navbar class="flex justify-between md:justify-start lg:justify-start items-center">
        <flux:tooltip content="Home" position="bottom">
            <flux:navbar.item :current="$layoutStatus === 'home'" wire:click="changeLayoutStatus('home')" icon="home"></flux:navbar.item>
        </flux:tooltip>
        <flux:tooltip content="Questions" position="bottom">
            <flux:navbar.item :current="$layoutStatus === 'questions'" wire:click="changeLayoutStatus('questions')" icon="numbered-list"></flux:navbar.item>
        </flux:tooltip>
        <flux:tooltip content="People" position="bottom">
            <flux:navbar.item :current="$layoutStatus === 'people'" wire:click="changeLayoutStatus('people')" icon="users"></flux:navbar.item>
        </flux:tooltip> 
    </flux:navbar>
    <hr>
    <br>
    @if($layoutStatus === 'home')
        <flux:text>{{ $exam->exam_description }}</flux:text>
    @elseif($layoutStatus === 'questions')
        <flux:modal.trigger name="addQuestion">
            <flux:badge variant="pill" icon="plus-circle" size="sm" color="green" class="mb-3" wire:click="upDataForUpdate('')">Add Question</flux:badge>
        </flux:modal.trigger>
        <flux:modal name="addQuestion" class="md:w-96">
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">Add Question</flux:heading>
                </div>
                <form wire:submit.prevent="saveQuestion" enctype="multipart/form-data">
                    <flux:textarea placeholder="Your question" wire:model="question_text" label="Your Question" size="sm" rows="2" class="mb-2"></flux:textarea>
                    <div class="grid grid-cols-2 gap-1 mb-2">
                        <flux:select wire:model="question_type" size="sm" label="Question Type" required>
                            <option value="" selected>Question Type</option>
                            <option value="multiple_choice">Multiple Choice</option>
                            <option value="short_answer">Short Answer</option>
                        </flux:select>
                        <flux:input label="Point" placeholder="Point" size="sm" type="number" wire:model="point" min="1" max="100" />
                    </div>
                    <flux:input label="Files (Images Only)" size="sm" wire:model="files" placeholder="Upload files" type="file" multiple accept="image/*" />
                    
                    {{-- Show file count when files are selected --}}
                    @if($files)
                        <small class="text-green-600 mt-1">{{ count($files) }} file(s) selected</small>
                    @endif
                    
                    <div class="flex" wire:loading.remove wire:target="saveQuestion">
                        <flux:spacer />
                        <flux:button type="submit" variant="primary">{{ $isEditMode ? 'Update Question' : 'Save Question' }}</flux:button>
                    </div>
                    
                    {{-- Loading indicator --}}
                    <div class="flex" wire:loading wire:target="saveQuestion">
                        <flux:spacer />
                        <flux:button variant="primary" disabled>
                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Processing...
                        </flux:button>
                    </div>
                </form>
            </div>
        </flux:modal>

        <flux:input size="sm" placeholder="Searching..." wire:model.live="questionSearch" class="mb-3"></flux:input>

        {{-- Success/Error Messages --}}
        @if (session()->has('success'))
            <flux:callout variant="success" class="mb-4">
                {{ session('success') }}
            </flux:callout>
        @endif

        @if (session()->has('error'))
            <flux:callout variant="danger" class="mb-4">
                {{ session('error') }}
            </flux:callout>
        @endif

        {{-- Questions List --}}
        @if(isset($questions) && $questions->count() > 0)
            <div class="space-y-4">
                @foreach($questions as $question)
                    <flux:callout class=" shadow-sm" lazy='on-load'>
                        <div class="flex justify-between items-start mb-2">
                            <div class="flex-1">
                                <flux:text>{{ $question->question_text }}</flux:text>
                                <div class="flex gap-2 mt-2">
                                    <flux:badge variant="pill" color="blue" size="sm">{{ ucfirst(str_replace('_', ' ', $question->question_type)) }}</flux:badge>
                                    <flux:badge variant="pill" color="green" size="sm">{{ $question->point }} Point{{ $question->point > 1 ? 's' : '' }}</flux:badge>
                                </div>
                            </div>
                        </div>

                        {{-- Question Media --}}
                        @if($question->media->count() > 0)
                            <div class="mt-1">
                                <p class="text-sm  mb-2">Attached Files:</p>
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                                    @foreach($question->media as $media)
                                        <div class="relative group">
                                            <img src="{{ asset('storage/examQuestion/' . $media->media_name) }}" 
                                                 alt="Question media" 
                                                 class="w-full h-20 object-cover rounded border">
                                            <button wire:click="deleteMedia('{{ $media->id }}')" 
                                                    wire:confirm="Delete this image?" 
                                                    class="absolute top-1 right-1 bg-red-500 text-white rounded-full p-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                </svg>
                                            </button>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        {{-- Question Options (if multiple choice) --}}
                        @if($question->question_type === 'multiple_choice')
                            <div class="mt-1">
                                <div class="flex justify-between items-center mb-2">
                                    <flux:text class="text-sm">Options:</flux:text>
                                    <flux:modal.trigger name="manageOptions">
                                        <flux:button variant="primary" size="sm" icon="plus" color="blue" wire:click="openOptionModal('{{ $question->id }}')">
                                            Manage Options
                                        </flux:button>
                                    </flux:modal.trigger>
                                </div>
                                
                                @if($question->options->count() > 0)
                                    <div class="space-y-2">
                                        @foreach($question->options as $option)
                                            <div class="flex items-start gap-1 rounded border p-3">
                                                <div class="w-5 h-5 rounded-full mt-0.5 {{ $option->is_correct ? 'bg-green-500 ' : '' }}">
                                                    @if($option->is_correct)
                                                        
                                                    @endif
                                                </div>
                                                <div class="flex-1">
                                                    <flux:text class="text-sm {{ $option->is_correct ? 'font-medium text-green-700' : '' }}">{{ $option->option_text }}</flux:text>
                                                    
                                                    {{-- Option Media --}}
                                                    @if($option->media->count() > 0)
                                                        <div class="mt-2">
                                                            <div class="grid grid-cols-3 gap-2">
                                                                @foreach($option->media as $media)
                                                                    <div class="relative group">
                                                                        <img src="{{ asset('storage/examQuestion/' . $media->media_name) }}" 
                                                                             alt="Option media" 
                                                                             class="w-full h-16 object-cover rounded border">
                                                                        <button wire:click="deleteOptionMedia('{{ $media->id }}')" 
                                                                                wire:confirm="Delete this option image?" 
                                                                                class="absolute top-1 right-1 bg-red-500 text-white rounded-full p-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                                            </svg>
                                                                        </button>
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="flex gap-1">
                                                    <flux:modal.trigger name="manageOptions">
                                                        <flux:button variant="ghost" size="sm" icon="pencil" color="blue" 
                                                                   wire:click="editOption('{{ $option->id }}')">
                                                        </flux:button>
                                                    </flux:modal.trigger>
                                                    <flux:button variant="ghost" size="sm" icon="trash" color="red" 
                                                               wire:click="deleteOption('{{ $option->id }}')" 
                                                               wire:confirm="Are you sure you want to delete this option?">
                                                    </flux:button>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <p class="text-sm text-gray-500 italic">No options added yet. Click "Manage Options" to add options.</p>
                                @endif
                            </div>
                        @endif
                        <div class="flex gap-2">
                            <flux:modal.trigger name="addQuestion">
                                <flux:button variant="primary" color="green" size="sm" icon="pencil-square" wire:click="upDataForUpdate('{{ $question->id }}')">Edit</flux:button>
                            </flux:modal.trigger>
                            <flux:button variant="danger" size="sm" icon="trash" color="red" 
                                       wire:click="deleteQuestion('{{ $question->id }}')" 
                                       wire:confirm="Are you sure you want to delete this question?">Delete</flux:button>
                        </div>
                    </flux:callout>
                @endforeach

                {{-- Pagination --}}
                <div class="mt-4">
                    {{ $questions->links() }}
                </div>
            </div>
        @else
            <div class="text-center py-8">
                <div class="text-gray-500">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No questions</h3>
                    <p class="mt-1 text-sm text-gray-500">Get started by creating a new question.</p>
                </div>
            </div>
        @endif
    @elseif($layoutStatus === 'people')
    @endif

    {{-- Option Management Modal --}}
    <flux:modal name="manageOptions" variant="flyout" class="md:w-96">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{ $isEditingOption ? 'Edit Option' : 'Add Option' }}</flux:heading>
                <flux:subheading>{{ $isEditingOption ? 'Update the option details below' : 'Create a new option for this question' }}</flux:subheading>
            </div>

            <form wire:submit.prevent="saveOption" class="space-y-4">
                {{-- Option Text --}}
                <flux:field>
                    <flux:label>Option Text</flux:label>
                    <flux:textarea wire:model="option_text" placeholder="Enter option text..." rows="3" resize="none" />
                    <flux:error name="option_text" />
                </flux:field>

                {{-- Is Correct Option --}}
                <flux:field>
                    <flux:label>Correct Answer</flux:label>
                    <flux:checkbox wire:model="is_correct">This is the correct answer</flux:checkbox>
                    <flux:error name="is_correct" />
                </flux:field>

                {{-- Option Files --}}
                <flux:field>
                    <flux:label>Option Images (Optional)</flux:label>
                    <flux:input type="file" wire:model="option_files" multiple accept="image/*" />
                    <flux:description>You can upload multiple images. Supported formats: JPEG, PNG, JPG, GIF, WEBP. Max size: 2MB each.</flux:description>
                    <flux:error name="option_files.*" />
                    
                    {{-- File Counter --}}
                    @if(!empty($option_files))
                        <div class="mt-2 text-sm text-gray-600">
                            {{ count($option_files) }} file(s) selected
                        </div>
                    @endif
                </flux:field>

                {{-- Action Buttons --}}
                <div class="flex justify-end space-x-2 pt-4 border-t">
                    <flux:modal.close>
                        <flux:button variant="ghost" type="button">Cancel</flux:button>
                    </flux:modal.close>
                    
                    <flux:button type="submit" variant="primary" wire:loading.remove wire:target="saveOption">
                        {{ $isEditingOption ? 'Update Option' : 'Add Option' }}
                    </flux:button>
                    
                    {{-- Loading indicator --}}
                    <div class="flex" wire:loading wire:target="saveOption">
                        <flux:spacer />
                        <flux:button variant="primary" disabled>
                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Processing...
                        </flux:button>
                    </div>
                </div>
            </form>
        </div>
    </flux:modal>
</div>
