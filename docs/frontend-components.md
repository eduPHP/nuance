# Frontend Components with DaisyUI

## Overview

DaisyUI provides beautiful, customizable components that work seamlessly with Livewire. This document outlines the component library setup and key UI components for the Humanizer app.

## DaisyUI Setup

### Installation

```bash
npm install -D daisyui@latest
```

### Tailwind Configuration

```javascript
// tailwind.config.js
export default {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./app/Livewire/**/*.php",
  ],
  theme: {
    extend: {
      colors: {
        // Custom brand colors
        'brand-primary': '#3b82f6',
        'brand-secondary': '#8b5cf6',
      },
    },
  },
  plugins: [
    require('daisyui'),
  ],
  daisyui: {
    themes: [
      {
        humanizer: {
          "primary": "#3b82f6",      // Blue
          "secondary": "#8b5cf6",    // Purple
          "accent": "#10b981",       // Green
          "neutral": "#1f2937",      // Dark gray
          "base-100": "#ffffff",     // White background
          "base-200": "#f3f4f6",     // Light gray
          "base-300": "#e5e7eb",     // Medium gray
          "info": "#0ea5e9",         // Sky blue
          "success": "#10b981",      // Green
          "warning": "#f59e0b",      // Amber
          "error": "#ef4444",        // Red
        },
      },
      "dark", // Include dark mode
    ],
    darkTheme: "dark",
    base: true,
    styled: true,
    utils: true,
  },
}
```

### CSS Import

```css
/* resources/css/app.css */
@tailwind base;
@tailwind components;
@tailwind utilities;

/* Custom component styles */
@layer components {
  .card-hover {
    @apply transition-all duration-300 hover:shadow-xl hover:-translate-y-1;
  }
  
  .gradient-primary {
    @apply bg-linear-to-r from-primary to-secondary;
  }
  
  .text-gradient {
    @apply bg-clip-text text-transparent bg-linear-to-r from-primary to-secondary;
  }
}
```

## Key Components

### 1. Landing Page Hero

```blade
<!-- resources/views/components/hero.blade.php -->
<div class="hero min-h-screen bg-base-200">
    <div class="hero-content text-center">
        <div class="max-w-4xl">
            <h1 class="text-6xl font-bold">
                Make Your Text 
                <span class="text-gradient">Sound Human</span>
            </h1>
            <p class="py-6 text-xl text-base-content/70">
                Detect AI-generated content and rewrite it using your unique writing style. 
                Powered by advanced mathematical analysis and AI.
            </p>
            
            <div class="flex gap-4 justify-center">
                @auth
                    <a href="{{ route('dashboard') }}" class="btn btn-primary btn-lg">
                        Go to Dashboard
                    </a>
                @else
                    <a href="{{ route('register') }}" class="btn btn-primary btn-lg">
                        Get Started Free
                    </a>
                    <a href="{{ route('login') }}" class="btn btn-outline btn-lg">
                        Sign In
                    </a>
                @endauth
            </div>
            
            <!-- Stats -->
            <div class="stats stats-vertical lg:stats-horizontal shadow mt-12">
                <div class="stat">
                    <div class="stat-title">Accuracy</div>
                    <div class="stat-value text-primary">85%</div>
                    <div class="stat-desc">AI Detection Rate</div>
                </div>
                
                <div class="stat">
                    <div class="stat-title">Speed</div>
                    <div class="stat-value text-secondary">< 100ms</div>
                    <div class="stat-desc">Instant Analysis</div>
                </div>
                
                <div class="stat">
                    <div class="stat-title">Users</div>
                    <div class="stat-value">1,000+</div>
                    <div class="stat-desc">And Growing</div>
                </div>
            </div>
        </div>
    </div>
</div>
```

### 2. Document Submission Form

