<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInternshipRequest;
use App\Http\Requests\UpdateInternshipRequest;
use App\Models\Internship;
use App\Models\Service;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;

class InternshipController extends Controller
{
    public function index(): Response
    {
        $internships = Internship::with('service:id,name')
            ->withCount(['students as total_students_count'])
            ->latest()
            ->paginate(15);

        return Inertia::render('Internships/Index', [
            'internships' => $internships,
        ]);
    }

    public function create(): Response
    {
        $services = Service::where('is_active', true)->get(['id', 'name']);

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
        $internship->load(['service', 'students']);

        return Inertia::render('Internships/Show', [
            'internship' => $internship,
        ]);
    }

    public function edit(Internship $internship): Response
    {
        $services = Service::where('is_active', true)->get(['id', 'name']);

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
