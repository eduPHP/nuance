@props(['result'])

<div class="overflow-hidden rounded-2xl border border-border bg-card shadow-2xl shadow-primary/5">
    <div class="flex items-center gap-2 border-b border-border px-5 py-3">
        <div class="h-3 w-3 rounded-full bg-red-400/60"></div>
        <div class="h-3 w-3 rounded-full bg-amber-400/60"></div>
        <div class="h-3 w-3 rounded-full bg-emerald-400/60"></div>
        <span class="ml-3 text-xs font-medium text-muted-foreground">{{ parse_url(config('app.url'), PHP_URL_HOST) }}/tool</span>
    </div>
    <div class="p-6 md:p-8">
        <div class="flex items-center justify-between gap-3 mb-6">
            <span class="text-sm font-semibold text-muted-foreground">AI Detection Result</span>
            <span @class([
                'rounded-full px-4 py-1.5 text-sm font-bold shadow-sm',
                'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200' => $result->isLikelyHuman(),
                'bg-amber-50 text-amber-700 ring-1 ring-amber-200' => $result->isMixed(),
                'bg-red-50 text-red-700 ring-1 ring-red-200' => $result->isLikelyAi(),
            ])>
                {{ $result->aiConfidence }}% AI Detected
            </span>
        </div>
        
        <div class="text-left text-base leading-relaxed text-foreground md:text-lg">
            @php
                $text = $result->originalText;
                $sections = collect($result->criticalSections)->sortBy('start')->values()->toArray();
                $output = '';
                $currentIdx = 0;

                foreach ($sections as $section) {
                    $start = $section['start'];
                    $end = $section['end'];

                    // Skip overlap
                    if ($start < $currentIdx) {
                        continue;
                    }

                    // Text before highlight
                    $output .= e(substr($text, $currentIdx, $start - $currentIdx));

                    // Highlighted part
                    $confidence = $section['confidence'];
                    $highlightClass = $confidence >= 85 
                        ? 'bg-red-100 text-red-800 border-b-2 border-red-300' 
                        : 'bg-amber-100 text-amber-800 border-b-2 border-amber-300';
                    
                    $output .= '<span class="px-1 rounded-sm cursor-help transition-all hover:brightness-95 ' . $highlightClass . '" title="' . e($section['reason']) . '">' . e(substr($text, $start, $end - $start)) . '</span>';
                    
                    $currentIdx = $end;
                }

                // Remaining text
                $output .= e(substr($text, $currentIdx));
                
                // Handle newlines for display
                // If it's short, just keep it. If long, split into paragraphs.
                $paragraphs = explode("\n", $output);
            @endphp
            
            <div class="space-y-4">
                @foreach ($paragraphs as $para)
                    @if (trim($para))
                        <p>{!! $para !!}</p>
                    @endif
                @endforeach
                
                @if (empty($result->criticalSections) && $result->isLikelyHuman())
                    <p class="text-muted-foreground italic text-sm">No suspicious passages detected. The text appears to have natural human-like variation.</p>
                @endif
            </div>
        </div>

        @if ($result->criticalSections)
            <div class="mt-8 flex flex-wrap gap-4 border-t border-border pt-6">
                <div class="flex items-center gap-2">
                    <div class="h-3 w-3 rounded-full bg-red-400"></div>
                    <span class="text-xs font-medium text-muted-foreground">High AI probability</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="h-3 w-3 rounded-full bg-amber-400"></div>
                    <span class="text-xs font-medium text-muted-foreground">Moderate</span>
                </div>
            </div>
        @endif
    </div>
</div>
