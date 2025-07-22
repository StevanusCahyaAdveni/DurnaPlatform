<div>
    <flux:heading size="xl">Class {{auth()->user()->name}}</flux:heading>
    <br>
    <div class="p-2 shadow-lg rounded-lg">
        <flux:input wire:model.live="searchClass" class="mb-0" size="sm" placeholder="Search Class..." />
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-2">
        @foreach ($classUser as $data)
        <flux:callout class="shadow-lg rounded-lg">
            
            <div class="">
                <flux:heading size="lg">{{ $data->class_name }}</flux:heading>
                <flux:badge variant="pill" size="sm" icon="user">{{ $data->name }}</flux:badge>
                <flux:badge variant="pill" size="sm" icon="user-group">{{ $data->class_category }}</flux:badge>
                <flux:text class="mt-2 line-clamp-1">{{ Str::limit($data->class_description, 100, '...') }}</flux:text>
                <flux:text class="mt-1">
                    <b>
                        Code : {{$data->class_code }}
                    </b>
                </flux:text>
                <div class="flex">
                    <flux:spacer />
                    <flux:button.group class="mt-3">
                        <flux:button wire:confirm="Are you sure to leave this class ?" wire:click="leaveClass('{{ $data->id }}')" size="sm" icon="trash" variant="danger"></flux:button>
                        <flux:button wire:navigate href="user-class-detail/{{$data->id}}" size="sm" icon="eye" variant="primary">Visit</flux:button>
                    </flux:button.group>
                </div>
            </div>
        </flux:callout>
        @endforeach
    </div>
    <div class="mt-8">
        {{ $classUser->links() }}
    </div>
</div>
