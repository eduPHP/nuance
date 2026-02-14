@props(['result'])

<div class="overflow-hidden rounded-2xl border border-border bg-card shadow-2xl shadow-primary/5">
    <div class="flex items-center gap-2 border-b border-border px-5 py-3">
        <div class="h-3 w-3 rounded-full bg-destructive/40"></div>
        <div class="h-3 w-3 rounded-full bg-primary/40"></div>
        <div class="h-3 w-3 rounded-full bg-muted-foreground/30"></div>
        <span class="ml-3 text-xs text-muted-foreground">/analysis-tool</span>
    </div>
    <div class="p-6 md:p-8">
        <div class="flex items-center justify-between gap-3">
            <span class="text-sm font-medium text-muted-foreground">AI Detection Result</span>
            <span @class([
                'rounded-full px-3 py-1 text-sm font-semibold',
                'bg-green-100 text-green-700' => $result->isLikelyHuman(),
                'bg-yellow-100 text-yellow-700' => $result->isMixed(),
                'bg-red-100 text-red-700' => $result->isLikelyAi(),
            ])>
                {{ $result->aiConfidence }}% AI Detected
            </span>
        </div>
        
        @if ($result->likelyModel)
            <div class="mt-3 flex items-center gap-2">
                <span class="text-xs font-medium text-muted-foreground">Detected Model:</span>
                <span class="rounded-full bg-primary/10 px-3 py-1 text-xs font-semibold text-primary">
                    {{ $result->likelyModel }} ({{ $result->modelConfidence }}% confidence)
                </span>
            </div>
        @endif
        
        <div class="mt-4 text-left text-base leading-relaxed text-foreground">
            @php
                $text = '';
                if ($result->criticalSections) {
                    $originalText = $result->criticalSections[0]['text'] ?? ''; // This is a bit flawed if we only have critical sections
                    // Actually, the service should probably return the full text or we should pass it here.
                    // For now, let's assume the preview shows the highlighted chunks.
                }
            @endphp
            
            <div class="space-y-4">
                @foreach ($result->criticalSections as $section)
                    <p>
                        <span @class([
                            'rounded px-1',
                            'bg-red-100 text-red-700' => $section['confidence'] > 80,
                            'bg-yellow-100 text-yellow-700' => $section['confidence'] <= 80,
                        ]) title="{{ $section['reason'] }}">
                            {{ $section['text'] }}
                        </span>
                    </p>
                @endforeach
                
                @if (empty($result->criticalSections))
                    <p class="text-muted-foreground italic">No suspicious passages detected. The text appears to have natural human-like variation.</p>
                @endif
            </div>
        </div>

        @if ($result->criticalSections)
            <div class="mt-6 flex flex-col gap-2 border-t border-border pt-4">
                <p class="text-xs font-semibold text-muted-foreground uppercase tracking-wider">Detection Signals:</p>
                <ul class="space-y-1">
                    @foreach (collect($result->criticalSections)->take(3) as $section)
                        <li class="text-xs text-muted-foreground flex gap-2">
                            <span class="text-primary">•</span>
                            {{ $section['reason'] }}
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>
</div>
