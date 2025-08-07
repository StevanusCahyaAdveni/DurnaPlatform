<div>
    <flux:heading size="xl">User Income & Top Up</flux:heading>
    <flux:text class="text-xs text-gray-500">Jer Basuki Mawa Beya (A success or achievement requires sacrifice)</flux:text>
    <flux:modal.trigger name="createIncomeModal" class="mb-2">
        <flux:button variant="primary" size="xs">[+] Create To Up</flux:button>
    </flux:modal.trigger>
    <flux:modal name="createIncomeModal" class="md:w-96">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Top Up</flux:heading>
            </div>
            <flux:input size="sm" label="Top Up Amount" class="mb-3" wire:model="nominal" placeholder="Top Up Amount" required />
            <flux:select size="sm" label="Top Up Method" wire:model="payment_method" placeholder="Choose a method..." required>
                <flux:select.option value="bank_transfer">Bank Transfer</flux:select.option>
                <flux:select.option value="e_wallet">E-Wallet</flux:select.option>
                <flux:select.option value="cash">Cash</flux:select.option>
            </flux:select>
            <div class="flex">
                <flux:spacer />
                <flux:button wire:click="createIncome" size="sm" class="mt-2" variant="primary" x-on:click="$flux.modals().close()">Create Top Up</flux:button>
            </div>
        </div>
    </flux:modal>

    @if (session()->has('message'))
        <flux:callout variant="success" icon="check-circle" class="my-2" heading="{{ session('message') }}" />
    @elseif (session()->has('error'))
        <flux:callout variant="error" icon="x-circle" class="my-2" heading="Failed to create income." />
    @endif

    @if($getUserIncome->isEmpty())
        <center>
            <flux:text class="text-xs text-gray-500">No income records found.</flux:text>
        </center>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 mt-2">
            @foreach ($getUserIncome as $data)
                <flux:callout class="shadow-lg rounded-lg p-0" style="padding: 0%;" >
                    <div class="flex justify-between items-center">
                        <div>
                            <flux:heading size="lg" class="mb-0">T{{date('symdhis', strtotime($data->created_at))}} </flux:heading>
                            <flux:text class="text-xs mt-0" style="text-transform: capitalize"><b>Rp {{ number_format($data->nominal,0,0,'.')  }}</b> ({{ $data->payment_method }})</flux:text>
                        </div>
                        <div>
                            <flux:button variant="primary" size="sm" style="text-transform: capitalize">{{$data->status}}</flux:button>
                        </div>
                    </div>
                </flux:callout>
            @endforeach
        </div>
    @endif
</div>
