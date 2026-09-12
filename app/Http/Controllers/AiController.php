<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Project;
use App\Models\WeeklyReport;
use App\Services\Ai\AiServiceInterface;
use Illuminate\Http\RedirectResponse;

class AiController extends Controller
{
    public function generateClientBrief(Client $client, AiServiceInterface $aiService): RedirectResponse
    {
        $brief = $aiService->generateLeadBrief($client);

        if ($brief) {
            $client->update(['ai_brief' => $brief]);

            return back()->with('success', 'AI Lead Brief generated successfully.');
        }

        return back()->with('error', 'Unable to generate AI brief. Please check AI settings or API key.');
    }

    public function generateReportSummary(WeeklyReport $report, AiServiceInterface $aiService): RedirectResponse
    {
        $summary = $aiService->summarizeWeeklyReport($report);

        if ($summary) {
            $report->update(['ai_summary' => $summary]);

            return back()->with('success', 'AI Report Summary generated successfully.');
        }

        return back()->with('error', 'Unable to generate AI summary. Please check AI settings.');
    }

    public function generateProjectSummary(Project $project, AiServiceInterface $aiService): RedirectResponse
    {
        $summary = $aiService->summarizeProjectStatus($project);

        if ($summary) {
            $project->update(['ai_status_summary' => $summary]);

            return back()->with('success', 'AI Project Summary generated successfully.');
        }

        return back()->with('error', 'Unable to generate AI summary. Please check AI settings.');
    }
}
