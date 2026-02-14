@props([
    'mobile' => false,
])

@if ($mobile)
    <div class="flex items-center justify-between px-3 py-2">
        <span class="text-sm font-medium text-muted-foreground">Theme</span>
        <x-theme-toggle />
    </div>
    <div class="my-1 border-t border-border"></div>
    @auth
        <a href="{{ route('dashboard') }}" wire:navigate class="block rounded-lg px-3 py-2 text-sm text-muted-foreground hover:bg-secondary hover:text-foreground">Dashboard</a>
    @else
        <a href="{{ route('login') }}" wire:navigate class="block rounded-lg px-3 py-2 text-sm text-muted-foreground hover:bg-secondary hover:text-foreground">Sign In</a>
        <a href="{{ route('register') }}" wire:navigate class="mt-1 block rounded-lg bg-primary px-3 py-2 text-center text-sm font-semibold text-primary-foreground">Try Free</a>
    @endauth
@else
    <div class="flex items-center gap-3">
        <x-theme-toggle />
        @auth
            <a href="{{ route('dashboard') }}" wire:navigate class="rounded-full border border-border bg-card px-5 py-2 text-sm font-medium text-foreground transition hover:bg-secondary">
                Dashboard
            </a>
        @else
            <a href="{{ route('login') }}" wire:navigate class="rounded-full border border-border bg-card px-5 py-2 text-sm font-medium text-muted-foreground transition hover:text-foreground">
                Sign In
            </a>
            <a href="{{ route('register') }}" wire:navigate class="rounded-full bg-primary px-5 py-2 text-sm font-semibold text-primary-foreground transition hover:bg-primary/90">
                Try Free
            </a>
        @endauth
    </div>
@endif
