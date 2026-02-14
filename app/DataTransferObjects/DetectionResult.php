<?php

namespace App\DataTransferObjects;

use Livewire\Wireable;

readonly class DetectionResult implements Wireable
{
    /**
     * @param  array<int, array{start: int, end: int, confidence: float, reason: string, text: string}>  $criticalSections
     */
    public function __construct(
        public float $aiConfidence,
        public float $perplexityScore,
        public float $burstinessScore,
        public float $diversityScore,
        public array $criticalSections = [],
        public ?string $likelyModel = null,
        public ?float $modelConfidence = null,
    ) {}

    public function isLikelyAi(): bool
    {
        return $this->aiConfidence >= 70;
    }

    public function isMixed(): bool
    {
        return $this->aiConfidence > 30 && $this->aiConfidence < 70;
    }

    public function isLikelyHuman(): bool
    {
        return $this->aiConfidence <= 30;
    }

    public function getLabel(): string
    {
        if ($this->isLikelyAi()) {
            return 'Likely AI-Generated';
        }

        if ($this->isMixed()) {
            return 'Mixed or Edited AI';
        }

        return 'Likely Human-Written';
    }

    public function toLivewire()
    {
        return [
            'aiConfidence' => $this->aiConfidence,
            'perplexityScore' => $this->perplexityScore,
            'burstinessScore' => $this->burstinessScore,
            'diversityScore' => $this->diversityScore,
            'criticalSections' => $this->criticalSections,
            'likelyModel' => $this->likelyModel,
            'modelConfidence' => $this->modelConfidence,
        ];
    }

    public static function fromLivewire($value)
    {
        return new self(
            aiConfidence: $value['aiConfidence'],
            perplexityScore: $value['perplexityScore'],
            burstinessScore: $value['burstinessScore'],
            diversityScore: $value['diversityScore'],
            criticalSections: $value['criticalSections'],
            likelyModel: $value['likelyModel'] ?? null,
            modelConfidence: $value['modelConfidence'] ?? null
        );
    }
}
