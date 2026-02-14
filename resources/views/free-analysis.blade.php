<x-marketing.layout :title="'Free Analysis - '.config('app.name', 'Nuance')">
    @php
        $navigationLinks = [
            ['label' => 'Home', 'href' => route('home')],
            ['label' => 'Analyzer', 'href' => '#analyzer'],
            ['label' => 'How It Works', 'href' => '#how-it-works'],
            ['label' => 'Pricing', 'href' => route('home').'#pricing'],
        ];

        $toolFeatures = [
            ['title' => 'Passage Highlighting', 'description' => 'Flag suspicious phrases instantly with clear color-coded emphasis.'],
            ['title' => 'Confidence Scoring', 'description' => 'Review an overall AI probability and sentence-level signal strength.'],
            ['title' => 'Rewrite Guidance', 'description' => 'Get practical rewrite suggestions to improve natural tone and cadence.'],
        ];

        $steps = [
            ['number' => '01', 'title' => 'Paste Your Text', 'description' => 'Drop in any paragraph, draft, or article you want to verify.'],
            ['number' => '02', 'title' => 'Review Detection Signals', 'description' => 'See highlighted passages and confidence indicators in seconds.'],
            ['number' => '03', 'title' => 'Refine & Rewrite', 'description' => 'Use suggestions to make the text sound more authentically human.'],
        ];
    @endphp

    <x-marketing.header :links="$navigationLinks" />

    <main>
        <section id="analyzer" class="px-6 py-12 md:py-16">
            <div class="mx-auto max-w-6xl">
                <x-marketing.section-heading
                    eyebrow="Free Analysis"
                    title="AI Text Analyzer"
                    description="Paste any text below to check if it was written by AI. We highlight suspicious passages and provide rewrite-ready guidance."
                />

                <div class="mx-auto max-w-5xl">
                    <div class="rounded-2xl border border-border/60 bg-card shadow-sm">
                        <div class="flex items-center justify-between border-b border-border/50 px-5 py-3">
                            <h2 class="text-sm font-semibold text-foreground">Input Text</h2>
                            <div class="flex items-center gap-2">
                                <button type="button" class="rounded-lg px-3 py-1.5 text-xs font-medium text-muted-foreground transition-colors hover:bg-secondary hover:text-foreground">
                                    Paste
                                </button>
                                <button type="button" class="rounded-lg px-3 py-1.5 text-xs font-medium text-muted-foreground transition-colors hover:bg-secondary hover:text-foreground">
                                    Clear
                                </button>
                            </div>
                        </div>

                        <textarea placeholder="Paste or type the text you want to analyze for AI-generated content..." class="min-h-[220px] w-full resize-none bg-transparent px-5 py-4 text-sm leading-relaxed text-foreground placeholder:text-muted-foreground/60 focus:outline-none md:text-base" aria-label="Text to analyze"></textarea>

                        <div class="flex flex-col gap-3 border-t border-border/50 px-5 py-3 sm:flex-row sm:items-center sm:justify-between">
                            <div class="flex items-center gap-4">
                                <span class="text-xs text-muted-foreground">0 words</span>
                            </div>

                            <button type="button" class="rounded-full bg-primary px-6 py-2.5 text-sm font-semibold text-primary-foreground shadow-md shadow-primary/20 transition hover:bg-primary/90">
                                Analyze Text
                            </button>
                        </div>

                        <details class="border-t border-border/50 px-5 py-4">
                            <summary class="cursor-pointer text-xs font-medium text-primary">Writing samples (optional)</summary>
                            <label class="mt-3 block text-xs text-muted-foreground">Add samples of your normal writing voice to get better rewrite suggestions.</label>
                            <textarea placeholder="Paste your writing samples here..." class="mt-2 min-h-[110px] w-full resize-none rounded-xl border border-border/60 bg-background p-3 text-sm leading-relaxed text-foreground placeholder:text-muted-foreground/60 focus:border-primary/50 focus:outline-none focus:ring-1 focus:ring-primary/20" aria-label="Your writing samples"></textarea>
                        </details>
                    </div>
                </div>

                <div class="mx-auto mt-8 max-w-5xl space-y-6">
                    <div class="rounded-2xl border border-border/60 bg-card p-6 shadow-sm">
                        <div class="flex flex-col gap-6 md:flex-row md:items-center md:justify-between">
                            <div class="flex items-center gap-4">
                                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-accent text-xl font-semibold text-accent-foreground">
                                    ✓
                                </div>
                                <div>
                                    <p class="text-lg font-bold text-foreground">Likely Human-Written</p>
                                    <p class="text-sm text-muted-foreground">Overall AI probability: 34%</p>
                                </div>
                            </div>

                            <div class="flex items-center gap-4">
                                <div class="h-3 w-48 overflow-hidden rounded-full bg-secondary">
                                    <div class="h-full rounded-full bg-primary" style="width: 34%;"></div>
                                </div>
                                <span class="font-mono text-2xl font-bold text-foreground">34%</span>
                            </div>
                        </div>
                    </div>

                    <x-marketing.analysis-preview />

                    <div class="rounded-2xl border border-border/60 bg-card p-6 shadow-sm">
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <h3 class="text-sm font-semibold text-foreground">Smart Rewrite</h3>
                                <p class="mt-1 text-xs text-muted-foreground">Rewrite flagged passages to sound more natural.</p>
                            </div>
                            <button type="button" class="rounded-full border border-primary/30 px-5 py-2 text-sm font-medium text-primary transition hover:bg-accent hover:text-accent-foreground">
                                Rewrite Now
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="px-6 pb-20 md:pb-28">
            <div class="mx-auto max-w-6xl">
                <x-marketing.section-heading
                    eyebrow="Included"
                    title="Everything you need in the free analyzer"
                    description="Use the same polished workflow as the main product while keeping this route open and accessible."
                />

                <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                    @foreach ($toolFeatures as $feature)
                        <x-marketing.feature-card :title="$feature['title']" :description="$feature['description']" />
                    @endforeach
                </div>
            </div>
        </section>

        <section id="how-it-works" class="px-6 py-20 md:py-28">
            <div class="mx-auto max-w-6xl">
                <x-marketing.section-heading
                    eyebrow="How It Works"
                    title="Analyze in three quick steps"
                    description="From input to rewrite guidance, the flow stays straightforward and fast."
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

        <x-marketing.cta-section />
    </main>

    <x-marketing.footer :product-links="$navigationLinks" />
</x-marketing.layout>
