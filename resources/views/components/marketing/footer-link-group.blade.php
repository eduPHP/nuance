@props([
    'title',
    'links' => [],
])

<div class="flex flex-col gap-3">
    <span class="text-xs font-semibold uppercase tracking-widest text-foreground">{{ $title }}</span>
    @foreach ($links as $link)
        <a href="{{ $link['href'] }}" class="text-sm text-muted-foreground hover:text-foreground">{{ $link['label'] }}</a>
    @endforeach
</div>
