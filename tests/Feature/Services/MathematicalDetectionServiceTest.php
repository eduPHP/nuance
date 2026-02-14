<?php

use App\DataTransferObjects\DetectionResult;
use App\Services\MathematicalDetectionService;

beforeEach(function () {
    $this->service = new MathematicalDetectionService;
});

test('it can analyze text and return a DetectionResult', function () {
    $text = 'Artificial intelligence is transforming the digital landscape. It is important to note that many industries are adopting these new tools. Furthermore, the implementation of technology requires careful planning and strategic execution. In conclusion, the future of work will be shaped by these advancements. We need to delve into the details of this transition to understand its full impact. The evolution of large language models has created new opportunities for automation. Many professionals are curious about how these changes will affect their daily productivity. It is essential to develop a balanced perspective on innovation.';

    $result = $this->service->analyze($text);

    expect($result)->toBeInstanceOf(DetectionResult::class);
    expect($result->aiConfidence)->toBeGreaterThan(0);
    expect($result->perplexityScore)->toBeGreaterThan(0);
    expect($result->burstinessScore)->toBeGreaterThan(-1);
    expect($result->diversityScore)->toBeGreaterThan(0);
});

test('it detects AI-like text with higher confidence', function () {
    $aiText = "In today's digital landscape, it's important to note that artificial intelligence is playing a crucial role. Furthermore, the integration of automated systems continues to expand. Consequently, many organizations are now leveraging these tools to improve efficiency. In conclusion, the future of technology appears to be inextricably linked with AI development. Moreover, the rapid advancement of language models provides a significant boost to digital transformation efforts across all major sectors of the modern economy. It is worth mentioning that these systems are designed to optimize complex processes with high precision.";

    $result = $this->service->analyze($aiText);

    expect($result->aiConfidence)->toBeGreaterThan(50);
    expect($result->perplexityScore)->toBeLessThan(100);
    expect($result->burstinessScore)->toBeLessThan(0);
    expect($result->diversityScore)->toBeGreaterThan(0);
});

test('it detects human-like text with lower confidence', function () {
    $humanText = "I was just wandering around the park today when I saw the weirdest thing. A squirrel was trying to steal a whole slice of pizza from a trash can! It was actually quite impressed by the little guy's determination. I mean, who doesn't love pizza? Anyway, it made me laugh and I wanted to tell someone about it. Life is funny sometimes. I think I'll go back tomorrow and see if he's still there. Maybe I'll bring some actual nuts this time instead of just watching him struggle with junk food. It's the little moments like these that make my weekends so much better than the stressful work week.";

    $result = $this->service->analyze($humanText);

    expect($result->aiConfidence)->toBeLessThan(50);
    expect($result->perplexityScore)->toBeGreaterThan(100);
    expect($result->burstinessScore)->toBeGreaterThan(-0.6);
    expect($result->diversityScore)->toBeGreaterThan(0.5);
});

test('it identifies critical sections in AI-like text', function () {
    $text = "This is a normal sentence. However, in today's digital landscape, it's important to note that artificial intelligence is everywhere and fundamentally changing how we interact with data. Furthermore, we must delve into these patterns to ensure we are using technology ethically and effectively. This is another normal sentence that helps ground the discussion in reality. The rapid pace of change is truly unprecedented.";

    $result = $this->service->analyze($text);

    expect($result->criticalSections)->not->toBeEmpty();
});

test('it handles text too short for analysis', function () {
    $shortText = 'This is too short.';

    $result = $this->service->analyze($shortText);

    expect($result->aiConfidence)->toBe(50.0);
    expect($result->criticalSections)->toBeEmpty();
});

test('it detects GPT-like text patterns', function () {
    $gptText = 'In today\'s digital landscape, it\'s important to note that artificial intelligence is revolutionizing the way we work. Moreover, this paradigm shift will delve into new possibilities. Furthermore, it\'s worth mentioning that the landscape of technology continues to evolve. In conclusion, these developments are transforming our world in unprecedented ways that will shape the future of innovation and progress across multiple industries and sectors.';

    $result = $this->service->analyze($gptText);

    expect($result->likelyModel)->toBe('GPT');
    expect($result->modelConfidence)->toBeGreaterThan(30);
});

test('it detects Claude-like text patterns', function () {
    $claudeText = 'I appreciate your question about this topic. I\'d be happy to help explain this concept in detail. To be clear, there are several important factors to consider when approaching this subject. In this case, it\'s worth noting that the approach may vary depending on your specific needs and circumstances. I understand this can be complex, so feel free to let me know if you need any clarification on these points or would like me to elaborate further.';

    $result = $this->service->analyze($claudeText);

    expect($result->likelyModel)->toBe('Claude');
    expect($result->modelConfidence)->toBeGreaterThan(30);
});

test('it detects Gemini-like text patterns', function () {
    $geminiText = 'Sure, here\'s what you need to know about this topic. Absolutely, this is a great question! Let\'s break this down into key takeaways. In a nutshell, the bottom line is that these concepts are interconnected. Definitely, here\'s what makes this approach effective for achieving your goals. To sum up, the key takeaway is understanding how these elements work together to create meaningful results.';

    $result = $this->service->analyze($geminiText);

    expect($result->likelyModel)->toBe('Gemini');
    expect($result->modelConfidence)->toBeGreaterThan(30);
});

test('it returns null model when confidence is too low', function () {
    $neutralText = 'The quick brown fox jumps over the lazy dog. This is a simple sentence without any AI-specific patterns or phrases. Just regular writing that could come from anyone. Nothing special or distinctive about the style used here at all.';

    $result = $this->service->analyze($neutralText);

    expect($result->likelyModel)->toBeNull();
    expect($result->modelConfidence)->toBeNull();
});
