{{-- resources/views/livewire/teacher-task.blade.php --}}

<div>
    <flux:heading size="xl">Create Task for Your Class</flux:heading>
    @if($formStatus == '0')
        <div>
            <flux:button size="sm" wire:click="changeFormStatus('1')" variant="primary">Add Task</flux:button>
            <br>
            <br>
            <div class="p-2 shadow-xl rounded-lg">
                <flux:input wire:model.live="search" placeholder="search" class="" size="sm"/>
            </div>
            <flux:callout class="overflow-x-auto shadow-lg mt-2">
                <div class="p-2 rounded-lg">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm mb-3 border-collapse">
                            <thead class="">
                                <tr class="text-left uppercase text-xs">
                                    <th class="py-3 px-2 border-b border-gray-200">No</th>
                                    <th class="py-3 px-2 border-b border-gray-200">Class</th>
                                    <th class="py-3 px-2 border-b border-gray-200">Task Name</th>
                                    <th class="py-3 px-2 border-b border-gray-200">Deadline</th>
                                    <th class="py-3 px-2 border-b border-gray-200">CreatedAt</th>
                                    <th class="py-3 px-2 border-b border-gray-200">Act</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($TaskData as $index => $task)
                                    <tr class="border-b">
                                        <td class="py-3 px-2">{{ $index + 1 }}</td>
                                        <td class="py-3 px-2">{{ $task->class_name }}</td>
                                        <td class="py-3 px-2">
                                            {{ $task->task_name }}<br>
                                            <flux:modal.trigger name="showDescription">
                                                <flux:badge size="sm" variant="solid" color="sky" wire:click="changeDescription('{{ $task->id }}')">Description</flux:badge>
                                            </flux:modal.trigger>
                                        </td>
                                        <td class="py-3 px-2">{{ $task->task_deadline }}</td>
                                        <td class="py-3 px-2">{{ $task->created_at->format('d M Y H:i') }}</td>
                                        <td class="py-3 px-2">
                                            {{-- Tombol Aksi (misalnya Edit, Delete) --}}
                                            <flux:button.group>
                                                {{-- <flux:button size="sm" wire:click="upDataForUpdate('{{ $task->id }}')" variant="primary" hidden icon="pencil"></flux:button> --}}
                                                <flux:button size="xs" href="/teacher-task-answer/{{ $task->id }}" wire:navigate variant="primary" icon="eye"></flux:button>
                                                <flux:button size="xs" wire:confirm="Are you sure to delete this data ?" wire:click="deleteTask('{{ $task->id }}')" variant="danger" icon="trash"></flux:button>
                                            </flux:button.group>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </flux:callout>
            {{-- showDescription Modal --}}
            <flux:modal name="showDescription" class="md:w-96">
                <div class="space-y-6">
                    <div>
                        <flux:heading size="lg">Description & Media</flux:heading>
                        <flux:text class="mt-2">
                            {!!$descriptionTask!!}
                        </flux:text>
                    </div>
                </div>
            </flux:modal>
        </div>
    @else
        <div>
            <flux:button size="sm" wire:click="changeFormStatus('0')" variant="filled">List Task</flux:button>
            <br>
            <br>
            @if (session()->has('success_message'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-2 py-3 rounded relative mb-4" role="alert">
                    {{ session('success_message') }}
                </div>
            @endif
            <div class="p-2 shadow-lg rounded-lg">
                <form wire:submit.prevent="inputTaskAndStoreTaskMedia">
                    <flux:select wire:model.defer="class_group_uuid" label="Your Class" class="mb-3" required>
                        <option value="" selected>Select Class</option>
                        @foreach ($selectClass as $data)
                            <option value="{{$data->id}}">{{$data->class_name}}</option>
                        @endforeach
                    </flux:select>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-4">
                        <flux:input wire:model.defer="task_name" type="text" placeholder="Task Name" label="Task Name" class="mb-3" required />
                        <flux:input wire:model.defer="task_deadline" type="datetime-local" label="Deadline" class="mb-3 w-100" />
                    </div>

                    {{-- CKEditor untuk Task Description --}}
                    <div class="mb-3">
                        <label for="task_description_editor" class="block text-sm font-bold mb-2">Deskripsi Tugas:</label>
                        <div
                            wire:ignore
                            x-data="{
                                editor: null,
                                content: @entangle('task_description'),
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
                            <textarea x-ref="ckeditor" wire:model="task_description" id="task_description_editor" class="border rounded-lg p-3 w-full" style="min-height: 200px;"></textarea>
                        </div>
                        @error('task_description') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                    </div>

                    <flux:input wire:model="task_media" wire:loading.attr="disabled" type="file" multiple label="Task Media" class="mb-3" />
                    <div wire:loading wire:target="task_media" class="text-blue-500 text-sm mt-2">Uploading file(s)...</div>
                    <div class="flex">
                        <flux:spacer />
                        <flux:button wire:loading.attr="disabled" variant="primary" type="submit" class="mb-3">Create Task</flux:button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>

{{-- CDN CKEditor 5 Classic Build --}}
<script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js"></script>
