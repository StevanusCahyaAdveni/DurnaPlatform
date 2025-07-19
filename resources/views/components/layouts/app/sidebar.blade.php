<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    @include('partials.head')
</head>

<body class="min-h-screen bg-white dark:bg-zinc-800">
    <flux:sidebar sticky stashable class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
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
                <flux:navlist.item icon="book-open" :href="route('teacher-class')" :current="request()->routeIs('teacher-class')" wire:navigate>{{ __('Your Class (As Teacher)') }}</flux:navlist.item>
            </flux:navlist.group>

            @if(auth()->user()->role == 'teacher' || auth()->user()->role == 'teacher-v2')
            <flux:navlist.group :heading="__('Task & Exam')" class="grid">
                <flux:navlist.item icon="book-open" :href="route('teacher-task')" :current="request()->routeIs('teacher-task')" wire:navigate>{{ __('Task (As Teacher)') }}</flux:navlist.item>
                <flux:navlist.item icon="pencil" :href="route('teacher-exam')" :current="request()->routeIs('teacher-exam')" wire:navigate>{{ __('Exam (As Teacher)') }}</flux:navlist.item>
            </flux:navlist.group>

            @endif
            <flux:navlist.group :heading="__('Role & User')" class="grid">
                <flux:navlist.item icon="adjustments-vertical" :href="route('upgrade-role')" :current="request()->routeIs('upgrade-role')" wire:navigate>{{ __('Upgrade Your Role') }}</flux:navlist.item>
            </flux:navlist.group>

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