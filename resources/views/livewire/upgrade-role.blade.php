<div>
    <flux:heading size="xl">Upgrade Your Role</flux:heading>
    <br>
    <div class=" p-2 shadow-lg rounded-lg">
        <form wire:submit.prevent="upgradeRole">
            <div class="grid grid-cols-2 gap-1">
                <flux:input label="Email" readonly wire:model="email" placeholder="River" />
    
                <flux:select label="Role" wire:model="role">
                    <option value="teacher" {{ $role == 'teacher' ? 'selected' : '' }}>Teahcer (Have A Class)</option>
                    <option value="teacher-v2" {{ $role == 'teacher-v2' ? 'selected' : '' }}>Teacher-v2 (Have A Class And Course)</option>
                    <option value="user" {{ $role == 'user' ? 'selected' : '' }}>User</option>
                </flux:select>
            </div>
            <p align="right">
                <flux:button variant="primary" color="blue" class="mt-4" type="submit">Upgrade Role</flux:button>
            </p>
        </form>
    </div>
</div>