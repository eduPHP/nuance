# AI Detection Strategy

## Overview

The Humanizer app uses **mathematical analysis** for instant AI detection, providing immediate feedback to users without external API dependencies.

## Detection Approach

### Mathematical Analysis (Primary Method)

We use a combination of three metrics to detect AI-generated text:

1. **Perplexity Score**
2. **Burstiness Score**
3. **Vocabulary Diversity**

### Why Mathematical Analysis?

- ✅ **Instant results** (< 100ms)
- ✅ **No API costs**
- ✅ **Works offline**
- ✅ **Privacy-friendly** (no data sent externally)
- ✅ **Predictable performance**
- ⚠️ **~75-85% accuracy** (good enough for MVP)

## Detection Metrics

### 1. Perplexity Score

**What it measures**: How predictable the text is.

**Theory**: AI-generated text tends to be more predictable because LLMs choose high-probability words.

**Calculation**:
```
Perplexity = exp(-1/N * Σ log P(word_i | context))
```

**Implementation approach**:
- Use n-gram frequency analysis
- Calculate probability of each word given previous words
- Lower perplexity = more predictable = likely AI

**Thresholds**:
- < 50: Likely human (varied, unpredictable)
- 50-100: Mixed or edited AI
- > 100: Likely AI (very predictable)

### 2. Burstiness Score

**What it measures**: Variation in sentence complexity.

**Theory**: Humans write with varied sentence lengths and complexity. AI tends toward consistent, medium-length sentences.

**Calculation**:
```
Burstiness = (σ - μ) / (σ + μ)

Where:
- σ = standard deviation of sentence lengths
- μ = mean sentence length
```

**Thresholds**:
- > 0.5: Likely human (high variation)
- 0.2-0.5: Mixed
- < 0.2: Likely AI (low variation)

### 3. Vocabulary Diversity

**What it measures**: Unique word usage and repetition patterns.

**Theory**: AI tends to reuse similar vocabulary and phrases. Humans have more diverse word choice.

**Calculation**:
```
TTR (Type-Token Ratio) = unique_words / total_words
```

**Enhanced metrics**:
- Repeated phrase detection
- Transition word frequency
- Adjective/adverb density

**Thresholds**:
- TTR > 0.6: Likely human
- TTR 0.4-0.6: Mixed
- TTR < 0.4: Likely AI

## Word Count Limits

### Minimum Word Count

**All tiers**: 50 words minimum required for analysis

**Reason**: Statistical analysis requires sufficient text to be accurate. Very short texts don't provide enough data for reliable perplexity, burstiness, and diversity calculations.

**Error message**: "Text too short for analysis (minimum 50 words)"

### Maximum Word Count (Free Tier)

**Free tier**: 800 words maximum per analysis  
**Pro tier**: Unlimited

**Reason**: Tier differentiation to encourage Pro upgrades while still providing valuable free tier functionality.

**Implementation**:
- Word count checked before queuing analysis job
- Validation occurs in `TierLimitService::checkAnalysisWordLimit()`
- Uses PHP's `str_word_count()` function
- Pro tier users have `analysis_word_limit = NULL` (unlimited)

**Error message**: "Text exceeds the 800-word limit for free tier analysis. Your text contains {count} words. Upgrade to Pro for unlimited analysis."

## Combined Scoring Algorithm

```php
class MathematicalDetectionService implements DetectionServiceInterface
{
    public function analyze(string $text): DetectionResult
    {
        // Calculate individual scores
        $perplexity = $this->calculatePerplexity($text);
        $burstiness = $this->calculateBurstiness($text);
        $diversity = $this->calculateDiversity($text);
        
        // Weighted combination
        $aiConfidence = (
            ($this->normalizePerplexity($perplexity) * 0.4) +
            ($this->normalizeBurstiness($burstiness) * 0.3) +
            ($this->normalizeDiversity($diversity) * 0.3)
        );
        
        // Identify critical sections
        $criticalSections = $this->findCriticalSections($text, $aiConfidence);
        
        return new DetectionResult(
            aiConfidence: $aiConfidence,
            perplexityScore: $perplexity,
            burstinessScore: $burstiness,
            diversityScore: $diversity,
            criticalSections: $criticalSections
        );
    }
}
```

