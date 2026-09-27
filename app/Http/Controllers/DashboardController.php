<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Internship;
use App\Models\Project;
use App\Models\Service;
use App\Models\Student;
use App\Models\WeeklyReport;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Handle the incoming admin dashboard request.
     */
    public function __invoke(): Response
    {
        // 1. Financial & Commercial Pipeline
        $totalPipelineValue = (float) DB::table('client_services')->sum('estimated_budget');
        $convertedRevenue = (float) DB::table('client_services')
            ->join('clients', 'client_services.client_id', '=', 'clients.id')
            ->where('clients.status', 'converted')
            ->whereNull('clients.deleted_at')
            ->sum('client_services.estimated_budget');

        $activeDealsCount = DB::table('client_services')
            ->where('estimated_budget', '>', 0)
            ->count();

        // 2. Leads & Clients Conversion Engine
        $newLeadsCount = Client::where('status', 'new_lead')->count();
        $contactedCount = Client::where('status', 'contacted')->count();
        $convertedClientsCount = Client::where('status', 'converted')->count();
        $totalLeadsCount = $newLeadsCount + $contactedCount;
        $totalClientsCount = Client::count();
        $conversionRate = $totalClientsCount > 0 ? round(($convertedClientsCount / $totalClientsCount) * 100, 1) : 0.0;

        // 3. Projects Operational Delivery
        $activeProjectsCount = Project::whereIn('status', ['planning', 'in_progress', 'under_review'])->count();
        $completedProjectsCount = Project::where('status', 'completed')->count();
        $clientProjectsCount = Project::whereNotNull('client_id')->count();
        $internalTasksCount = Project::whereNull('client_id')->count();
        $avgProjectProgress = round((float) (Project::whereIn('status', ['planning', 'in_progress', 'under_review'])->avg('progress') ?? 0), 1);

        $projectsByStatus = [
            'planning' => Project::where('status', 'planning')->count(),
            'in_progress' => Project::where('status', 'in_progress')->count(),
            'under_review' => Project::where('status', 'under_review')->count(),
            'completed' => Project::where('status', 'completed')->count(),
            'on_hold' => Project::where('status', 'on_hold')->count(),
            'cancelled' => Project::where('status', 'cancelled')->count(),
        ];

        $today = Carbon::today();

        $upcomingDeadlines = Project::with(['client:id,name,company_name', 'service:id,name', 'students:id,name'])
            ->whereIn('status', ['planning', 'in_progress', 'under_review'])
            ->whereNotNull('deadline')
            ->whereBetween('deadline', [$today, Carbon::today()->addDays(14)])
            ->orderBy('deadline')
            ->take(6)
            ->get();

        $overdueProjects = Project::with(['client:id,name,company_name', 'service:id,name', 'students:id,name'])
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->whereNotNull('deadline')
            ->where('deadline', '<', $today)
            ->orderBy('deadline')
            ->take(6)
            ->get();

        $recentProjects = Project::with(['client:id,name,company_name', 'service:id,name', 'students:id,name'])
            ->latest()
            ->take(6)
            ->get();

        // 4. Internship Batches & Academy Engine
        $activeBatchesCount = Internship::where('status', 'active')->count();
        $totalBatchesCount = Internship::count();
        $completedBatchesCount = Internship::where('status', 'completed')->count();

        $totalStudentsCount = Student::count();
        $activeStudentsCount = Student::where('status', 'active')->count();
        $completedStudentsCount = Student::where('status', 'completed')->count();
        $enrolledStudentsCount = Student::where('status', 'enrolled')->count();

        $graduationRate = $totalStudentsCount > 0 ? round(($completedStudentsCount / $totalStudentsCount) * 100, 1) : 0.0;
        $avgStudentProgress = round((float) (Student::where('status', 'active')->avg('overall_progress') ?? 0), 1);

        $activeBatches = Internship::with('service:id,name')
            ->withCount('students')
            ->where('status', 'active')
            ->orderBy('end_date')
            ->get()
            ->map(function ($batch) use ($today) {
                $start = Carbon::parse($batch->start_date);
                $end = Carbon::parse($batch->end_date);
                $totalDays = max(1, $start->diffInDays($end));
                $daysElapsed = $start->greaterThan($today) ? 0 : min($totalDays, $start->diffInDays($today));
                $percent = min(100, (int) round(($daysElapsed / $totalDays) * 100));
                $daysRemaining = max(0, (int) $today->diffInDays($end, false));

                return [
                    'id' => $batch->id,
                    'name' => $batch->name,
                    'batch_no' => $batch->batch_no,
                    'service' => $batch->service?->name ?? 'General Track',
                    'start_date' => $batch->start_date ? Carbon::parse($batch->start_date)->format('M d, Y') : null,
                    'end_date' => $batch->end_date ? Carbon::parse($batch->end_date)->format('M d, Y') : null,
                    'students_count' => $batch->students_count,
                    'progress_percent' => $percent,
                    'days_remaining' => $daysRemaining,
                ];
            });

        $topStudents = Student::with(['internship:id,name,batch_no', 'projects:id,title'])
            ->withCount(['weeklyReports', 'projects'])
            ->whereIn('status', ['active', 'completed'])
            ->orderByDesc('overall_progress')
            ->take(5)
            ->get();

        // 5. Quality Assurance & Mentorship Pulse
        $pendingWeeklyReportsCount = WeeklyReport::whereIn('status', ['submitted', 'under_review'])->count();
        $totalWeeklyReportsCount = WeeklyReport::count();
        $approvedWeeklyReportsCount = WeeklyReport::where('status', 'approved')->count();

        $pendingReports = WeeklyReport::with('student:id,name,email,internship_id')
            ->whereIn('status', ['submitted', 'under_review'])
            ->latest('submitted_at')
            ->take(6)
            ->get();

        $blockerReports = WeeklyReport::with('student:id,name,email')
            ->whereNotNull('blockers')
            ->where('blockers', '!=', '')
            ->whereIn('status', ['submitted', 'under_review'])
            ->latest()
            ->take(5)
            ->get(['id', 'student_id', 'week_number', 'blockers', 'created_at']);

        // 6. Actionable Alerts (Uncontacted Leads)
        $uncontactedLeads = Client::where('status', 'new_lead')
            ->where('created_at', '<=', Carbon::now()->subHours(24))
            ->latest()
            ->take(5)
            ->get(['id', 'name', 'email', 'phone', 'company_name', 'created_at']);

        // 7. Services Demand Distribution
        $servicesBreakdown = Service::where('is_active', true)
            ->withCount(['projects', 'clients', 'internships'])
            ->get(['id', 'name', 'type'])
            ->map(function ($service) {
                return [
                    'id' => $service->id,
                    'name' => $service->name,
                    'type' => $service->type,
                    'projects_count' => $service->projects_count,
                    'clients_count' => $service->clients_count,
                    'internships_count' => $service->internships_count,
                ];
            });

        // 8. Unified System Activity Timeline
        $recentLeads = Client::latest()->take(4)->get(['id', 'name', 'company_name', 'status', 'created_at'])
            ->map(fn ($c) => [
                'type' => 'client',
                'title' => $c->name,
                'subtitle' => $c->company_name ? "Company: {$c->company_name}" : 'New Client Registration',
                'status' => $c->status,
                'timestamp' => $c->created_at?->diffForHumans() ?? 'Recently',
                'date' => $c->created_at,
                'link' => route('clients.show', $c->id),
            ]);

        $recentProjUpdates = Project::latest()->take(4)->get(['id', 'title', 'status', 'progress', 'created_at'])
            ->map(fn ($p) => [
                'type' => 'project',
                'title' => $p->title,
                'subtitle' => "Progress: {$p->progress}% • Status: ".ucfirst(str_replace('_', ' ', $p->status)),
                'status' => $p->status,
                'timestamp' => $p->created_at?->diffForHumans() ?? 'Recently',
                'date' => $p->created_at,
                'link' => route('projects.show', $p->id),
            ]);

        $recentReportSubmissions = WeeklyReport::with('student:id,name')->latest('submitted_at')->take(4)->get()
            ->map(fn ($r) => [
                'type' => 'report',
                'title' => "Weekly Report (Week {$r->week_number})",
                'subtitle' => $r->student ? "Intern: {$r->student->name}" : 'Student Report',
                'status' => $r->status,
                'timestamp' => $r->submitted_at?->diffForHumans() ?? 'Recently',
                'date' => $r->submitted_at ?? $r->created_at,
                'link' => $r->student ? route('students.show', $r->student->id) : route('students.index'),
            ]);

        $activityStream = $recentLeads->concat($recentProjUpdates)->concat($recentReportSubmissions)
            ->sortByDesc('date')
            ->values()
            ->take(8);

        return Inertia::render('Dashboard', [
            'metrics' => [
                // Financials & Pipeline
                'total_pipeline_value' => $totalPipelineValue,
                'converted_revenue' => $convertedRevenue,
                'active_deals' => $activeDealsCount,

                // Clients & Leads
                'total_leads' => $totalLeadsCount,
                'new_leads' => $newLeadsCount,
                'contacted_leads' => $contactedCount,
                'active_clients' => $convertedClientsCount,
                'total_clients' => $totalClientsCount,
                'conversion_rate' => $conversionRate,

                // Projects
                'active_projects' => $activeProjectsCount,
                'completed_projects' => $completedProjectsCount,
                'client_projects' => $clientProjectsCount,
                'internal_tasks' => $internalTasksCount,
                'avg_project_progress' => $avgProjectProgress,

                // Academy
                'active_batches' => $activeBatchesCount,
                'total_batches' => $totalBatchesCount,
                'completed_batches' => $completedBatchesCount,
                'total_students' => $totalStudentsCount,
                'active_students' => $activeStudentsCount,
                'completed_students' => $completedStudentsCount,
                'enrolled_students' => $enrolledStudentsCount,
                'graduation_rate' => $graduationRate,
                'avg_student_progress' => $avgStudentProgress,

                // Quality & Mentorship
                'pending_reports' => $pendingWeeklyReportsCount,
                'total_reports' => $totalWeeklyReportsCount,
                'approved_reports' => $approvedWeeklyReportsCount,
                'blockers_count' => $blockerReports->count(),
            ],
            'projectsByStatus' => $projectsByStatus,
            'upcomingDeadlines' => $upcomingDeadlines,
            'overdueProjects' => $overdueProjects,
            'recentProjects' => $recentProjects,
            'activeBatches' => $activeBatches,
            'topStudents' => $topStudents,
            'uncontactedLeads' => $uncontactedLeads,
            'pendingReports' => $pendingReports,
            'blockerReports' => $blockerReports,
            'servicesBreakdown' => $servicesBreakdown,
            'activityStream' => $activityStream,
        ]);
    }
}
