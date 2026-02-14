<?php

namespace App\Services;

use App\DataTransferObjects\DetectionResult;

class MathematicalDetectionService
{
    /**
     * AI common phrases to detect.
     */
    protected array $aiPhrases = [
        'delve into',
        'it\'s important to note',
        'at the end of the day',
        'going forward',
        'in conclusion',
        'moreover',
        'furthermore',
        'accordingly',
        'consequently',
        'it\'s worth mentioning',
        'in today\'s digital landscape',
        'in summary',
        'to summarize',
    ];

    /**
     * GPT-specific phrases and patterns.
     */
    protected array $gptFingerprints = [
        'delve into',
        'it\'s important to note',
        'in today\'s digital landscape',
        'it\'s worth mentioning',
        'in conclusion',
        'moreover',
        'furthermore',
        'consequently',
        'the landscape of',
        'revolutionize',
        'paradigm shift',
    ];

    /**
     * Claude-specific phrases and patterns.
     */
    protected array $claudeFingerprints = [
        'i appreciate',
        'i\'d be happy to',
        'let me know if',
        'feel free to',
        'i understand',
        'i\'m happy to help',
        'i should mention',
        'it\'s worth noting',
        'to be clear',
        'in this case',
    ];

    /**
     * Gemini-specific phrases and patterns.
     */
    protected array $geminiFingerprints = [
        'sure, here\'s',
        'absolutely',
        'definitely',
        'great question',
        'here\'s what',
        'let\'s break',
        'in a nutshell',
        'bottom line',
        'key takeaway',
        'to sum up',
    ];

    public function analyze(string $text): DetectionResult
    {
        $words = $this->tokenize($text);
        $totalWords = count($words);

        if ($totalWords < 50) {
            // Should be handled by validation, but safety check
            return new DetectionResult(50, 0, 0, 0, []);
        }

        $perplexity = $this->calculatePerplexity($words);
        $burstiness = $this->calculateBurstiness($text);
        $diversity = $this->calculateDiversity($words);

        // Normalize scores (0-100, where 100 is "Very AI-like")
        // Low perplexity (predictable) -> High AI score
        $normPerplexity = $this->normalizePerplexity($perplexity);
        // Low burstiness (consistent) -> High AI score
        $normBurstiness = $this->normalizeBurstiness($burstiness);
        // Low diversity (repetitive) -> High AI score
        $normDiversity = $this->normalizeDiversity($diversity);

        // Weighted combination from doc: 40% Perplexity, 30% Burstiness, 30% Diversity
        $aiConfidence = (
            ($normPerplexity * 0.4) +
            ($normBurstiness * 0.3) +
            ($normDiversity * 0.3)
        );

        // Add small boost for AI phrases
        $phraseScore = $this->calculatePhraseScore($text);
        $aiConfidence = min(100, $aiConfidence + $phraseScore);

        $criticalSections = $this->findCriticalSections($text);

        // Detect model family
        [$likelyModel, $modelConfidence] = $this->detectModelFamily($text);

        return new DetectionResult(
            aiConfidence: round($aiConfidence, 2),
            perplexityScore: round($perplexity, 2),
            burstinessScore: round($burstiness, 2),
            diversityScore: round($diversity, 2),
            criticalSections: $criticalSections,
            likelyModel: $likelyModel,
            modelConfidence: $modelConfidence ? round($modelConfidence, 2) : null
        );
    }

    protected function detectModelFamily(string $text): array
    {
        $textLower = strtolower($text);

        $gptScore = $this->calculateModelScore($textLower, $this->gptFingerprints);
        $claudeScore = $this->calculateModelScore($textLower, $this->claudeFingerprints);
        $geminiScore = $this->calculateModelScore($textLower, $this->geminiFingerprints);

        $scores = [
            'GPT' => $gptScore,
            'Claude' => $claudeScore,
            'Gemini' => $geminiScore,
        ];

        $maxScore = max($scores);

        // Only return if confidence is high enough (at least 30%)
        if ($maxScore < 30) {
            return [null, null];
        }

        $likelyModel = array_search($maxScore, $scores);

        return [$likelyModel, $maxScore];
    }

    protected function calculateModelScore(string $text, array $fingerprints): float
    {
        $found = 0;
        $totalPhrases = count($fingerprints);

        foreach ($fingerprints as $phrase) {
            if (str_contains($text, $phrase)) {
                $found++;
            }
        }

        // Return percentage of fingerprints found
        return ($found / $totalPhrases) * 100;
    }

    protected function tokenize(string $text): array
    {
        // Simple word tokenization
        return str_word_count(strtolower($text), 1);
    }

    protected function calculatePerplexity(array $words): float
    {
        // Simple bigram frequency analysis to simulate "predictability"
        // In a real implementation with a model, this would be math-heavy.
        // Here we simulate it by checking common bigram probabilities.

        $bigrams = [];
        $totalBigrams = 0;
        for ($i = 0; $i < count($words) - 1; $i++) {
            $bigram = $words[$i].' '.$words[$i + 1];
            $bigrams[$bigram] = ($bigrams[$bigram] ?? 0) + 1;
            $totalBigrams++;
        }

        if ($totalBigrams === 0) {
            return 0;
        }

        $entropy = 0;
        foreach ($bigrams as $count) {
            $p = $count / $totalBigrams;
            $entropy -= $p * log($p, 2);
        }

        // Perplexity = 2^entropy
        return pow(2, $entropy);
    }

