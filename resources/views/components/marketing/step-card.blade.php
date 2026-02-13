@props([
    'number',
    'title',
    'description',
    'showConnector' => false,
])

<article class="relative flex flex-col items-center text-center">
    @if ($showConnector)
        <div class="absolute left-[calc(50%+40px)] top-10 hidden h-px w-[calc(100%-80px)] bg-border md:block"></div>
    @endif

    <div class="relative mb-6 flex h-20 w-20 items-center justify-center rounded-2xl bg-accent">
        <span class="absolute -right-2 -top-2 flex h-7 w-7 items-center justify-center rounded-full bg-primary text-xs font-bold text-primary-foreground">{{ $number }}</span>
    </div>

    <h3 class="mb-3 text-xl font-semibold text-foreground">{{ $title }}</h3>
    <p class="max-w-sm text-sm leading-relaxed text-muted-foreground">{{ $description }}</p>
</article>
