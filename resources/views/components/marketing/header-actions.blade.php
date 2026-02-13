@props([
    'mobile' => false,
])

@if ($mobile)
    @auth
        <a href="{{ route('dashboard') }}" class="block rounded-lg px-3 py-2 text-sm text-muted-foreground hover:bg-secondary hover:text-foreground">Dashboard</a>
    @else
        <a href="{{ route('login') }}" class="block rounded-lg px-3 py-2 text-sm text-muted-foreground hover:bg-secondary hover:text-foreground">Sign In</a>
        <a href="{{ route('register') }}" class="mt-1 block rounded-lg bg-primary px-3 py-2 text-center text-sm font-semibold text-primary-foreground">Try Free</a>
    @endauth
@else
    @auth
        <a href="{{ route('dashboard') }}" class="rounded-full border border-border bg-card px-5 py-2 text-sm font-medium text-foreground transition hover:bg-secondary">
            Dashboard
        </a>
    @else
        <a href="{{ route('login') }}" class="rounded-full border border-border bg-card px-5 py-2 text-sm font-medium text-muted-foreground transition hover:text-foreground">
            Sign In
        </a>
        <a href="{{ route('register') }}" class="rounded-full bg-primary px-5 py-2 text-sm font-semibold text-primary-foreground transition hover:bg-primary/90">
            Try Free
        </a>
    @endauth
@endif
