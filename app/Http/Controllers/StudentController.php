<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreStudentRequest;
use App\Http\Requests\UpdateStudentRequest;
use App\Models\Internship;
use App\Models\Project;
use App\Models\Student;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class StudentController extends Controller
{
    public function index(): Response
    {
        $students = Student::with('internship:id,name,batch_no')
            ->withCount('weeklyReports')
            ->latest()
            ->paginate(15);

        return Inertia::render('Students/Index', [
            'students' => $students,
        ]);
    }

    public function create(): Response
    {
        $internships = Internship::where('status', '!=', 'completed')
            ->get(['id', 'name', 'batch_no']);

        return Inertia::render('Students/Create', [
            'internships' => $internships,
        ]);
    }

    public function store(StoreStudentRequest $request): RedirectResponse
    {
        Student::create($request->validated());

        return redirect()->route('students.index')
            ->with('success', 'Student record created successfully.');
    }

    public function show(Student $student): Response
    {
        $student->load([
            'internship.service',
            'weeklyReports',
            'projects.client',
            'projects.service',
        ]);

        $availableProjects = Project::get(['id', 'title']);

        return Inertia::render('Students/Show', [
            'student' => $student,
            'availableProjects' => $availableProjects,
        ]);
    }

    public function edit(Student $student): Response
    {
        $internships = Internship::get(['id', 'name', 'batch_no']);

        return Inertia::render('Students/Edit', [
            'student' => $student,
            'internships' => $internships,
        ]);
    }

    public function update(UpdateStudentRequest $request, Student $student): RedirectResponse
    {
        $student->update($request->validated());

        return redirect()->route('students.index')
            ->with('success', 'Student record updated successfully.');
    }

    public function destroy(Student $student): RedirectResponse
    {
        $student->delete();

        return redirect()->route('students.index')
            ->with('success', 'Student record deleted successfully.');
    }

    public function assignProject(Request $request, Student $student): RedirectResponse
    {
        $validated = $request->validate([
            'project_id' => ['required', 'exists:projects,id'],
            'role' => ['required', 'string', 'max:255'],
            'progress' => ['required', 'integer', 'min:0', 'max:100'],
            'submission_status' => ['required', 'string', 'max:50'],
        ]);

        $student->projects()->syncWithoutDetaching([
            $validated['project_id'] => [
                'role' => $validated['role'],
                'progress' => $validated['progress'],
                'submission_status' => $validated['submission_status'],
            ],
        ]);

        return redirect()->route('students.show', $student->id)
            ->with('success', 'Project assigned successfully.');
    }
}
