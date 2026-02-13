<section class="px-6 py-20 md:py-28">
    <div class="mx-auto max-w-6xl">
        <div class="relative overflow-hidden rounded-3xl bg-primary px-8 py-16 text-center md:px-16 md:py-20">
            <div class="pointer-events-none absolute -left-16 -top-16 h-56 w-56 rounded-full bg-primary-foreground/10"></div>
            <div class="pointer-events-none absolute -bottom-8 -right-8 h-40 w-40 rounded-full bg-primary-foreground/5"></div>

            <div class="relative">
                <h2 class="mx-auto max-w-2xl text-balance text-3xl font-bold text-primary-foreground md:text-4xl">
                    Start writing with confidence today
                </h2>
                <p class="mx-auto mt-4 max-w-lg text-pretty text-lg text-primary-foreground/80">
                    Join thousands of writers, students, and professionals who trust Nuance to keep their content authentic.
                </p>
                @auth
                    <a href="{{ route('dashboard') }}" class="mt-8 inline-block rounded-full bg-card px-8 py-3 text-base font-semibold text-foreground shadow-xl transition hover:bg-card/90">
                        Open Dashboard
                    </a>
                @else
                    <a href="{{ route('register') }}" class="mt-8 inline-block rounded-full bg-card px-8 py-3 text-base font-semibold text-foreground shadow-xl transition hover:bg-card/90">
                        Try Nuance Free
                    </a>
                @endauth
            </div>
        </div>
    </div>
</section>
