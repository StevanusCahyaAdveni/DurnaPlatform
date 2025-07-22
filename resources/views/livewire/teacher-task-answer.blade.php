<div>
    {{-- Success is as dangerous as failure. --}}
    <flux:heading size="xl">Teacher Task Answer</flux:heading>
    <flux:heading size="xl">{{$singleTask->task_name}}</flux:heading>
    <hr>
    <flux:text class="text-xs">{{$singleTask->group_name}}</flux:text>
    @if($formStatus === '0')
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-1 mt-2">
            @foreach($taskAnswers as $answer)
                <flux:callout class="shadow-lg rounded-lg p-0" style="padding: 0%;">
                    <flux:heading  class="mb-0" style="margin-bottom: -10px;"> {{$answer->student_name}}</flux:heading>
                    <flux:text class="mt-0 line-clamp-1">{!! Str::limit($answer->answer_text, 100, '...') !!}</flux:text>
                    <div class="flex">
                        <flux:spacer />
                        <flux:button type="submit" size="xs" variant="primary" wire:click="changeForm('1', '{{ $answer->id }}')"><flux:icon.eye variant="micro"></flux:icon.eye></flux:button>
                    </div>
                </flux:callout>
            @endforeach
        </div>
    @elseif($formStatus === '1')
        <flux:button size="xs" variant="filled" wire:click="changeForm('0')" icon="arrow-left" class="mt-2 mb-2">Back</flux:button>
        <flux:heading size="xl" class="">{{$answerName}} <sup><flux:modal.trigger name="setPoint"><flux:badge size="sm" wire:click="upDataPoint('{{ $singleAnswerPoint->point ?? ''}}')">{{ $singleAnswerPoint->point ?? 'Points'}}</flux:badge></flux:modal.trigger></sup></flux:heading>
        <flux:modal name="setPoint" class="md:w-96">
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">Set Points</flux:heading>
                </div>
                <flux:input type="number" wire:model="point" label="Points" />
                <div class="flex">
                    <flux:spacer />
                    <flux:button type="button" size="sm" x-on:click="$flux.modals().close()"  wire:click="setPoint" variant="primary">Save changes</flux:button>
                </div>
            </div>
        </flux:modal>
        <div class="max-w-full overflow-x-auto">
            <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 gap-2 mt-2">
                @foreach ($answerMedia as $media)
                    <flux:callout >
                        <flux:button size="sm" href="{{ asset('storage/task_media') }}/{{ $media->media_name }}" variant="primary" icon="eye" target="_blank">Show Media</flux:button>
                    </flux:callout>
                @endforeach
            </div>
        </div>
        <flux:callout class="mt-2 mb-2 shadow-lg rounded-lg">
            <flux:text>{!! $answerText !!}</flux:text>
        </flux:callout>
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
    @endif
</div>
