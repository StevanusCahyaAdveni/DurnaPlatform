<div>
    <flux:heading size="xl">Exam Class</flux:heading>
    <flux:modal.trigger name="addExam">
        <flux:badge color="green" wire:click="upDataForUpdate('')" variant="pill" size="sm">Add Exam</flux:badge>
    </flux:modal.trigger>
    <flux:modal name="addExam" class="md:w-96">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Add Exam</flux:heading>
            </div>
            <form wire:submit.prevent="addExam">
                <flux:select wire:model.defer="classgroup_id" size="sm" label="Your Class" class="mb-2" required>
                    <option value="" selected>Select Class</option>
                    @foreach ($getClass as $data)
                        <option value="{{$data->id}}" {{$classgroup_id == $data->id ? 'selected' : ''}}>{{$data->class_name}}</option>
                    @endforeach
                </flux:select>
                <flux:input size="sm" label="Exam Name" wire:model="exam_name" class="mb-2" placeholder="Exam Name" required/>
                <flux:input size="sm" label="Deadline" wire:model="exam_deadline" class="mb-2" type="datetime-local" required/>
                <flux:textarea size="sm" label="Description" wire:model="exam_description" class="mb-2" placeholder="Exam Description" required/>
                <div class="flex">
                    <flux:spacer />
                    <flux:button type="submit" x-on:click="$flux.modals().close()" variant="primary" size="sm" class="mt-3">Save changes</flux:button>
                </div>
            </form>
        </div>
    </flux:modal>
    @if(session()->has('message'))
        <flux:callout variant="success" icon="check-circle" class="my-2" heading="{{ session('message') }}" />
    @endif
    @if(session()->has('error'))
        <flux:callout variant="danger" icon="x-circle" class="my-2" heading="{{ session('error') }}" />
    @endif
    <flux:input wire:model.live="searchTerm" class="mb-0 mt-2" size="sm" placeholder="Search Exam..." />
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 mt-2">
        @foreach($getExam as $exam)
            <flux:callout class="shadow-lg rounded-lg">
                <div>
                    <flux:heading size="lg">{{ $exam->exam_name }}</flux:heading>
                    <flux:badge variant="pill" color="blue" size="sm">{{ $exam->classGroup->class_name }}</flux:badge>
                    <flux:badge variant="pill" color="red" size="sm" icon="calendar">{{ \Carbon\Carbon::parse($exam->exam_deadline)->format('d M Y H:i') }}</flux:badge>
                    <flux:text class="mt-0">{{ Str::limit($exam->exam_description, 100, '...') }}</flux:text>
                    <div class="flex justify-end mt-3">
                        <flux:button wire:click="deleteExam('{{$exam->exam_id}}')" wire:confirm="Are you sure to delete this data ?" size="sm" variant="danger" class="mx-1" icon="trash"></flux:button>
                        <flux:modal.trigger name="addExam">
                            <flux:button wire:click="upDataForUpdate('{{$exam->exam_id}}')" size="sm" variant="primary" color="green" class="mx-1" icon="pencil"></flux:button>
                        </flux:modal.trigger>
                        <flux:button wire:navigate href="/teacher-exam-detail/{{$exam->exam_id}}" size="sm" icon="eye" class="mx-1">View Details</flux:button>
                        {{-- <flux:button wire:navigate href="{{ route('teacher.exam.detail', ['id' => $exam->id]) }}" size="sm" icon="eye">View Details</flux:button> --}}
                    </div>
                </div>
            </flux:callout>
        @endforeach
    </div>
    <div class="mt-4">
        {{ $getExam->links() }} {{-- This will render pagination links --}}
    </div>
</div>
