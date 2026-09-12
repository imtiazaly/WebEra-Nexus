<?php

namespace App\Services\Ai;

use App\Models\Client;
use App\Models\Project;
use App\Models\WeeklyReport;

interface AiServiceInterface
{
    /**
     * Generate AI Executive Brief for a Client/Lead.
     */
    public function generateLeadBrief(Client $client): ?string;

    /**
     * Generate AI Summary for a Student's Weekly Report.
     */
    public function summarizeWeeklyReport(WeeklyReport $report): ?string;

    /**
     * Generate AI Status Summary for a Project.
     */
    public function summarizeProjectStatus(Project $project): ?string;
}
