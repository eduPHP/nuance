@props([
    'eyebrow',
    'title',
    'description',
])

<div {{ $attributes->class('mx-auto mb-16 max-w-2xl text-center') }}>
    <span class="text-sm font-semibold uppercase tracking-widest text-primary">{{ $eyebrow }}</span>
    <h2 class="mt-3 text-balance text-3xl font-bold tracking-tight text-foreground md:text-4xl">
        {{ $title }}
    </h2>
    <p class="mt-4 text-pretty text-lg leading-relaxed text-muted-foreground">
        {{ $description }}
    </p>
</div>