```blade
<!-- resources/views/livewire/documents/create.blade.php -->
<div class="card bg-base-100 shadow-xl">
    <div class="card-body">
        <h2 class="card-title">Submit Document for Analysis</h2>
        
        @if($remainingDocuments !== null)
            <div class="alert alert-info">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                </svg>
                <span>{{ $remainingDocuments }} documents remaining on free tier</span>
            </div>
        @endif
        
        <form wire:submit="submit">
            <!-- Title -->
            <div class="form-control w-full">
                <label class="label">
                    <span class="label-text">Document Title</span>
                </label>
                <input 
                    type="text" 
                    wire:model="title"
                    placeholder="My Essay" 
                    class="input input-bordered w-full @error('title') input-error @enderror"
                />
                @error('title')
                    <label class="label">
                        <span class="label-text-alt text-error">{{ $message }}</span>
                    </label>
                @enderror
            </div>
            
            <!-- Content -->
            <div class="form-control w-full mt-4">
                <label class="label">
                    <span class="label-text">Content</span>
                    <span class="label-text-alt">Minimum 50 words</span>
                </label>
                <textarea 
                    wire:model="content"
                    class="textarea textarea-bordered h-48 @error('content') textarea-error @enderror"
                    placeholder="Paste your text here..."
                ></textarea>
                @error('content')
                    <label class="label">
                        <span class="label-text-alt text-error">{{ $message }}</span>
                    </label>
                @enderror
            </div>
            
            <!-- Submit Button -->
            <div class="card-actions justify-end mt-6">
                <button 
                    type="submit" 
                    class="btn btn-primary"
                    wire:loading.attr="disabled"
                >
                    <span wire:loading.remove>Analyze Document</span>
                    <span wire:loading class="loading loading-spinner loading-sm"></span>
                    <span wire:loading>Analyzing...</span>
                </button>
            </div>
        </form>
    </div>
</div>
```

### 3. Analysis Results Display

```blade
<!-- resources/views/livewire/documents/analysis.blade.php -->
<div class="space-y-6">
    <!-- AI Confidence Card -->
    <div class="card bg-base-100 shadow-xl">
        <div class="card-body">
            <h2 class="card-title">AI Detection Results</h2>
            
            <div class="stats stats-vertical lg:stats-horizontal shadow">
                <div class="stat">
                    <div class="stat-figure text-primary">
                        <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="stat-title">AI Confidence</div>
                    <div class="stat-value text-primary">{{ number_format($aiConfidence, 1) }}%</div>
                    <div class="stat-desc">
                        @if($aiConfidence > 70)
                            Likely AI-generated
                        @elseif($aiConfidence > 40)
                            Mixed or edited
                        @else
                            Likely human-written
                        @endif
                    </div>
                </div>
                
                <div class="stat">
                    <div class="stat-title">Perplexity</div>
                    <div class="stat-value text-sm">{{ number_format($perplexityScore, 2) }}</div>
                    <div class="stat-desc">Text predictability</div>
                </div>
                
                <div class="stat">
                    <div class="stat-title">Burstiness</div>
                    <div class="stat-value text-sm">{{ number_format($burstinessScore, 2) }}</div>
                    <div class="stat-desc">Sentence variation</div>
                </div>
            </div>
            
            <!-- Progress Bar -->
            <div class="mt-4">
                <div class="flex justify-between text-sm mb-1">
                    <span>Human</span>
                    <span>AI</span>
                </div>
                <progress 
                    class="progress @if($aiConfidence > 70) progress-error @elseif($aiConfidence > 40) progress-warning @else progress-success @endif w-full" 
                    value="{{ $aiConfidence }}" 
                    max="100"
                ></progress>
            </div>
        </div>
    </div>
    
    <!-- Critical Sections -->
    @if(count($criticalSections) > 0)
        <div class="card bg-base-100 shadow-xl">
            <div class="card-body">
                <h3 class="card-title">Critical Sections</h3>
                <p class="text-sm text-base-content/70">
                    These parts of your text show high AI probability
                </p>
                
                <div class="space-y-4 mt-4">
                    @foreach($criticalSections as $section)
                        <div class="alert alert-warning">
                            <div class="flex-1">
                                <p class="font-medium">{{ $section['text'] }}</p>
                                <div class="flex items-center gap-4 mt-2 text-sm">
                                    <span class="badge badge-warning">
                                        {{ number_format($section['confidence'], 1) }}% AI
                                    </span>
                                    <span class="text-base-content/60">
                                        {{ $section['reason'] }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
    
    <!-- Rewrite Button (Pro Only) -->
    @can('create', App\Models\Rewrite::class)
        @if($aiConfidence > 50)
            <livewire:documents.rewrite-button :document="$document" />
        @endif
    @else
        @if($aiConfidence > 50)
            <div class="card bg-linear-to-r from-primary to-secondary text-primary-content shadow-xl">
                <div class="card-body">
                    <h3 class="card-title">Want to humanize this text?</h3>
                    <p>Upgrade to Pro to unlock AI-powered rewriting using your writing style.</p>
                    <div class="card-actions justify-end">
                        <a href="{{ route('pricing') }}" class="btn btn-neutral">
                            Upgrade to Pro
                        </a>
                    </div>
                </div>
            </div>
        @endif
    @endcan
</div>
```

### 4. Rewrite Progress

