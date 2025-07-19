<div>
    <flux:heading size="xl" class="mb-3">{{$singleData->class_name}}</flux:heading>
    <flux:badge variant="pill" size="sm" icon="user">{{$singleData->name}}</flux:badge>
    <flux:badge variant="pill" size="sm" icon="user-group">{{$singleData->class_category}}</flux:badge>
    <div class="p-2 shadow-lg rounded-lg mt-3">
        <flux:text>{{$singleData->class_description}}</flux:text>

        @if($getJoin == 0)
            <flux:modal.trigger name="JoinClass">
                <flux:button size="sm" class="mt-5" icon="user-plus">Join Class</flux:button>
            </flux:modal.trigger>
        @elseif($getJoin >= 1)
            <flux:button variant="danger" size="sm" class="mt-5" icon="user-minus" wire:click="leaveClass()">Leave Class</flux:button>
            <flux:button variant="primary" href="/user-class-detail/{{$singleData->id}}" wire:navigate size="sm" class="mt-5" icon="user-minus" >Visite Class</flux:button>
        @endif
        
        <flux:modal name="JoinClass" class="md:w-96">
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">Verify</flux:heading>
                    <flux:text class="mt-2">Are you sure to join this class ?</flux:text>
                </div>
                <form wire:submit="joinClass">
                    <div class="flex">
                        <flux:spacer />
                        <flux:button type="submit" x-on:click="$flux.modals().close()" variant="primary" >Join Class</flux:button>
                    </div>
                </form>
            </div>
        </flux:modal>
    </div>
</div>
