@props([
    'plans' => [],
])

<section id="pricing" class="px-6 py-20 md:py-28">
    <div class="mx-auto max-w-6xl">
        <x-marketing.section-heading
            eyebrow="Pricing"
            title="Simple, transparent pricing"
            description="Start free and upgrade when you need more power."
        />

        <div class="grid items-start gap-6 md:grid-cols-3">
            @foreach ($plans as $plan)
                <x-marketing.pricing-card :plan="$plan" />
            @endforeach
        </div>
    </div>
</section>
