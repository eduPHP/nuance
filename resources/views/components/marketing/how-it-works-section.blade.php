@props([
    'steps' => [],
])

<section id="how-it-works" class="px-6 py-20 md:py-28">
    <div class="mx-auto max-w-6xl">
        <x-marketing.section-heading
            eyebrow="How It Works"
            title="Three steps to authentic writing"
            description="Simple, fast, and transparent."
        />

        <div class="grid gap-8 md:grid-cols-3">
            @foreach ($steps as $index => $step)
                <x-marketing.step-card
                    :number="$step['number']"
                    :title="$step['title']"
                    :description="$step['description']"
                    :show-connector="$index < count($steps) - 1"
                />
            @endforeach
        </div>
    </div>
</section>
