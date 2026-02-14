<div x-data="{
    theme: localStorage.getItem('theme') || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light'),
    init() {
        this.$watch('theme', (value) => {
            localStorage.setItem('theme', value);
            this.updateTheme();
        });
    },
    updateTheme() {
        if (this.theme === 'dark') {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    },
    toggle() {
        this.theme = this.theme === 'dark' ? 'light' : 'dark';
    }
}" class="flex items-center">
    <button 
        @click="toggle()" 
        type="button" 
        class="flex h-9 w-9 items-center justify-center rounded-full border border-border/60 bg-card text-muted-foreground transition-colors hover:bg-secondary hover:text-foreground focus:outline-hidden"
        :aria-label="theme === 'dark' ? 'Switch to light mode' : 'Switch to dark mode'"
    >
        <template x-if="theme === 'light'">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z" />
            </svg>
        </template>
        <template x-if="theme === 'dark'">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386-1.591 1.591M21 12h-2.25m-.386 6.364-1.591-1.591M12 18.75V21m-4.773-4.227-1.591 1.591M3 12h2.25m.386-6.364 1.591-1.591M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z" />
            </svg>
        </template>
    </button>
</div>