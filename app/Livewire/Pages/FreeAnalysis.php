<?php

namespace App\Livewire\Pages;

use App\DataTransferObjects\DetectionResult;
use App\Services\MathematicalDetectionService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.marketing')]
class FreeAnalysis extends Component
{
    public string $text = '';

    public string $samples = '';

    public ?DetectionResult $result = null;

    public bool $isAnalyzing = false;

    public function analyze(MathematicalDetectionService $service): void
    {
        $this->validate([
            'text' => ['required', 'string', 'min:50'],
        ], [
            'text.min' => 'Text too short for analysis (minimum 50 words)',
        ]);

        $wordCount = str_word_count($this->text);

        if ($wordCount > 800) {
            $this->addError('text', "Text exceeds the 800-word limit for free tier analysis. Your text contains {$wordCount} words. Upgrade to Pro for unlimited analysis.");

            return;
        }

        $this->isAnalyzing = true;

        // In a real app we might simulate a delay or use a job
        $this->result = $service->analyze($this->text);

        $this->isAnalyzing = false;
    }

    public function getWordCountProperty(): int
    {
        return str_word_count($this->text);
    }

    public function clear(): void
    {
        $this->text = '';
        $this->result = null;
    }

    public function render(): View
    {
        return view('livewire.pages.free-analysis');
    }
}
