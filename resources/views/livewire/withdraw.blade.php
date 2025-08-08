<div>
    <div class="flex justify-between items-center mb-4">
        <flux:heading size="xl">Withdraw Funds</flux:heading>
        <div class="text-right">
            <flux:text class="text-sm text-gray-600">Available Balance</flux:text>
            <div class="text-xl font-bold text-green-600">
                Rp {{ number_format($userSaldo, 0, 0, '.') }}
            </div>
        </div>
    </div>

    <!-- Development Mode Notice -->
    @if(config('app.env') === 'local')
        <flux:callout variant="info" icon="information-circle" class="mb-4">
            <strong>Development Mode</strong><br>
            <small>Withdrawals are simulated and will not process real money transfers.</small>
        </flux:callout>
    @endif

    <!-- Flash Messages -->
    @if (session()->has('success'))
        <flux:callout variant="success" icon="check-circle" class="mb-4" heading="{{ session('success') }}" />
    @elseif (session()->has('error'))
        <flux:callout variant="error" icon="x-circle" class="mb-4" heading="{{ session('error') }}" />
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Withdrawal Form -->
        <flux:callout>
            <flux:heading size="lg" class="mb-4">Create Withdrawal</flux:heading>
            
            <form wire:submit.prevent="processWithdrawal" class="space-y-4">
                <!-- Amount Input -->
                <div>
                    <flux:input 
                        wire:model.live="withdrawAmount" 
                        type="number" 
                        label="Withdrawal Amount" 
                        placeholder="Minimum: 50,000" 
                        min="50000" 
                        step="1000"
                        required />
                    <flux:text class="text-xs text-gray-500 mt-1">
                        Minimum withdrawal: Rp 50.000
                    </flux:text>
                </div>

                <!-- Withdrawal Type -->
                <div>
                    <flux:select wire:model.live="withdrawalType" label="Withdrawal Method" required>
                        <flux:select.option value="">Choose withdrawal method...</flux:select.option>
                        <flux:select.option value="bank">Bank Transfer</flux:select.option>
                        <flux:select.option value="ewallet">E-Wallet</flux:select.option>
                    </flux:select>
                </div>

                <!-- Bank Transfer Fields -->
                @if($withdrawalType === 'bank')
                    <div class=" rounded-lg">
                        {{-- <flux:heading size="sm" class="text-blue-800">Bank Transfer Details</flux:heading> --}}
                        
                        <flux:select wire:model="bankCode" label="Bank" required>
                            <flux:select.option value="">Select bank...</flux:select.option>
                            <flux:select.option value="BCA">BCA</flux:select.option>
                            <flux:select.option value="BNI">BNI</flux:select.option>
                            <flux:select.option value="BRI">BRI</flux:select.option>
                            <flux:select.option value="MANDIRI">Mandiri</flux:select.option>
                            <flux:select.option value="CIMB">CIMB Niaga</flux:select.option>
                            <flux:select.option value="PERMATA">Permata Bank</flux:select.option>
                            <flux:select.option value="DANAMON">Danamon</flux:select.option>
                            <flux:select.option value="BTN">BTN</flux:select.option>
                        </flux:select>

                        <flux:input 
                            wire:model="accountNumber" 
                            label="Account Number" 
                            placeholder="1234567890" 
                            required />

                        <flux:input 
                            wire:model="accountHolderName" 
                            label="Account Holder Name" 
                            placeholder="John Doe" 
                            required />
                    </div>
                @endif

                <!-- E-Wallet Fields -->
                @if($withdrawalType === 'ewallet')
                    <div class="rounded-lg">
                        {{-- <flux:heading size="sm" class="text-purple-800">E-Wallet Details</flux:heading> --}}
                        
                        <flux:select wire:model="ewalletType" label="E-Wallet Provider" required>
                            <flux:select.option value="">Select e-wallet...</flux:select.option>
                            <flux:select.option value="OVO">OVO</flux:select.option>
                            <flux:select.option value="DANA">DANA</flux:select.option>
                            <flux:select.option value="LINKAJA">LinkAja</flux:select.option>
                            <flux:select.option value="SHOPEEPAY">ShopeePay</flux:select.option>
                        </flux:select>

                        <flux:input 
                            wire:model="phoneNumber" 
                            label="Phone Number" 
                            placeholder="08123456789" 
                            required />
                    </div>
                @endif

                <!-- Notes -->
                <div>
                    <flux:textarea 
                        wire:model="notes" 
                        label="Notes (Optional)" 
                        placeholder="Additional notes for this withdrawal..."
                        rows="2" />
                </div>

                <!-- Fee Calculation -->
                @if($withdrawAmount && $withdrawalType)
                    <div class="bg-yellow-50 p-4 rounded-lg border border-yellow-200">
                        <flux:heading size="sm" class="text-yellow-800 mb-2">Fee Breakdown</flux:heading>
                        <div class="space-y-1 text-sm">
                            <div class="flex justify-between">
                                <span>Withdrawal Amount:</span>
                                <span class="font-semibold">Rp {{ number_format($withdrawAmount, 0, 0, '.') }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Admin Fee:</span>
                                <span class="font-semibold">Rp {{ number_format($adminFee, 0, 0, '.') }}</span>
                            </div>
                            <hr class="border-yellow-300">
                            <div class="flex justify-between font-bold text-yellow-800">
                                <span>Total Deduction:</span>
                                <span>Rp {{ number_format($withdrawAmount + $adminFee, 0, 0, '.') }}</span>
                            </div>
                            <div class="flex justify-between text-green-700">
                                <span>You will receive:</span>
                                <span class="font-bold">Rp {{ number_format($withdrawAmount, 0, 0, '.') }}</span>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Submit Button -->
                <div class="pt-4">
                    <flux:button 
                        type="submit" 
                        variant="primary" 
                        class="w-full" 
                        wire:loading.attr="disabled"
                        :disabled="!$withdrawAmount || !$withdrawalType || ($userSaldo < ($withdrawAmount + $adminFee))">
                        <span wire:loading.remove wire:target="processWithdrawal">
                            Submit Withdrawal Request
                        </span>
                        <span wire:loading wire:target="processWithdrawal">
                            Processing...
                        </span>
                    </flux:button>
                </div>
            </form>
        </flux:callout>

        <!-- Withdrawal History -->
        <flux:callout>
            <flux:heading size="lg" class="mb-4">Withdrawal History</flux:heading>
            
            @if($withdrawals->isEmpty())
                <div class="text-center py-8">
                    <flux:text class="text-gray-500">No withdrawal records found.</flux:text>
                </div>
            @else
                <div class="space-y-3 max-h-96 overflow-y-auto">
                    @foreach ($withdrawals as $withdrawal)
                        <div class="border border-gray-200 rounded-lg p-4">
                            <div class="flex justify-between items-start mb-2">
                                <div>
                                    <flux:text class="font-semibold">
                                        {{ $withdrawal->formatted_amount }}
                                    </flux:text>
                                    <flux:text class="text-xs text-gray-500">
                                        ID: {{ substr($withdrawal->external_id, -8) }}
                                    </flux:text>
                                </div>
                                <flux:badge variant="{{ $withdrawal->status_badge_color }}" size="sm">
                                    {{ ucfirst($withdrawal->status) }}
                                </flux:badge>
                            </div>
                            
                            <div class="text-sm text-gray-600 mb-2">
                                @if($withdrawal->withdrawal_type === 'bank')
                                    <div>🏦 {{ $withdrawal->bank_code }}</div>
                                    <div>{{ $withdrawal->account_number }} ({{ $withdrawal->account_holder_name }})</div>
                                @else
                                    <div>💳 {{ $withdrawal->ewallet_type }}</div>
                                    <div>{{ $withdrawal->phone_number }}</div>
                                @endif
                            </div>
                            
                            <div class="flex justify-between items-center text-xs text-gray-500">
                                <span>{{ $withdrawal->created_at->format('d M Y H:i') }}</span>
                                <span>Fee: Rp {{ number_format($withdrawal->admin_fee, 0, 0, '.') }}</span>
                            </div>
                            
                            <!-- Action Buttons -->
                            <div class="mt-3 flex gap-2">
                                @if($withdrawal->status === 'pending')
                                    <flux:button 
                                        wire:click="cancelWithdrawal('{{ $withdrawal->id }}')" 
                                        variant="ghost" 
                                        size="sm"
                                        wire:confirm="Are you sure you want to cancel this withdrawal? Your balance will be refunded.">
                                        Cancel
                                    </flux:button>
                                @endif
                                
                                @if($withdrawal->status === 'processing' && config('app.env') === 'local')
                                    <flux:button 
                                        wire:click="simulateComplete('{{ $withdrawal->id }}')" 
                                        variant="primary" 
                                        size="sm">
                                        🎯 Simulate Complete
                                    </flux:button>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
                
                <!-- Pagination -->
                <div class="mt-4">
                    {{ $withdrawals->links() }}
                </div>
            @endif
        </flux:callout>
    </div>
</div>
