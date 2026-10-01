<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInternshipRequest;
use App\Http\Requests\UpdateInternshipRequest;
use App\Models\Internship;
use App\Models\Service;
use App\Models\Student;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class InternshipController extends Controller
{
    public function index(Request $request): Response
    {
        $status = $request->query('status', 'all');
        $track = $request->query('track', 'all');
        $search = $request->query('search');

        $query = Internship::with([
            'service:id,name',
            'students:id,internship_id,name,email,phone,status,overall_progress',
        ])
            ->withCount(['students as total_students_count']);

        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }

        if ($track && $track !== 'all') {
            $query->whereHas('service', function ($q) use ($track) {
                $q->where('name', $track)->orWhere('id', $track);
            });
        }

        if ($search) {
            $term = trim($search);
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('batch_no', 'like', "%{$term}%")
                    ->orWhereHas('service', function ($sq) use ($term) {
                        $sq->where('name', 'like', "%{$term}%");
                    });
            });
        }

        $perPage = (int) $request->query('per_page', 9);
        $internships = $query->latest()
            ->paginate($perPage)
            ->withQueryString();

        $allTracks = Service::forInternships()
            ->withCount('internships')
            ->latest()
            ->get();

        $metrics = [
            'total' => Internship::count(),
            'active' => Internship::where('status', 'active')->count(),
            'upcoming' => Internship::where('status', 'upcoming')->count(),
            'completed' => Internship::where('status', 'completed')->count(),
            'total_enrolled' => Student::count(),
            'avg_progress' => (int) round(Student::avg('overall_progress') ?? 0),
            'unique_tracks_count' => Service::forInternships()->whereHas('internships')->count(),
        ];

        return Inertia::render('Internships/Index', [
            'internships' => $internships,
            'all_tracks' => $allTracks,
            'filters' => [
                'status' => $status,
                'track' => $track,
                'search' => $search ?? '',
            ],
            'metrics' => $metrics,
        ]);
    }

    public function create(): Response
    {
        $services = Service::where('is_active', true)->forInternships()->get();

        return Inertia::render('Internships/Create', [
            'services' => $services,
        ]);
    }

    public function store(StoreInternshipRequest $request): RedirectResponse
    {
        Internship::create($request->validated());

        return redirect()->route('internships.index')
            ->with('success', 'Internship batch created successfully.');
    }

    public function show(Internship $internship): Response
    {
        $internship->load([
            'service',
            'students' => function ($query) {
                $query->withCount('weeklyReports');
            },
        ]);

        return Inertia::render('Internships/Show', [
            'internship' => $internship,
        ]);
    }

    public function edit(Internship $internship): Response
    {
        $services = Service::where('is_active', true)->forInternships()->get();

        return Inertia::render('Internships/Edit', [
            'internship' => $internship,
            'services' => $services,
        ]);
    }

    public function update(UpdateInternshipRequest $request, Internship $internship): RedirectResponse
    {
        $internship->update($request->validated());

        return redirect()->route('internships.index')
            ->with('success', 'Internship batch updated successfully.');
    }

    public function destroy(Internship $internship): RedirectResponse
    {
        $internship->delete();

        return redirect()->route('internships.index')
            ->with('success', 'Internship batch deleted successfully.');
    }
}
