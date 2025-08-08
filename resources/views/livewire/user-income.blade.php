<!-- filepath: d:\Laravel App\Laravel12\DurnaPlatform\resources\views\livewire\user-income.blade.php -->
<div>
    <flux:heading size="xl">User Income & Top Up</flux:heading>
    <flux:text class="text-xs text-gray-500">Jer Basuki Mawa Beya (A ghost or achievement requires sacrifice)</flux:text>
    <div class="flex justify-between">
        <flux:modal.trigger name="createIncomeModal" class="mb-2">
            <flux:button variant="primary" size="xs">[+] Create Top Up</flux:button>
        </flux:modal.trigger>
        <div>
            <b>
                Rp {{number_format($UserSaldo, 0, ',', '.')}}
            </b>
        </div>
    </div>
    <hr class="mt-2">
    
    <!-- Create Income Modal -->
    <flux:modal name="createIncomeModal" class="md:w-96">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Top Up via Xendit</flux:heading>
                <flux:text class="text-xs text-gray-500">Minimum: Rp 10.000 - Maximum: Rp 10.000.000</flux:text>
            </div>
            <flux:input size="sm" label="Top Up Amount" class="mb-3" wire:model="nominal" placeholder="Enter amount (min 10.000)" type="number" min="10000" max="10000000" required />
            <flux:select size="sm" label="Top Up Method" wire:model="payment_method" placeholder="Choose a method..." required>
                <flux:select.option value="xendit_all">All Payment Methods (Xendit)</flux:select.option>
                <flux:select.option value="xendit_bank">Bank Transfer</flux:select.option>
                <flux:select.option value="xendit_ewallet">E-Wallet</flux:select.option>
                <flux:select.option value="xendit_qris">QRIS</flux:select.option>
            </flux:select>
            <div class="bg-blue-50 p-3 rounded-lg">
                <flux:text class="text-xs text-blue-600">
                    <strong>Note:</strong> You will be redirected to Xendit payment page after creating this top up request.
                </flux:text>
            </div>
            <div class="flex">
                <flux:spacer />
                <flux:button wire:click="createIncome" size="sm" class="mt-2" variant="primary" x-on:click="$flux.modals().close()">
                    Create & Pay Now
                </flux:button>
            </div>
        </div>
    </flux:modal>

    @if (session()->has('message'))
        <flux:callout variant="success" icon="check-circle" class="my-2" heading="{{ session('message') }}" />
    @elseif (session()->has('error'))
        <flux:callout variant="error" icon="x-circle" class="my-2" heading="{{ session('error') }}" />
    @elseif (session()->has('success'))
        <flux:callout variant="success" icon="check-circle" class="my-2" heading="{{ session('success') }}" />
    @endif

    @if($getUserIncome->isEmpty())
        <center>
            <flux:text class="text-xs text-gray-500">No income records found.</flux:text>
        </center>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 mt-2">
            @foreach ($getUserIncome as $data)
                <flux:callout class="shadow-lg rounded-lg p-4" >
                    <div class="flex justify-between items-center">
                        <div class="flex-1">
                            <flux:heading size="lg" class="mb-0">T{{date('symdhis', strtotime($data->created_at))}}</flux:heading>
                            <flux:text class="text-xs mt-0" style="text-transform: capitalize">
                                <b>Rp {{ number_format($data->nominal,0,0,'.') }}</b> ({{ str_replace('_', ' ', $data->payment_method) }})
                            </flux:text>
                            @if($data->paid_at)
                                <flux:text class="text-xs text-green-600">
                                    Paid: {{ $data->paid_at->format('d M Y H:i') }}
                                </flux:text>
                            @endif
                        </div>
                        <div class="flex flex-col gap-2">
                            @if($data->status === 'pending')
                                <flux:button variant="primary" size="sm" wire:click="payNow('{{ $data->id }}')" class="text-xs">
                                    Pay Now
                                </flux:button>
                                <flux:button variant="danger" size="sm" wire:click="checkPaymentStatus('{{ $data->id }}')" class="text-xs">
                                    Check Status
                                </flux:button>
                            @elseif($data->status === 'paid')
                                <flux:button variant="primary" size="sm" disabled class="text-xs">
                                    ✓ Paid
                                </flux:button>
                            @else
                                <flux:button variant="danger" size="sm" style="text-transform: capitalize" class="text-xs">
                                    {{ $data->status }}
                                </flux:button>
                            @endif
                        </div>
                    </div>
                </flux:callout>
            @endforeach
        </div>
    @endif
    <div class="mt-8">
        {{ $getUserIncome->links() }} {{-- Ini akan merender tautan paginasi --}}
    </div>

    <script>
        // Auto-refresh payment status every 30 seconds for pending payments
        document.addEventListener('DOMContentLoaded', function() {
            setInterval(function() {
                // Check if there are pending payments
                const pendingButtons = document.querySelectorAll('[wire\\:click*="checkPaymentStatus"]');
                if (pendingButtons.length > 0) {
                    console.log('Auto-checking payment status...');
                    // Trigger a component refresh
                    Livewire.dispatch('refreshComponent');
                }
            }, 30000); // 30 seconds
        });
    </script>
</div>