@props([
    'links' => [],
])

<header class="sticky top-0 z-50 border-b border-border/50 bg-background/80 backdrop-blur-xl">
    <nav class="mx-auto flex w-full max-w-6xl items-center justify-between px-6 py-4">
        <x-marketing.brand />

        <x-marketing.nav-links :links="$links" class="hidden md:flex" />

        <div class="hidden items-center gap-3 md:flex">
            <x-marketing.header-actions />
        </div>

        <x-marketing.mobile-menu :links="$links" />
    </nav>
</header>
