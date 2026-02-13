@props([
    'productLinks' => [],
])

@php
    $accountLinks = auth()->check()
        ? [['href' => route('dashboard'), 'label' => 'Dashboard']]
        : [['href' => route('login'), 'label' => 'Sign In'], ['href' => route('register'), 'label' => 'Get Started']];
@endphp

<footer class="border-t border-border/50 px-6 py-12">
    <div class="mx-auto max-w-6xl">
        <div class="flex flex-col items-center gap-8 md:flex-row md:items-start md:justify-between">
            <div class="flex flex-col items-center md:items-start">
                <x-marketing.brand size="small" />
                <p class="mt-3 max-w-xs text-center text-sm text-muted-foreground md:text-left">
                    AI text detection and humanization. Write with confidence, every time.
                </p>
            </div>

            <div class="flex gap-12">
                <x-marketing.footer-link-group title="Product" :links="$productLinks" />
                <x-marketing.footer-link-group title="Account" :links="$accountLinks" />
            </div>
        </div>

        <div class="mt-10 border-t border-border/50 pt-6 text-center text-xs text-muted-foreground">
            <p>{{ now()->year }} Nuance. All rights reserved.</p>
        </div>
    </div>
</footer>
