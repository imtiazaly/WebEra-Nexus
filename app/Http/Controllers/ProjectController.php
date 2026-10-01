<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Models\Client;
use App\Models\Project;
use App\Models\Service;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProjectController extends Controller
{
    public function index(Request $request): Response
    {
        $status = $request->query('status', 'all');
        $type = $request->query('type', 'all');
        $search = $request->query('search');

        $query = Project::with(['client:id,name,company_name', 'service:id,name']);

        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }

        if ($type === 'client') {
            $query->whereNotNull('client_id');
        } elseif ($type === 'internal') {
            $query->whereNull('client_id');
        }

        if ($search) {
            $term = trim($search);
            $query->where(function ($q) use ($term) {
                $q->where('title', 'like', "%{$term}%")
                    ->orWhere('description', 'like', "%{$term}%")
                    ->orWhereHas('client', function ($cq) use ($term) {
                        $cq->where('name', 'like', "%{$term}%")
                            ->orWhere('company_name', 'like', "%{$term}%");
                    })
                    ->orWhereHas('service', function ($sq) use ($term) {
                        $sq->where('name', 'like', "%{$term}%");
                    });
            });
        }

        $perPage = (int) $request->query('per_page', 10);
        $projects = $query->latest()
            ->paginate($perPage)
            ->withQueryString();

        $metrics = [
            'total' => Project::count(),
            'in_progress' => Project::where('status', 'in_progress')->count(),
            'planning' => Project::where('status', 'planning')->count(),
            'completed' => Project::where('status', 'completed')->count(),
            'under_review' => Project::where('status', 'under_review')->count(),
            'on_hold' => Project::where('status', 'on_hold')->count(),
            'cancelled' => Project::where('status', 'cancelled')->count(),
            'overdue' => Project::whereNotNull('deadline')
                ->where('deadline', '<', now()->toDateString())
                ->where('status', '!=', 'completed')
                ->count(),
        ];

        return Inertia::render('Projects/Index', [
            'projects' => $projects,
            'filters' => [
                'status' => $status,
                'type' => $type,
                'search' => $search ?? '',
            ],
            'metrics' => $metrics,
        ]);
    }

    public function create(): Response
    {
        $clients = Client::get(['id', 'name', 'company_name']);
        $services = Service::where('is_active', true)->get(['id', 'name']);

        return Inertia::render('Projects/Create', [
            'clients' => $clients,
            'services' => $services,
        ]);
    }

    public function store(StoreProjectRequest $request): RedirectResponse
    {
        Project::create($request->validated());

        return redirect()->route('projects.index')
            ->with('success', 'Project created successfully.');
    }

    public function show(Project $project): Response
    {
        $project->load(['client', 'service']);

        return Inertia::render('Projects/Show', [
            'project' => $project,
        ]);
    }

    public function edit(Project $project): Response
    {
        $clients = Client::get(['id', 'name', 'company_name']);
        $services = Service::where('is_active', true)->get(['id', 'name']);

        return Inertia::render('Projects/Edit', [
            'project' => $project,
            'clients' => $clients,
            'services' => $services,
        ]);
    }

    public function update(UpdateProjectRequest $request, Project $project): RedirectResponse
    {
        $project->update($request->validated());

        return redirect()->route('projects.index')
            ->with('success', 'Project updated successfully.');
    }

    public function destroy(Project $project): RedirectResponse
    {
        $project->delete();

        return redirect()->route('projects.index')
            ->with('success', 'Project deleted successfully.');
    }
}