## Critical Section Detection

Identify specific parts of text that are most likely AI-generated:

**Algorithm**:
1. Split text into paragraphs/sentences
2. Analyze each segment independently
3. Flag segments with:
   - Very low perplexity (< 30)
   - Very low burstiness (< 0.15)
   - Repetitive patterns
   - Common AI phrases ("delve into", "it's important to note")

**Output format**:
```json
[
  {
    "start": 0,
    "end": 150,
    "confidence": 92.5,
    "reason": "Low perplexity and repetitive structure",
    "text": "In today's digital landscape, it's important to note..."
  },
  {
    "start": 300,
    "end": 450,
    "confidence": 88.3,
    "reason": "Consistent sentence length and predictable word choice",
    "text": "Furthermore, the implementation of..."
  }
]
```

## Sample Validation

When users upload writing samples, we validate they're human-written:

**Validation rules**:
- AI confidence must be < 30%
- If > 30%, mark sample as `is_valid = false`
- Show warning to user: "This sample appears to be AI-generated and may contaminate rewriting results"

**User flow**:
```
User uploads sample
    ↓
Analyze sample (same detection algorithm)
    ↓
AI confidence > 30%?
    ↓ Yes
Mark as invalid, show warning
    ↓ No
Mark as valid, allow use for rewriting
```

## Performance Optimization

### Caching
- Cache analysis results for identical text (hash-based)
- Cache n-gram frequencies for common words
- TTL: 24 hours

### Async Processing
- For long documents (> 5000 words), queue analysis
- Show "Analyzing..." state in UI
- Broadcast result via WebSocket when complete

### Database Indexing
- Index `detection_results.ai_confidence` for filtering
- Index `samples.is_valid` for quick valid sample lookup

## Future Enhancements

### Phase 2: ML Model Integration
- Add lightweight BERT/RoBERTa classifier
- Run mathematical analysis first (instant)
- Queue ML analysis in background
- Update confidence score when ML completes

### Phase 3: Pattern Library
- Build database of known AI patterns
- Detect GPT-specific phrases
- Identify model-specific writing styles

## Testing Strategy

### Unit Tests
```php
test('calculates perplexity correctly', function () {
    $text = "The quick brown fox jumps over the lazy dog.";
    $score = $this->analyzer->calculatePerplexity($text);
    expect($score)->toBeFloat()->toBeGreaterThan(0);
});

test('detects high AI confidence for predictable text', function () {
    $aiText = "In conclusion, it is important to note that...";
    $result = $this->service->analyze($aiText);
    expect($result->aiConfidence)->toBeGreaterThan(70);
});
```

### Integration Tests
```php
test('marks sample as invalid if AI confidence is high', function () {
    $sample = Sample::factory()->create([
        'content' => 'Very predictable AI-generated text...'
    ]);
    
    $this->sampleService->validate($sample);
    
    expect($sample->fresh()->is_valid)->toBeFalse();
});
```

## API Response Format

```json
{
  "ai_confidence": 85.3,
  "perplexity_score": 125.7,
  "burstiness_score": 0.18,
  "diversity_score": 0.35,
  "interpretation": "High likelihood of AI generation",
  "critical_sections": [
    {
      "start": 0,
      "end": 150,
      "confidence": 92.5,
      "reason": "Low perplexity and repetitive structure"
    }
  ],
  "metadata": {
    "word_count": 450,
    "sentence_count": 18,
    "avg_sentence_length": 25,
    "analysis_time_ms": 45
  }
}
```

## Error Handling

- **Empty text**: Return error "Text too short for analysis (minimum 50 words)"
- **Text too short**: Return error if word count < 50 words
- **Text too long (free tier)**: Return error if word count > 800 words with upgrade prompt
- **Very long text**: Queue for async processing
- **Invalid characters**: Sanitize before analysis
- **Analysis failure**: Return default confidence of 50% with error flag