```blade
<!-- resources/views/livewire/rewrites/progress.blade.php -->
<div class="card bg-base-100 shadow-xl">
    <div class="card-body">
        <h2 class="card-title">Rewriting in Progress</h2>
        
        <div class="flex flex-col items-center gap-4 py-8">
            @if($status === 'queued')
                <div class="loading loading-ring loading-lg text-primary"></div>
                <p class="text-lg">Queued for processing...</p>
            @elseif($status === 'processing')
                <div class="radial-progress text-primary" style="--value:{{ $progress }};">
                    {{ $progress }}%
                </div>
                <p class="text-lg">Analyzing your writing style...</p>
            @elseif($status === 'completed')
                <svg class="w-16 h-16 text-success" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <p class="text-lg font-semibold text-success">Rewrite Complete!</p>
            @elseif($status === 'failed')
                <svg class="w-16 h-16 text-error" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
                <p class="text-lg font-semibold text-error">Rewrite Failed</p>
            @endif
            
            <!-- Progress Steps -->
            <ul class="steps steps-vertical lg:steps-horizontal mt-8">
                <li class="step step-primary">Queued</li>
                <li class="step @if($progress > 0) step-primary @endif">Processing</li>
                <li class="step @if($status === 'completed') step-primary @endif">Complete</li>
            </ul>
        </div>
    </div>
</div>
```

### 5. Diff Viewer

```blade
<!-- resources/views/livewire/rewrites/diff-view.blade.php -->
<div class="card bg-base-100 shadow-xl">
    <div class="card-body">
        <div class="flex justify-between items-center">
            <h2 class="card-title">Comparison</h2>
            
            <div class="stats stats-horizontal shadow">
                <div class="stat py-2 px-4">
                    <div class="stat-title text-xs">Similarity</div>
                    <div class="stat-value text-sm">{{ $diff['similarity_percentage'] }}%</div>
                </div>
                <div class="stat py-2 px-4">
                    <div class="stat-title text-xs">Changes</div>
                    <div class="stat-value text-sm text-success">+{{ $diff['additions'] }}</div>
                </div>
                <div class="stat py-2 px-4">
                    <div class="stat-title text-xs">Deletions</div>
                    <div class="stat-value text-sm text-error">-{{ $diff['deletions'] }}</div>
                </div>
            </div>
        </div>
        
        <!-- Tab View -->
        <div role="tablist" class="tabs tabs-boxed mt-4">
            <input type="radio" name="diff_tabs" role="tab" class="tab" aria-label="Side by Side" checked />
            <div role="tabpanel" class="tab-content p-6">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                    <!-- Original -->
                    <div>
                        <h3 class="font-semibold mb-2">Original</h3>
                        <div class="prose max-w-none p-4 bg-base-200 rounded-lg">
                            {{ $document->content }}
                        </div>
                    </div>
                    
                    <!-- Rewritten -->
                    <div>
                        <h3 class="font-semibold mb-2">Rewritten</h3>
                        <div class="prose max-w-none p-4 bg-base-200 rounded-lg">
                            {{ $rewrite->rewritten_content }}
                        </div>
                    </div>
                </div>
            </div>
            
            <input type="radio" name="diff_tabs" role="tab" class="tab" aria-label="Unified" />
            <div role="tabpanel" class="tab-content p-6">
                <div class="space-y-2">
                    @foreach($diff['changes'] as $change)
                        @if($change['type'] === 'replace')
                            <div class="diff-line">
                                <div class="bg-error/10 text-error p-2 rounded">
                                    <span class="font-mono text-sm">- {{ $change['original'] }}</span>
                                </div>
                                <div class="bg-success/10 text-success p-2 rounded">
                                    <span class="font-mono text-sm">+ {{ $change['rewritten'] }}</span>
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>
        
        <!-- Actions -->
        <div class="card-actions justify-end mt-6">
            <button class="btn btn-outline" onclick="navigator.clipboard.writeText('{{ addslashes($rewrite->rewritten_content) }}')">
                Copy Rewritten Text
            </button>
            <button class="btn btn-primary" wire:click="download">
                Download
            </button>
        </div>
    </div>
</div>
```

### 6. Sample Management

