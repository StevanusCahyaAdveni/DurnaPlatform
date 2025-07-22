<div>
    <flux:heading size="xl">{{ $singleTask->task_name }}</flux:heading>
    <flux:button size="xs" href="/user-class-detail/{{ $singleTask->class_group_id }}" class="my-2" variant="filled" icon="arrow-left" wire:navigate>Back to Class</flux:button>

    <hr>

    <flux:text class="text-xs">{{ $singleTask->class_name }}</flux:text>
    <flux:text class="mb-2"><b>Deadline: {{ $singleTask->task_deadline }}</b></flux:text>
    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
        @foreach ($getTaskMedia as $media)
        <div class="w-full h-96 bg-gray-100 rounded-lg shadow-sm overflow-hidden flex flex-col">
            <iframe 
              src="{{ asset('storage/task_media') }}/{{ $media->media_name }}" 
              class="w-full h-full"
              frameborder="0"
              allowfullscreen>
            </iframe>
            <flux:button size="sm" variant="primary"  href="{{ asset('storage/task_media') }}/{{ $media->media_name }}" class="my-0" icon="arrow-down-tray" target="_blank">Download</flux:button>
        </div>
        @endforeach
    </div>
    <flux:callout class="p-0 mt-2 mb-4">
        <flux:callout.text class="p-0">
            {!!$singleTask->task_description!!}
        </flux:callout.text>
    </flux:callout>

    <hr class="mb-4">
    @if(session()->has('message'))
        <flux:callout variant="info" class="mb-4">
            <flux:callout.text>
                {{ session('message') }}
            </flux:callout.text>
        </flux:callout>
    @endif

    @if($getAnswer == null || $getAnswerMedia == null || $getAnswer->count() == 0)
        <form method="post" wire:submit.prevent="submitTaskAnswer" enctype="multipart/form-data">
            <flux:input type="file" label="Media Answer" wire:model.live="task_media" multiple class="mb-2"/>
            <div wire:loading wire:target="task_media" class="text-blue-500 text-sm mt-2">Uploading file(s)...</div>
            <label for="answer_text_editor" class="block text-sm font-bold mb-2">Deskripsi Tugas:</label>
            <div
                wire:ignore
                x-data="{
                    editor: null,
                    content: @entangle('answer_text'),
                    init() {
                        const ckeditorElement = this.$refs.ckeditor;

                        ClassicEditor
                            .create(ckeditorElement, {
                                ckfinder: {
                                    uploadUrl: '{{ route('ckeditor.upload-image') }}?_token={{ csrf_token() }}',
                                },
                                toolbar: {
                                    items: [
                                        'heading', '|', 'bold', 'italic', 'link', 'bulletedList', 'numberedList', 'blockQuote', '|',
                                        'insertTable', 'mediaEmbed', 'undo', 'redo'
                                    ]
                                },
                                image: {
                                    toolbar: [
                                        'imageTextAlternative', 'imageStyle:inline', 'imageStyle:block', 'imageStyle:side'
                                    ]
                                }
                            })
                            .then(editor => {
                                this.editor = editor;
                                // Set initial theme
                                function setCkeditorTheme(isDark) {
                                    const editable = editor.ui.view.editable.element;
                                    if (isDark) {
                                        editable.style.backgroundColor = '#18181b';
                                        editable.style.color = '#f3f4f6';
                                        // CKEditor uses .ck-editor__main and .ck-content for the editable area
                                        editable.classList.add('dark-bg');
                                    } else {
                                        editable.style.backgroundColor = '#fff';
                                        editable.style.color = '#18181b';
                                        editable.classList.remove('dark-bg');
                                    }
                                }

                                // Detect theme on load
                                const isDark = document.documentElement.classList.contains('dark');
                                setCkeditorTheme(isDark);

                                // Listen for theme changes (Tailwind dark mode)
                                const observer = new MutationObserver(() => {
                                    const isDarkNow = document.documentElement.classList.contains('dark');
                                    setCkeditorTheme(isDarkNow);
                                });
                                observer.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });

                                // Also listen for focus/blur to re-apply theme (CKEditor resets styles on focus)
                                editor.ui.view.editable.element.addEventListener('focus', () => {
                                    const isDarkNow = document.documentElement.classList.contains('dark');
                                    setCkeditorTheme(isDarkNow);
                                });
                                editor.ui.view.editable.element.addEventListener('blur', () => {
                                    const isDarkNow = document.documentElement.classList.contains('dark');
                                    setCkeditorTheme(isDarkNow);
                                });

                                editor.model.document.on('change:data', () => {
                                    this.content = editor.getData();
                                });

                                Livewire.on('resetCkeditor', () => {
                                    editor.setData('');
                                });
                            })
                            .catch(error => {
                                console.error('Error initializing CKEditor:', error);
                            });
                    }
                }"
            >
                <textarea x-ref="ckeditor" wire:model="answer_text" id="answer_text_editor" class="border rounded-lg p-3 w-full" style="min-height: 200px;">{!! $answer_text !!}</textarea>
            </div>
            <flux:button wire:loading.attr="disabled" type="submit" variant="primary" class="mt-2">Submit Answer</flux:button>
        </form>
    @else
        <div class="mb-4">
            <div class="flex">
                <flux:heading size="lg">Your Answer <sup><flux:badge size="sm">{{ $singleAnswerPoint->point ?? '?'}}</flux:badge></sup>:</flux:heading>
                @if($singleTask->task_deadline >= now())
                    @if($formStatus == '1')
                        <flux:button type="submit" wire:click="changeForm('2')" variant="primary" color="green" icon="pencil" size="xs" class="flex items-center ml-auto">Edit Answer</flux:button>
                    @elseif($formStatus == '2')
                        <flux:button type="submit" wire:click="changeForm('1')" variant="filled" size="xs" class="flex items-center ml-auto">Cancel Edit</flux:button>
                    @endif
                @endif
            </div>

            @if($formStatus == '1')
                {{-- Jawaban Soal yang sudah diajawa  --}}
                <div>
                    <div class="max-w-full overflow-x-auto">
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2 mt-2">
                            @foreach ($getAnswerMedia as $media)
                            <div class="w-full max-w-md h-96 bg-gray-100 rounded-lg shadow-sm overflow-hidden flex flex-col">
                                <iframe 
                                    src="{{ asset('storage/task_media') }}/{{ $media->media_name }}"
                                    class="w-full h-full"
                                    frameborder="0"
                                    allowfullscreen>
                                </iframe>
                                <flux:button.group class="w-full">
                                    <flux:button size="xs" variant="primary" href="{{ asset('storage/task_answer_media') }}/{{ $media->media_name }}" class="my-0 w-full" icon="arrow-down-tray" target="_blank"></flux:button>
                                    {{-- <flux:button size="xs" variant="danger" class="my-0 w-full" icon="trash" wire:confirm="Are you sure to delete this data ?" wire:click="deleteAnswerMedia('{{ $media->id }}')"></flux:button> --}}
                                </flux:button.group>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    <br>
                    <div class="max-w-full overflow-x-auto">
                        {!! $getAnswer->answer_text !!}
                    </div>                
                </div>
                <hr class="mb-2">    
                <div class="flex">
                    <flux:spacer />
                    <flux:modal.trigger name="add-coments" class="mt-2">
                        <flux:button size="xs" variant="primary">Add Coments</flux:button>
                    </flux:modal.trigger>
                </div>    
                <flux:modal name="add-coments" class="md:w-96">
                    <div class="space-y-6">
                        <div>
                            <flux:heading size="lg">Add Coments</flux:heading>
                        </div>
                        <flux:input type="file" wire:model="commentMedia" label="Upload Media (Optional)" class="mb-4"/>
                        <flux:textarea label="Add Coments" wire:model="commentText" placeholder="Coments about this answer" class="mb-2"/>
                        <div class="flex">
                            <flux:spacer />
                            <flux:button type="button" size="sm" x-on:click="$flux.modals().close()"  wire:click="addComent()" variant="primary">Save changes</flux:button>
                        </div>
                    </div>
                </flux:modal>
                @foreach($taskAnswersComments as $comment)
                    <div class="flex items-center gap-1 mt-3">
                        <flux:avatar name="{{ $comment->commenter_name }}" size="xs"/>
                        <div>
                            <flux:heading>{{ $comment->commenter_name}}</flux:heading>
                        </div>
                    </div>
                    @if($comment->comment_media)
                    <flux:callout class="mt-2" style="max-width: 200px;">
                        <flux:button variant="primary" target="_blank" size="xs" href="{{ asset('storage/task_media') }}/{{ $comment->comment_media }}" frameborder="0" icon="eye">Show Media</flux:button>
                    </flux:callout>
                    @endif
                    <flux:text class="text-sm mt-2">{{ $comment->comment_text }}</flux:text>
                @endforeach
                {{-- End Jawaban Soal yang sudah diajawa  --}}
                @elseif($formStatus == '2')
                <div>
                    <div class="max-w-full overflow-x-auto">
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2 mt-2">
                            @foreach ($getAnswerMedia as $media)
                            <div class="w-full max-w-md h-96 bg-gray-100 rounded-lg shadow-sm overflow-hidden flex flex-col">
                                <iframe 
                                    src="{{ asset('storage/task_media') }}/{{ $media->media_name }}"
                                    class="w-full h-full"
                                    frameborder="0"
                                    allowfullscreen>
                                </iframe>
                                <flux:button.group class="w-full">
                                    <flux:button size="xs" variant="primary" href="{{ asset('storage/task_answer_media') }}/{{ $media->media_name }}" class="my-0 w-full" icon="arrow-down-tray" target="_blank"></flux:button>
                                    <flux:button size="xs" variant="danger" class="my-0 w-full" icon="trash" wire:confirm="Are you sure to delete this data ?" wire:click="deleteAnswerMedia('{{ $media->id }}')"></flux:button>
                                </flux:button.group>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    <br>
                    <form method="post" wire:submit.prevent="submitTaskAnswer" enctype="multipart/form-data">
                        <flux:input type="file" label="Media Answer" wire:model.live="task_media" multiple class="mb-2"/>
                        <div wire:loading wire:target="task_media" class="text-blue-500 text-sm mt-2">Uploading file(s)...</div>
                        <label for="answer_text_editor" class="block text-sm font-bold mb-2">Deskripsi Tugas:</label>
                        <div
                            wire:ignore
                            x-data="{
                                editor: null,
                                content: @entangle('answer_text'),
                                init() {
                                    const ckeditorElement = this.$refs.ckeditor;
            
                                    ClassicEditor
                                        .create(ckeditorElement, {
                                            ckfinder: {
                                                uploadUrl: '{{ route('ckeditor.upload-image') }}?_token={{ csrf_token() }}',
                                            },
                                            toolbar: {
                                                items: [
                                                    'heading', '|', 'bold', 'italic', 'link', 'bulletedList', 'numberedList', 'blockQuote', '|',
                                                    'insertTable', 'mediaEmbed', 'undo', 'redo'
                                                ]
                                            },
                                            image: {
                                                toolbar: [
                                                    'imageTextAlternative', 'imageStyle:inline', 'imageStyle:block', 'imageStyle:side'
                                                ]
                                            }
                                        })
                                        .then(editor => {
                                            this.editor = editor;
                                            // Set initial theme
                                            function setCkeditorTheme(isDark) {
                                                const editable = editor.ui.view.editable.element;
                                                if (isDark) {
                                                    editable.style.backgroundColor = '#18181b';
                                                    editable.style.color = '#f3f4f6';
                                                    // CKEditor uses .ck-editor__main and .ck-content for the editable area
                                                    editable.classList.add('dark-bg');
                                                } else {
                                                    editable.style.backgroundColor = '#fff';
                                                    editable.style.color = '#18181b';
                                                    editable.classList.remove('dark-bg');
                                                }
                                            }
            
                                            // Detect theme on load
                                            const isDark = document.documentElement.classList.contains('dark');
                                            setCkeditorTheme(isDark);
            
                                            // Listen for theme changes (Tailwind dark mode)
                                            const observer = new MutationObserver(() => {
                                                const isDarkNow = document.documentElement.classList.contains('dark');
                                                setCkeditorTheme(isDarkNow);
                                            });
                                            observer.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
            
                                            // Also listen for focus/blur to re-apply theme (CKEditor resets styles on focus)
                                            editor.ui.view.editable.element.addEventListener('focus', () => {
                                                const isDarkNow = document.documentElement.classList.contains('dark');
                                                setCkeditorTheme(isDarkNow);
                                            });
                                            editor.ui.view.editable.element.addEventListener('blur', () => {
                                                const isDarkNow = document.documentElement.classList.contains('dark');
                                                setCkeditorTheme(isDarkNow);
                                            });
            
                                            editor.model.document.on('change:data', () => {
                                                this.content = editor.getData();
                                            });
            
                                            Livewire.on('resetCkeditor', () => {
                                                editor.setData('');
                                            });
                                        })
                                        .catch(error => {
                                            console.error('Error initializing CKEditor:', error);
                                        });
                                }
                            }"
                        >
                            <textarea x-ref="ckeditor" wire:model="answer_text" id="answer_text_editor" class="border rounded-lg p-3 w-full" style="min-height: 200px;">{!! $answer_text !!}</textarea>
                        </div>
                        <flux:button wire:loading.attr="disabled" type="submit" variant="primary" class="mt-2">Submit Answer</flux:button>
                    </form>          
                </div>
            @endif

        </div>
    @endif


</div>
<script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js"></script>

