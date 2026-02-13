@props([
    'links' => [],
])

<details class="relative md:hidden">
    <summary class="cursor-pointer list-none rounded-lg border border-border bg-card px-3 py-2 text-sm font-medium text-foreground">Menu</summary>
    <div class="absolute right-0 mt-2 w-56 rounded-xl border border-border bg-popover p-2 shadow-lg">
        @foreach ($links as $link)
            <a href="{{ $link['href'] }}" class="block rounded-lg px-3 py-2 text-sm text-muted-foreground hover:bg-secondary hover:text-foreground">{{ $link['label'] }}</a>
        @endforeach
        <div class="my-2 border-t border-border"></div>
        <x-marketing.header-actions mobile />
    </div>
</details>
