<?php

use App\Services\MathematicalDetectionService;

test('detection signals show unique specific patterns for each critical section', function () {
    $service = new MathematicalDetectionService;
    
    // Text with multiple different AI patterns that will trigger critical sections
    $text = "In today's digital landscape, it's important to note that artificial intelligence is fundamentally transforming the way we approach complex problems and develop innovative solutions. Furthermore, we must delve into these emerging patterns to understand their implications for future technological advancement and strategic implementation. Moreover, I'd be happy to help explain how these sophisticated systems work in this case to provide clarity and comprehensive understanding.";
    
    $result = $service->analyze($text);
    
    // Should have critical sections
    expect($result->criticalSections)->not->toBeEmpty();
    
    // Get all unique reasons
    $reasons = array_map(fn($section) => $section['reason'], $result->criticalSections);
    
    // Each reason should be unique (no duplicates)
    $uniqueReasons = array_unique($reasons);
    expect(count($uniqueReasons))->toBe(count($reasons), 'All detection signals should be unique');
    
    // At least one reason should mention a specific pattern
    $hasSpecificPattern = false;
    foreach ($reasons as $reason) {
        if (
            str_contains($reason, 'AI phrase:') ||
            str_contains($reason, 'pattern:') ||
            str_contains($reason, 'diversity')
        ) {
            $hasSpecificPattern = true;
            break;
        }
    }
    
    expect($hasSpecificPattern)->toBeTrue('At least one detection signal should mention a specific pattern');
});
