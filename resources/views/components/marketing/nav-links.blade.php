@props([
    'links' => [],
    'itemClass' => 'text-sm font-medium text-muted-foreground transition-colors hover:text-foreground',
])

<div {{ $attributes->class('items-center gap-8') }}>
    @foreach ($links as $link)
        @php
            $shouldNavigate = ! str_starts_with($link['href'], '#')
                && ! str_starts_with($link['href'], 'mailto:')
                && ! str_starts_with($link['href'], 'tel:');
        @endphp

        <a href="{{ $link['href'] }}" @if ($shouldNavigate) wire:navigate @endif class="{{ $itemClass }}">{{ $link['label'] }}</a>
    @endforeach
</div>
