@props([
    'features' => [],
])

<section id="features" class="px-6 py-20 md:py-28">
    <div class="mx-auto max-w-6xl">
        <x-marketing.section-heading
            eyebrow="Features"
            title="Everything you need to verify your writing"
            description="From detection to rewriting, Nuance gives you the full toolkit to ensure your content reads as genuinely human."
        />

        <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
            @foreach ($features as $feature)
                <x-marketing.feature-card :title="$feature['title']" :description="$feature['description']" />
            @endforeach
        </div>
    </div>
</section>
