@props([
    'title',
    'links' => [],
])

<div class="flex flex-col gap-3">
    <span class="text-xs font-semibold uppercase tracking-widest text-foreground">{{ $title }}</span>
    @foreach ($links as $link)
        @php
            $shouldNavigate = ! str_starts_with($link['href'], '#')
                && ! str_starts_with($link['href'], 'mailto:')
                && ! str_starts_with($link['href'], 'tel:');
        @endphp

        <a href="{{ $link['href'] }}" @if ($shouldNavigate) wire:navigate @endif class="text-sm text-muted-foreground hover:text-foreground">{{ $link['label'] }}</a>
    @endforeach
</div>
