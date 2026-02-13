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
                @auth
                    <a href="{{ route('dashboard') }}" class="rounded-full bg-primary px-8 py-3 text-base font-semibold text-primary-foreground shadow-lg shadow-primary/25 transition hover:bg-primary/90 hover:shadow-xl hover:shadow-primary/30">
                        Analyze Your Text
                    </a>
                @else
                    <a href="{{ route('register') }}" class="rounded-full bg-primary px-8 py-3 text-base font-semibold text-primary-foreground shadow-lg shadow-primary/25 transition hover:bg-primary/90 hover:shadow-xl hover:shadow-primary/30">
                        Analyze Your Text
                    </a>
                @endauth
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

            <div class="mt-16 w-full max-w-4xl">
                <x-marketing.analysis-preview />
            </div>
        </div>
    </div>
</section>
