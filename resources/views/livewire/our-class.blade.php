<div>
    <flux:heading size="xl">Other Class</flux:heading>
    @if (session()->has('error'))
        <flux:callout variant="danger" icon="x-circle" class="my-2"  heading="{{ session('error') }}" />
    @endif
    <div class="flex">
        <flux:spacer></flux:spacer>
        <flux:modal.trigger name="srcByCodeModal" class="mb-2">
            <flux:badge icon="eye-slash" color="green" class="mb-2">Join Private Class</flux:badge>
        </flux:modal.trigger>
    </div>
    <div class="mb-3">
        <flux:input wire:model.live="search" class="mb-0" size="sm" placeholder="Search Class..." />
    </div>
    <flux:modal name="srcByCodeModal" class="md:w-96">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Search Private Class</flux:heading>
                <flux:text class="mt-2">Search private class with class unique code</flux:text>
            </div>
            <flux:input label="Class Code" class="mb-3" wire:model="classCode" placeholder="Class Code" />
            <div class="flex">
                <flux:spacer />
                <flux:button type="submit" x-on:click="$flux.modals().close()" wire:click="srcByCode" size="sm" variant="primary">Save changes</flux:button>
            </div>
        </div>
    </flux:modal>
    {{-- <br> --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 mt-2">
        @foreach ($classGroups as $data)
        <flux:callout class="shadow-lg rounded-lg">
            <div class="">
                <div class="flex justify-between items-center">
                    <flux:heading size="lg">{{ $data->class_name }}</flux:heading>
                    <flux:tooltip content="{{$data->subscription}}" position="bottom">
                        <flux:badge variant="pill" color="green" size="sm" icon="">{{$data->price == 0 ? 'Free' : 'Rp'.number_format($data->price, 0, ',', '.') }}</flux:badge>
                    </flux:tooltip>
                </div>
                <flux:badge variant="pill" size="sm" class="mt-2" icon="user">{{ $data->name }}</flux:badge>
                <flux:badge variant="pill" size="sm" class="mt-2" icon="user-group">{{ $data->class_category }} ({{$data->participants}} Max Members)</flux:badge>
                <flux:text class="mt-2 line-clamp-1">{{ Str::limit($data->class_description, 100, '...') }}</flux:text>
                <flux:text class="mt-1">
                    <b>
                        Code : {{$data->class_code }}
                    </b>
                </flux:text>
                <div class="flex">
                    <flux:spacer />
                    <flux:button.group class="mt-3">
                        <flux:button wire:navigate href="our-class-preview/{{$data->id}}" size="sm" icon="user-plus">Visit for Join</flux:button>
                    </flux:button.group>
                </div>
            </div>
        </flux:callout>
        @endforeach
    </div>
    <div class="mt-8">
        {{ $classGroups->links() }} {{-- Ini akan merender tautan paginasi --}}
    </div>
</div>
