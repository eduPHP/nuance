<section class="relative overflow-hidden px-6 py-20 md:py-32">
    <div class="pointer-events-none absolute inset-0 overflow-hidden">
        <div class="absolute -right-32 -top-32 h-96 w-96 rounded-full bg-primary/5"></div>
        <div class="absolute -left-20 bottom-0 h-72 w-72 rounded-full bg-primary/10"></div>
    </div>

    <div class="relative mx-auto max-w-6xl">
        <div class="flex flex-col items-center text-center">
            <div class="mb-8 inline-flex items-center gap-2 rounded-full border border-primary/20 bg-accent px-4 py-2 text-sm font-medium text-accent-foreground">
                <span>AI Detection You Can Trust</span>
            </div>

            <h1 class="max-w-4xl text-balance text-4xl font-bold leading-tight tracking-tight text-foreground md:text-6xl lg:text-7xl">
                Know if your text
                <br>
                <span class="text-primary">sounds human</span>
            </h1>

            <p class="mt-6 max-w-2xl text-pretty text-lg leading-relaxed text-muted-foreground md:text-xl">
                Instantly detect AI-generated content, pinpoint the most suspicious passages,
                and rewrite them to sound authentically yours.
            </p>

            <div class="mt-10 flex flex-col items-center gap-4 sm:flex-row">
                <a href="{{ route('free-analysis') }}" wire:navigate class="rounded-full bg-primary px-8 py-3 text-base font-semibold text-primary-foreground shadow-lg shadow-primary/25 transition hover:bg-primary/90 hover:shadow-xl hover:shadow-primary/30">
                    Analyze Your Text
                </a>
                <a href="#how-it-works" class="rounded-full border border-border bg-card px-8 py-3 text-base font-semibold text-foreground transition hover:bg-secondary">
                    See How It Works
                </a>
            </div>

            <div class="mt-16 flex flex-wrap items-center justify-center gap-8 text-sm text-muted-foreground">
                <div class="flex items-center gap-2">
                    <span>99.2% accuracy rate</span>
                </div>
                <div class="h-4 w-px bg-border"></div>
                <span>Trusted by 50,000+ writers</span>
                <div class="hidden h-4 w-px bg-border sm:block"></div>
                <span class="hidden sm:block">Encrypted & secure</span>
            </div>

            @php
                use App\DataTransferObjects\DetectionResult;
                
                $mockResult = new DetectionResult(
                    aiConfidence: 72.5,
                    perplexityScore: 45.2,
                    burstinessScore: 0.15,
                    diversityScore: 0.42,
                    criticalSections: [
                        [
                            'start' => 0,
                            'end' => 28,
                            'confidence' => 95.0,
                            'reason' => "Common GPT writing pattern: 'in today\'s digital landscape'",
                            'text' => "In today's digital landscape",
                        ],
                        [
                            'start' => 30,
                            'end' => 52,
                            'confidence' => 92.0,
                            'reason' => "Detected common AI phrase: 'it\'s important to note'",
                            'text' => "it's important to note",
                        ],
                        [
                            'start' => 89,
                            'end' => 100,
                            'confidence' => 78.0,
                            'reason' => "Detected common AI phrase: 'Furthermore'",
                            'text' => "Furthermore",
                        ],
                        [
                            'start' => 110,
                            'end' => 120,
                            'confidence' => 95.0,
                            'reason' => "Detected common AI phrase: 'delve into'",
                            'text' => "delve into",
                        ],
                        [
                            'start' => 155,
                            'end' => 156,
                            'confidence' => 98.0,
                            'reason' => "Advanced AI fingerprint (em-dash usage)",
                            'text' => "—",
                        ],
                        [
                            'start' => 191,
                            'end' => 206,
                            'confidence' => 82.0,
                            'reason' => "Common Claude writing pattern: 'i\'d be happy to'",
                            'text' => "I'd be happy to",
                        ],
                    ],
                    likelyModel: 'GPT',
                    modelConfidence: 68.5,
                    originalText: "In today's digital landscape, it's important to note that AI is transforming everything. Furthermore, we must delve into these patterns to understand the implications — even when they seem clear. I'd be happy to help explain how these systems work in this particular case."
                );
            @endphp
            
            <div class="mt-16 w-full max-w-4xl">
                <x-marketing.analysis-preview :result="$mockResult" />
            </div>
        </div>
    </div>
</section>
