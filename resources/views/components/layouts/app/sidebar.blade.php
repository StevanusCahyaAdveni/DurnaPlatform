<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    @include('partials.head')
</head>

<body class="min-h-screen bg-white dark:bg-zinc-800">
    @php
        $currentUrl = url()->current();
        $isExamAnswer = str_contains($currentUrl, 'user-exam-answer');
    @endphp
    <flux:sidebar 
        sticky 
        stashable  
        class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900 {{ $isExamAnswer ? 'hidden' : '' }}"
    >
        <flux:sidebar.toggle class="lg:hidden" icon="x-mark" />
        <a href="{{ route('user-dashboard') }}" class="me-5 flex items-center space-x-2 rtl:space-x-reverse" wire:navigate>
            <!-- <x-app-logo /> -->
             <h1>
                 <flux:brand style="font-size: 20px" href="user-dashboard" wire:navigate logo="/app-logo.png" name="{{ config('app.name', 'Laravel') }}" />
             </h1>
        </a>
        <flux:navlist variant="outline">
            <flux:navlist.group :heading="__('Dashboard')" class="grid">
                <flux:navlist.item icon="home" :href="route('user-dashboard')" :current="request()->routeIs('user-dashboard')" wire:navigate>{{ __('Dashboard') }}</flux:navlist.item>
            </flux:navlist.group>

            <flux:navlist.group :heading="__('Class')" class="grid">
                <flux:navlist.item icon="home-modern" :href="route('user-class')" :current="request()->routeIs('user-class') OR request()->routeIs('user-class-detail')" wire:navigate>{{ __('Your Class') }}</flux:navlist.item>
                <flux:navlist.item icon="magnifying-glass-circle" :href="route('our-class')" :current="request()->routeIs('our-class')" wire:navigate>{{ __('Other Class') }}</flux:navlist.item>
                @if(auth()->user()->role == 'teacher' || auth()->user()->role == 'teacher-v2')
                <flux:navlist.item icon="book-open" :href="route('teacher-class')" :current="request()->routeIs('teacher-class')" wire:navigate>{{ __('Your Class (As Teacher)') }}</flux:navlist.item>
                @endif
            </flux:navlist.group>

            @if(auth()->user()->role == 'teacher' || auth()->user()->role == 'teacher-v2')
            <flux:navlist.group :heading="__('Task & Exam')" class="grid">
                <flux:navlist.item icon="book-open" :href="route('teacher-task')" :current="request()->routeIs('teacher-task')" wire:navigate>{{ __('Task (As Teacher)') }}</flux:navlist.item>
                <flux:navlist.item icon="pencil" :href="route('teacher-exam')" :current="request()->routeIs('teacher-exam')" wire:navigate>{{ __('Exam (As Teacher)') }}</flux:navlist.item>
            </flux:navlist.group>
            @endif

            <flux:navlist.group :heading="__('Ask AI')" class="grid">
                <flux:navlist.item icon="light-bulb" :href="route('ai-chat')" :current="request()->routeIs('ai-chat')" wire:navigate>{{ __('Chat with AI') }}</flux:navlist.item>
            </flux:navlist.group>
            
            <flux:navlist.group :heading="__('Saldo & Top Up')" class="grid">
                @php
                    // Calculate balance from 3 tables: Income + Withdrawals - Subscriptions
                    $getIncome = \App\Models\Income::where('user_id', auth()->id())
                        ->where('status', 'paid')
                        ->sum('nominal') ?? 0;
                    
                    $getWithdrawals = \App\Models\Withdrawal::where('user_id', auth()->id())
                        ->where('status', 'completed')
                        ->sum('total_amount') ?? 0;
                    
                    $getSubscription = \App\Models\Subscription::where('user_id', auth()->id())->sum('nominal') ?? 0;
                    $getIncomeClassBySubscription = \App\Models\Subscription::join('class_groups', 'class_groups.id', '=', 'subscriptions.class_uuid')->where('class_groups.user_id', auth()->id())->sum('nominal') ?? 0;
                    $getIncomeCourseBySubscription = \App\Models\Subscription::join('courses', 'courses.id', '=', 'subscriptions.course_uuid')->where('courses.user_id', auth()->id())->sum('nominal') ?? 0;

                    // Balance = Income - Withdrawals - Subscriptions
                    $UserSaldo = ($getIncome+$getIncomeClassBySubscription+$getIncomeCourseBySubscription) - $getWithdrawals - $getSubscription;
                @endphp
                <flux:navlist.item icon="credit-card" >Saldo : <b>Rp {{ number_format($UserSaldo,0,0,'.') }}</b></flux:navlist.item>
                <flux:navlist.group expandable heading="Income & Outcome" class="grid" :expanded="request()->routeIs('user-income') || request()->routeIs('invoice-history') || request()->routeIs('withdraw')">
                    <flux:navlist.item icon="credit-card" :href="route('user-income')" :current="request()->routeIs('user-income')" wire:navigate>{{ __('Income & Top Up') }}</flux:navlist.item>
                    <flux:navlist.item icon="credit-card" :href="route('invoice-history')" :current="request()->routeIs('invoice-history')" wire:navigate>{{ __('Subscription History') }}</flux:navlist.item>
                    <flux:navlist.item icon="banknotes" :href="route('withdraw')" :current="request()->routeIs('withdraw')" wire:navigate>{{ __('Withdraw') }}</flux:navlist.item>
                </flux:navlist.group>
            </flux:navlist.group>

            <br>
            <flux:radio.group x-data variant="segmented" x-model="$flux.appearance">
                <flux:radio size="sm" value="light" icon="sun">{{ __('') }}</flux:radio>
                <flux:radio size="sm" value="dark" icon="moon">{{ __('') }}</flux:radio>
                <flux:radio size="sm" value="system" icon="computer-desktop">{{ __('') }}</flux:radio>
            </flux:radio.group>
            
            {{-- <flux:navlist.group :heading="__('Role & User')" class="grid"> --}}
                {{-- <flux:navlist.item icon="adjustments-vertical" :href="route('upgrade-role')" :current="request()->routeIs('upgrade-role')" wire:navigate>{{ __('Upgrade Your Role') }}</flux:navlist.item> --}}
            {{-- </flux:navlist.group> --}}

        </flux:navlist>

        <flux:spacer />

        <!-- <flux:navlist variant="outline">
                <flux:navlist.item icon="folder-git-2" href="https://github.com/laravel/livewire-starter-kit" target="_blank">
                {{ __('Repository') }}
                </flux:navlist.item>

                <flux:navlist.item icon="book-open-text" href="https://laravel.com/docs/starter-kits#livewire" target="_blank">
                {{ __('Documentation') }}
                </flux:navlist.item>
            </flux:navlist> -->

        <!-- Desktop User Menu -->
        <flux:dropdown class="hidden lg:block" position="bottom" align="start">
            <flux:profile
                :name="auth()->user()->name"
                :initials="auth()->user()->initials()"
                icon:trailing="chevrons-up-down" />

            <flux:menu class="w-[220px]">
                <flux:menu.radio.group>
                    <div class="p-0 text-sm font-normal">
                        <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                            <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                <span
                                    class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white">
                                    {{ auth()->user()->initials() }}
                                </span>
                            </span>

                            <div class="grid flex-1 text-start text-sm leading-tight">
                                <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                                <span class="truncate text-xs">{{ auth()->user()->email }}</span>
                            </div>
                        </div>
                    </div>
                </flux:menu.radio.group>

                <flux:menu.separator />

                <flux:menu.radio.group>
                    <flux:menu.item :href="route('settings.profile')" icon="cog" wire:navigate>{{ __('Settings') }}</flux:menu.item>
                </flux:menu.radio.group>
                <flux:menu.separator />

                <flux:menu.item icon="adjustments-vertical" :href="route('upgrade-role')" :current="request()->routeIs('upgrade-role')" wire:navigate>{{ __('Upgrade Your Role') }}</flux:menu.item>

                <flux:menu.separator />

                <form method="POST" action="{{ route('logout') }}" class="w-full">
                    @csrf
                    <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
                        {{ __('Log Out') }}
                    </flux:menu.item>
                </form>
            </flux:menu>
        </flux:dropdown>
    </flux:sidebar>

    <!-- Mobile User Menu -->
    <flux:header class="lg:hidden">
        <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

        <flux:spacer />

        <flux:dropdown position="top" align="end">
            <flux:profile
                :initials="auth()->user()->initials()"
                icon-trailing="chevron-down" />

            <flux:menu>
                <flux:menu.radio.group>
                    <div class="p-0 text-sm font-normal">
                        <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                            <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                <span
                                    class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white">
                                    {{ auth()->user()->initials() }}
                                </span>
                            </span>

                            <div class="grid flex-1 text-start text-sm leading-tight">
                                <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                                <span class="truncate text-xs">{{ auth()->user()->email }}</span>
                            </div>
                        </div>
                    </div>
                </flux:menu.radio.group>

                <flux:menu.separator />

                <flux:menu.radio.group>
                    <flux:menu.item :href="route('settings.profile')" icon="cog" wire:navigate>{{ __('Settings') }}</flux:menu.item>
                </flux:menu.radio.group>
                <flux:menu.separator />
                
                <flux:menu.item icon="adjustments-vertical" :href="route('upgrade-role')" :current="request()->routeIs('upgrade-role')" wire:navigate>{{ __('Upgrade Your Role') }}</flux:menu.item>

                <flux:menu.separator />
                <flux:menu.separator />

                <form method="POST" action="{{ route('logout') }}" class="w-full">
                    @csrf
                    <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
                        {{ __('Log Out') }}
                    </flux:menu.item>
                </form>
            </flux:menu>
        </flux:dropdown>
    </flux:header>

    {{ $slot }}

    @fluxScripts
</body>

</html>