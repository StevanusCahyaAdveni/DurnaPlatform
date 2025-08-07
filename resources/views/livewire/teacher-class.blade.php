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
                <flux:input size="sm" label="Class Name" class="mb-3" wire:model="name" placeholder="Your Class Name" required/>
                <flux:textarea size="sm" label="Class Description" class="mb-3" wire:model="description" placeholder="Your Class Description" required />
                <div class="grid grid-cols-2 gap-2 mb-3">
                    <flux:field>
                        <flux:label>Class Price</flux:label>
                        <flux:input.group>
                            <flux:input.group.prefix>Rp</flux:input.group.prefix>
                            <flux:input size="sm" placeholder="Class Price" wire:model="price" type="number"  required/>
                        </flux:input.group>
                        <flux:error name="Class Price" />
                    </flux:field>
                    <flux:select size="sm" label="Type Subscription" wire:model="subscription" placeholder="Choose a type..." required>
                        <flux:select.option value="monthly">monthly</flux:select.option>
                        <flux:select.option value="yearly">yearly</flux:select.option>
                        <flux:select.option value="one_time">one_time</flux:select.option>
                    </flux:select>
                </div>
                <div class="grid grid-cols-2 gap-4 mb-3">
                    <flux:input type="number" size="sm" wire:model="participants" placeholder="Max Participant" label="Max Participant" required />
                    <flux:select size="sm" label="Class Category" wire:model="category" placeholder="Choose a category..." required>
                        <flux:select.option>Public</flux:select.option>
                        <flux:select.option>Private</flux:select.option>
                    </flux:select>
                </div>
                <div class="flex">
                    <flux:spacer />
                    <flux:button type="submit" x-on:click="$flux.modals().close()" variant="primary">Save changes</flux:button>
                </div>
            </form>
        </div>
    </flux:modal>
    <br>
    <flux:input wire:model.live="search" class="mb-2" placeholder="Search Class..." />
    {{-- <div class="p-2 shadow-lg rounded-lg">
    </div> --}}
    {{-- <br> --}}
    @if($classGroups->isEmpty())
    <div class="text-center p-6 bg-white rounded-lg shadow-md">
        <p class="text-gray-600 text-lg">Tidak ada kelas yang ditemukan.</p>
        <p class="text-gray-500 text-sm mt-2">Coba sesuaikan pencarian Anda atau buat kelas baru.</p>
    </div>
    @else
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
                        <flux:button wire:click="deleteClass('{{ $data->id }}')" wire:confirm="Are you sure to delete this data ?" size="sm" icon="trash"></flux:button>
                        <flux:modal.trigger name="add-class">
                            <flux:button wire:click="upData('{{ $data->id }}')" size="sm" icon="pencil"></flux:button>
                        </flux:modal.trigger>
                        <flux:button size="sm" href="/user-class-detail/{{$data->id}}" wire:navigate icon:trailing="arrow-right">
                            Visit Class
                        </flux:button>
                    </flux:button.group>
                </div>
            </div>
        </flux:callout>
        @endforeach
    </div>
    <div class="mt-8">
        {{ $classGroups->links() }} {{-- Ini akan merender tautan paginasi --}}
    </div>
    @endif
</div>