```blade
<!-- resources/views/livewire/samples/manage.blade.php -->
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h2 class="text-2xl font-bold">Writing Samples</h2>
        <button class="btn btn-primary" onclick="upload_modal.showModal()">
            Add Sample
        </button>
    </div>
    
    <!-- Samples Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($samples as $sample)
            <div class="card bg-base-100 shadow-xl card-hover">
                <div class="card-body">
                    <div class="flex justify-between items-start">
                        <h3 class="card-title text-lg">{{ $sample->title }}</h3>
                        
                        @if($sample->is_valid)
                            <span class="badge badge-success">Valid</span>
                        @else
                            <span class="badge badge-error">Invalid</span>
                        @endif
                    </div>
                    
                    <p class="text-sm text-base-content/70 line-clamp-3">
                        {{ $sample->content }}
                    </p>
                    
                    @if(!$sample->is_valid)
                        <div class="alert alert-warning mt-2">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-xs">{{ $sample->ai_confidence }}% AI detected</span>
                        </div>
                    @endif
                    
                    <div class="card-actions justify-end mt-4">
                        <button class="btn btn-sm btn-ghost" wire:click="delete({{ $sample->id }})">
                            Delete
                        </button>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full text-center py-12">
                <p class="text-base-content/60">No samples yet. Add 3-6 samples to enable rewriting.</p>
            </div>
        @endforelse
    </div>
    
    <!-- Upload Modal -->
    <dialog id="upload_modal" class="modal">
        <div class="modal-box w-11/12 max-w-2xl">
            <h3 class="font-bold text-lg">Upload Writing Sample</h3>
            <form wire:submit="upload" class="mt-4">
                <div class="form-control">
                    <label class="label">
                        <span class="label-text">Title</span>
                    </label>
                    <input type="text" wire:model="title" class="input input-bordered" />
                </div>
                
                <div class="form-control mt-4">
                    <label class="label">
                        <span class="label-text">Content</span>
                    </label>
                    <textarea wire:model="content" class="textarea textarea-bordered h-48"></textarea>
                </div>
                
                <div class="modal-action">
                    <button type="button" class="btn" onclick="upload_modal.close()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Upload</button>
                </div>
            </form>
        </div>
    </dialog>
</div>
```

## Theme Customization

Users can switch between light/dark themes:

```blade
<!-- resources/views/components/theme-toggle.blade.php -->
<label class="swap swap-rotate">
    <input type="checkbox" class="theme-controller" value="dark" />
    
    <!-- Sun icon -->
    <svg class="swap-on fill-current w-6 h-6" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
        <path d="M5.64,17l-.71.71a1,1,0,0,0,0,1.41,1,1,0,0,0,1.41,0l.71-.71A1,1,0,0,0,5.64,17ZM5,12a1,1,0,0,0-1-1H3a1,1,0,0,0,0,2H4A1,1,0,0,0,5,12Zm7-7a1,1,0,0,0,1-1V3a1,1,0,0,0-2,0V4A1,1,0,0,0,12,5ZM5.64,7.05a1,1,0,0,0,.7.29,1,1,0,0,0,.71-.29,1,1,0,0,0,0-1.41l-.71-.71A1,1,0,0,0,4.93,6.34Zm12,.29a1,1,0,0,0,.7-.29l.71-.71a1,1,0,1,0-1.41-1.41L17,5.64a1,1,0,0,0,0,1.41A1,1,0,0,0,17.66,7.34ZM21,11H20a1,1,0,0,0,0,2h1a1,1,0,0,0,0-2Zm-9,8a1,1,0,0,0-1,1v1a1,1,0,0,0,2,0V20A1,1,0,0,0,12,19ZM18.36,17A1,1,0,0,0,17,18.36l.71.71a1,1,0,0,0,1.41,0,1,1,0,0,0,0-1.41ZM12,6.5A5.5,5.5,0,1,0,17.5,12,5.51,5.51,0,0,0,12,6.5Zm0,9A3.5,3.5,0,1,1,15.5,12,3.5,3.5,0,0,1,12,15.5Z"/>
    </svg>
    
    <!-- Moon icon -->
    <svg class="swap-off fill-current w-6 h-6" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
        <path d="M21.64,13a1,1,0,0,0-1.05-.14,8.05,8.05,0,0,1-3.37.73A8.15,8.15,0,0,1,9.08,5.49a8.59,8.59,0,0,1,.25-2A1,1,0,0,0,8,2.36,10.14,10.14,0,1,0,22,14.05,1,1,0,0,0,21.64,13Zm-9.5,6.69A8.14,8.14,0,0,1,7.08,5.22v.27A10.15,10.15,0,0,0,17.22,15.63a9.79,9.79,0,0,0,2.1-.22A8.11,8.11,0,0,1,12.14,19.73Z"/>
    </svg>
</label>

<script>
    // Persist theme preference
    const toggle = document.querySelector('.theme-controller');
    toggle.addEventListener('change', (e) => {
        localStorage.setItem('theme', e.target.checked ? 'dark' : 'humanizer');
    });
    
    // Load saved theme
    const savedTheme = localStorage.getItem('theme') || 'humanizer';
    document.documentElement.setAttribute('data-theme', savedTheme);
    if (savedTheme === 'dark') toggle.checked = true;
</script>
```

## Responsive Design

All components are mobile-first and responsive:

- Use `lg:` prefix for desktop layouts
- Stack cards vertically on mobile
- Use `drawer` component for mobile navigation
- Ensure touch-friendly button sizes (min 44x44px)
