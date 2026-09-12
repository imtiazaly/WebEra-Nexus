<?php

namespace App\Jobs;

use App\Models\WeeklyReport;
use App\Services\Ai\AiServiceInterface;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class GenerateWeeklyReportAiSummaryJob implements ShouldQueue
{
    use Queueable;

    public function __construct(public WeeklyReport $report) {}

    public function handle(AiServiceInterface $aiService): void
    {
        $summary = $aiService->summarizeWeeklyReport($this->report);
        if ($summary) {
            $this->report->update(['ai_summary' => $summary]);
        }
    }
}
