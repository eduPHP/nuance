@props([
    'plan',
])

<article class="relative rounded-2xl border p-7 transition-shadow {{ $plan['featured'] ? 'border-primary bg-card shadow-xl shadow-primary/10' : 'border-border/60 bg-card hover:shadow-lg hover:shadow-primary/5' }}">
    @if ($plan['featured'])
        <span class="absolute -top-3 left-1/2 -translate-x-1/2 rounded-full bg-primary px-4 py-1 text-xs font-semibold text-primary-foreground">Most Popular</span>
    @endif

    <h3 class="text-lg font-semibold text-foreground">{{ $plan['name'] }}</h3>
    <p class="mt-1 text-sm text-muted-foreground">{{ $plan['description'] }}</p>

    <div class="mt-5 flex items-baseline gap-1">
        <span class="text-4xl font-bold text-foreground">{{ $plan['price'] }}</span>
        <span class="text-sm text-muted-foreground">/{{ $plan['period'] }}</span>
    </div>

    @auth
        <a href="{{ route('dashboard') }}" class="mt-6 block w-full rounded-full px-5 py-3 text-center text-sm font-semibold {{ $plan['featured'] ? 'bg-primary text-primary-foreground shadow-lg shadow-primary/25 hover:bg-primary/90' : 'border border-border bg-card text-foreground hover:bg-secondary' }}">
            {{ $plan['featured'] ? 'Start Free Trial' : 'Get Started' }}
        </a>
    @else
        <a href="{{ route('register') }}" class="mt-6 block w-full rounded-full px-5 py-3 text-center text-sm font-semibold {{ $plan['featured'] ? 'bg-primary text-primary-foreground shadow-lg shadow-primary/25 hover:bg-primary/90' : 'border border-border bg-card text-foreground hover:bg-secondary' }}">
            {{ $plan['featured'] ? 'Start Free Trial' : 'Get Started' }}
        </a>
    @endauth

    <ul class="mt-7 space-y-3">
        @foreach ($plan['features'] as $feature)
            <li class="flex items-center gap-3 text-sm text-foreground">
                <span class="h-1.5 w-1.5 rounded-full bg-primary"></span>
                {{ $feature }}
            </li>
        @endforeach
    </ul>
</article>
