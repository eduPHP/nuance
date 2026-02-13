@props([
    'size' => 'default',
])

@php
    $iconClasses = $size === 'small'
        ? 'h-8 w-8 text-xs'
        : 'h-9 w-9 text-sm';

    $nameClasses = $size === 'small'
        ? 'text-lg'
        : 'text-xl';
@endphp

<a href="{{ route('home') }}" {{ $attributes->class('flex items-center gap-2') }}>
    <span class="flex {{ $iconClasses }} items-center justify-center rounded-lg bg-primary font-bold text-primary-foreground">H</span>
    <span class="{{ $nameClasses }} font-bold tracking-tight text-foreground">Nuance</span>
</a>
