@props([
    'title',
    'description',
])

<article class="group rounded-2xl border border-border/60 bg-card p-7 transition-all hover:border-primary/30 hover:shadow-lg hover:shadow-primary/5">
    <div class="mb-4 h-12 w-12 rounded-xl bg-accent transition-colors group-hover:bg-primary"></div>
    <h3 class="mb-2 text-lg font-semibold text-foreground">{{ $title }}</h3>
    <p class="text-sm leading-relaxed text-muted-foreground">{{ $description }}</p>
</article>
