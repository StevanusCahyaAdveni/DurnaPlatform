<div>
    <flux:heading size="xl">Teacher Class</flux:heading>
    <flux:modal.trigger name="add-class">
        <flux:button size="xs" color="primary" wire:click="upData('')" class="mb-5 mt-2">[+] Add Class</flux:button>
    </flux:modal.trigger>
    <flux:modal name="add-class" class="md:w-100">
        <div class="space-y-6">
            <form wire:submit.prevent="addClass">
                <div>
                    <flux:heading size="lg">Class Group</flux:heading>
                    <flux:text class="mt-2">Class Goup for your private or public students</flux:text>
                </div>
                <flux:input label="Class Name" class="mb-3" wire:model="name" placeholder="Your Class Name" />
                <flux:textarea label="Class Description" class="mb-3" wire:model="description" placeholder="Your Class Description" />
                <flux:select label="Class Category" class="mb-3" wire:model="category" placeholder="Choose a category...">
                    <flux:select.option>Public</flux:select.option>
                    <flux:select.option>Private</flux:select.option>
                </flux:select>
                <div class="flex">
                    <flux:spacer />
                    <flux:button type="submit" x-on:click="$flux.modals().close()" variant="primary">Save changes</flux:button>
                </div>
            </form>
        </div>
    </flux:modal>
    <br>
    <div class="p-2 shadow-lg rounded-lg">
        <flux:input wire:model.live="search" class="mb-3" placeholder="Search Class..." />
    </div>
    <br>
    @if($classGroups->isEmpty())
    <div class="text-center p-6 bg-white rounded-lg shadow-md">
        <p class="text-gray-600 text-lg">Tidak ada kelas yang ditemukan.</p>
        <p class="text-gray-500 text-sm mt-2">Coba sesuaikan pencarian Anda atau buat kelas baru.</p>
    </div>
    @else
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        @foreach ($classGroups as $data)
        <div class="p-2 shadow-lg rounded-lg">
            <flux:heading size="lg">{{ $data->class_name }}</flux:heading>
            <flux:badge variant="pill" size="sm" icon="user">{{ $data->name }}</flux:badge>
            <flux:badge variant="pill" size="sm" icon="user-group">{{ $data->class_category }}</flux:badge>
            <flux:text class="mt-2">{{$data->class_description }}</flux:text>
            <flux:text class="mt-1">
                <b>
                    Code : {{$data->class_code }}
                </b>
            </flux:text>
            <div class="flex">
                <flux:spacer />
                <flux:button.group class="mt-3">
                    <flux:button wire:click="deleteClass('{{ $data->id }}')" wire:confirm="Are you sure to delete this data ?" size="sm" icon="trash"></flux:button>
                    <flux:modal.trigger name="add-class">
                        <flux:button wire:click="upData('{{ $data->id }}')" size="sm" icon="pencil"></flux:button>
                    </flux:modal.trigger>
                    <flux:button size="sm" href="https://google.com" icon:trailing="arrow-right">
                        Visit Class
                    </flux:button>
                </flux:button.group>
            </div>
        </div>
        @endforeach
    </div>
    <div class="mt-8">
        {{ $classGroups->links() }} {{-- Ini akan merender tautan paginasi --}}
    </div>
    @endif
</div>