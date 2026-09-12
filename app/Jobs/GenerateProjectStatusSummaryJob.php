<?php

namespace App\Jobs;

use App\Models\Project;
use App\Services\Ai\AiServiceInterface;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class GenerateProjectStatusSummaryJob implements ShouldQueue
{
    use Queueable;

    public function __construct(public Project $project) {}

    public function handle(AiServiceInterface $aiService): void
    {
        $summary = $aiService->summarizeProjectStatus($this->project);
        if ($summary) {
            $this->project->update(['ai_status_summary' => $summary]);
        }
    }
}
