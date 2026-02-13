@props([
    'links' => [],
    'itemClass' => 'text-sm font-medium text-muted-foreground transition-colors hover:text-foreground',
])

<div {{ $attributes->class('items-center gap-8') }}>
    @foreach ($links as $link)
        <a href="{{ $link['href'] }}" class="{{ $itemClass }}">{{ $link['label'] }}</a>
    @endforeach
</div>
