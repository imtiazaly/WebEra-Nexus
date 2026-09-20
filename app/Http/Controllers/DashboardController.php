<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Internship;
use App\Models\Project;
use App\Models\Student;
use App\Models\WeeklyReport;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(): Response
    {
        // 1. Leads & Clients Metrics
        $totalLeadsCount = Client::whereIn('status', ['new_lead', 'contacted'])->count();
        $convertedClientsCount = Client::where('status', 'converted')->count();
        $totalClientsCount = Client::count();
        $conversionRate = $totalClientsCount > 0 ? round(($convertedClientsCount / $totalClientsCount) * 100, 1) : 0;

        // 2. Projects Metrics
        $activeProjectsCount = Project::whereIn('status', ['planning', 'in_progress', 'under_review'])->count();
        $clientProjectsCount = Project::whereNotNull('client_id')->count();
        $internalTasksCount = Project::whereNull('client_id')->count();

        $upcomingDeadlines = Project::with(['client:id,name', 'service:id,name'])
            ->whereIn('status', ['planning', 'in_progress', 'under_review'])
            ->whereNotNull('deadline')
            ->whereBetween('deadline', [Carbon::today(), Carbon::today()->addDays(7)])
            ->orderBy('deadline')
            ->get();

        $overdueProjects = Project::with(['client:id,name', 'service:id,name'])
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->whereNotNull('deadline')
            ->where('deadline', '<', Carbon::today())
            ->orderBy('deadline')
            ->get();

        // 3. Internships & Students Metrics
        $activeBatchesCount = Internship::where('status', 'active')->count();
        $totalStudentsCount = Student::count();
        $activeStudentsCount = Student::where('status', 'active')->count();
        $completedStudentsCount = Student::where('status', 'completed')->count();

        $pendingWeeklyReportsCount = WeeklyReport::whereIn('status', ['submitted', 'under_review'])->count();

        // 4. Actionable Alerts
        $uncontactedLeads = Client::where('status', 'new_lead')
            ->where('created_at', '<=', Carbon::now()->subHours(48))
            ->latest()
            ->take(5)
            ->get(['id', 'name', 'email', 'created_at']);

        $pendingReports = WeeklyReport::with('student:id,name')
            ->whereIn('status', ['submitted', 'under_review'])
            ->latest()
            ->take(5)
            ->get();

        return Inertia::render('Dashboard', [
            'metrics' => [
                'total_leads' => $totalLeadsCount,
                'active_clients' => $convertedClientsCount,
                'conversion_rate' => $conversionRate,
                'active_projects' => $activeProjectsCount,
                'client_projects' => $clientProjectsCount,
                'internal_tasks' => $internalTasksCount,
                'active_batches' => $activeBatchesCount,
                'total_students' => $totalStudentsCount,
                'active_students' => $activeStudentsCount,
                'completed_students' => $completedStudentsCount,
                'pending_reports' => $pendingWeeklyReportsCount,
            ],
            'upcomingDeadlines' => $upcomingDeadlines,
            'overdueProjects' => $overdueProjects,
            'uncontactedLeads' => $uncontactedLeads,
            'pendingReports' => $pendingReports,
        ]);
    }
}
