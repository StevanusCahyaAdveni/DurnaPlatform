<div>
    <flux:heading size="xl" class="mb-0">{{$singleData->class_name}} ({{number_format($UserSaldo,0,0,'.')}})</flux:heading>
    <flux:badge variant="pill" size="sm" class="mb-2" icon="user">{{$singleData->name}}</flux:badge>
    <flux:badge variant="pill" size="sm" class="mt-2" icon="user-group">{{$singleData->class_category}} ({{$singleData->participants}} Max Members)</flux:badge>
    <flux:badge variant="pill" size="sm" class="mt-2" color="green" icon="">Rp {{ number_format($singleData->price,0,0,'.')}}/{{$singleData->subscription}}</flux:badge>
    @if(session()->has('message'))
        <flux:callout  class="my-2" heading="{{ session('message') }}" />
    @endif
    <flux:callout class="shadow-lg rounded-lg mt-3">
        <flux:text>{{$singleData->class_description}}</flux:text>
        <flux:text>
            <b>Current member : {{$currentMembers}}</b>
        </flux:text>
        @if(Auth::user()->id != $singleData->user_id)
            @if($getJoin == 0)
                @if($currentMembers <= $singleData->participants)
                    <flux:modal.trigger name="JoinClass">
                        <flux:button size="sm" class="mt-5" icon="user-plus">Join Class</flux:button>
                    </flux:modal.trigger>
                @endif
            @elseif($getJoin >= 1)
                <flux:button.group>
                    <flux:button variant="danger" size="sm" class="mt-5" icon="user-minus" wire:click="leaveClass()" wire:confirm="Are you sure to leave this class ?">Leave Class</flux:button>
                    <flux:button variant="primary" href="/user-class-detail/{{$singleData->id}}" wire:navigate size="sm" class="mt-5" icon="arrow-right" >Visite Class</flux:button>
                </flux:button.group>
            @endif
        @else
            <flux:button variant="primary" href="/user-class-detail/{{$singleData->id}}" wire:navigate size="sm" class="mt-5" icon="arrow-right" >Visite Class</flux:button>   
        @endif
        
        <flux:modal name="JoinClass" class="md:w-96">
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">Verify</flux:heading>
                    <flux:text class="mt-2">Are you sure to join this class with <b>Rp {{ number_format($singleData->price,0,0,'.')}}</b> ?</flux:text>
                </div>
                <form wire:submit="joinClass">
                    <div class="flex">
                        <flux:spacer />
                        <flux:button type="submit" x-on:click="$flux.modals().close()" variant="primary" >Join Class</flux:button>
                    </div>
                </form>
            </div>
        </flux:modal>
    </flux:call>
</div>