    protected function calculateBurstiness(string $text): float
    {
        // Variation in sentence lengths
        $sentences = preg_split('/[.!?]+/', $text, -1, PREG_SPLIT_NO_EMPTY);
        $lengths = array_map(fn ($s) => str_word_count(trim($s)), $sentences);
        $count = count($lengths);

        if ($count <= 1) {
            return 0;
        }

        $mean = array_sum($lengths) / $count;
        $variance = array_reduce($lengths, fn ($carry, $val) => $carry + pow($val - $mean, 2), 0) / $count;
        $stdDev = sqrt($variance);

        // Formula from doc: (σ - μ) / (σ + μ)
        // High variation = High score (Human)
        // Low variation = Low score (AI)
        if ($stdDev + $mean === 0.0) {
            return 0;
        }

        return ($stdDev - $mean) / ($stdDev + $mean);
    }

    protected function calculateDiversity(array $words): float
    {
        // TTR (Type-Token Ratio) = unique_words / total_words
        $total = count($words);
        if ($total === 0) {
            return 0;
        }

        $unique = count(array_unique($words));

        return $unique / $total;
    }

    protected function normalizePerplexity(float $perplexity): float
    {
        // Thresholds from doc (interpreted):
        // Higher perplexity = more human.
        // Let's say 100+ is human (0 AI confidence), 20- is AI (100 AI confidence).
        if ($perplexity >= 100) {
            return 0;
        }
        if ($perplexity <= 20) {
            return 100;
        }

        return 100 - (($perplexity - 20) / (100 - 20) * 100);
    }

    protected function normalizeBurstiness(float $burstiness): float
    {
        // Thresholds from doc:
        // > 0.5: Likely human (0 AI score)
        // < 0.2: Likely AI (100 AI score)
        if ($burstiness >= 0.5) {
            return 0;
        }
        if ($burstiness <= 0.2) {
            return 100;
        }

        return 100 - (($burstiness - 0.2) / (0.5 - 0.2) * 100);
    }

    protected function normalizeDiversity(float $diversity): float
    {
        // Thresholds from doc:
        // TTR > 0.6: Likely human (0 AI score)
        // TTR < 0.4: Likely AI (100 AI score)
        if ($diversity >= 0.6) {
            return 0;
        }
        if ($diversity <= 0.4) {
            return 100;
        }

        return 100 - (($diversity - 0.4) / (0.6 - 0.4) * 100);
    }

    protected function calculatePhraseScore(string $text): float
    {
        $textLower = strtolower($text);
        $found = 0;
        foreach ($this->aiPhrases as $phrase) {
            if (str_contains($textLower, $phrase)) {
                $found++;
            }
        }

        return min(25, $found * 5); // Max 25% boost for AI phrases
    }

    protected function findCriticalSections(string $text): array
    {
        // Split by sentences and analyze each
        $sentences = preg_split('/([.!?]+)/', $text, -1, PREG_SPLIT_DELIM_CAPTURE);
        $sections = [];
        $offset = 0;

        for ($i = 0; $i < count($sentences); $i += 2) {
            $sentenceText = $sentences[$i];
            $delimiter = $sentences[$i + 1] ?? '';
            $fullSentence = $sentenceText.$delimiter;

            $trimmed = trim($sentenceText);
            if (empty($trimmed)) {
                $offset += strlen($fullSentence);

                continue;
            }

            $words = $this->tokenize($trimmed);
            if (count($words) < 5) {
                $offset += strlen($fullSentence);

                continue;
            }

            $sentenceAiScore = $this->analyzeSentence($trimmed);

            if ($sentenceAiScore >= 40) {
                $sections[] = [
                    'start' => $offset,
                    'end' => $offset + strlen($fullSentence),
                    'confidence' => round($sentenceAiScore, 2),
                    'reason' => $this->getSentenceReason($sentenceAiScore, $trimmed),
                    'text' => $fullSentence,
                ];
            }

            $offset += strlen($fullSentence);
        }

        return $sections;
    }

    protected function analyzeSentence(string $sentence): float
    {
        $words = $this->tokenize($sentence);
        $p = $this->calculatePerplexity($words);
        $d = $this->calculateDiversity($words);

        // Simplified scoring for sentences
        $np = $this->normalizePerplexity($p);
        $nd = $this->normalizeDiversity($d);

        $score = ($np * 0.6) + ($nd * 0.4);

        // Boost for AI phrases in sentence
        foreach ($this->aiPhrases as $phrase) {
            if (str_contains(strtolower($sentence), $phrase)) {
                $score += 20;
            }
        }

        return min(100, $score);
    }

    protected function getSentenceReason(float $score, string $sentence): string
    {
        if ($score > 85) {
            return 'Highly repetitive structure and common AI patterns.';
        }
        if ($score > 70) {
            return 'Predictable word choice and low vocabulary variance.';
        }

        return 'Consistent rhythm often found in AI-generated responses.';
    }
}
