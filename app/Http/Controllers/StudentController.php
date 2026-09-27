<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreStudentRequest;
use App\Http\Requests\UpdateStudentRequest;
use App\Models\Internship;
use App\Models\Project;
use App\Models\Student;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class StudentController extends Controller
{
    public function index(): Response
    {
        $students = Student::with(['internship:id,name,batch_no', 'projects:id,title'])
            ->withCount('weeklyReports')
            ->latest()
            ->paginate(15);

        $stats = [
            'total_students' => Student::count(),
            'active_students' => Student::whereIn('status', ['active', 'enrolled'])->count(),
            'completed_students' => Student::where('status', 'completed')->count(),
            'avg_progress' => (int) round((float) (Student::avg('overall_progress') ?? 0)),
        ];

        return Inertia::render('Students/Index', [
            'students' => $students,
            'stats' => $stats,
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
        $validated = $request->validated();

        $student = Student::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'status' => $validated['status'],
            'overall_progress' => $validated['overall_progress'],
            'internship_id' => $validated['internship_id'] ?? null,
        ]);

        $selectedIds = $validated['selected_internships'] ?? [];
        if (! empty($validated['internship_id']) && ! in_array((int) $validated['internship_id'], array_map('intval', $selectedIds))) {
            $selectedIds[] = (int) $validated['internship_id'];
        }

        foreach ($selectedIds as $batchId) {
            $student->internships()->attach($batchId, [
                'status' => $batchId == ($validated['internship_id'] ?? null) ? $validated['status'] : 'enrolled',
                'progress' => $batchId == ($validated['internship_id'] ?? null) ? $validated['overall_progress'] : 0,
                'joined_at' => now(),
            ]);
        }

        return redirect()->route('students.index')
            ->with('success', 'Student record created and enrolled in batch(es) successfully.');
    }

    public function show(Student $student): Response
    {
        $student->load([
            'internship.service',
            'internships.service', // All batches history
            'weeklyReports',
            'projects.client',
        ]);

        $availableProjects = Project::get(['id', 'title']);
        $availableBatches = Internship::where('status', '!=', 'completed')->get(['id', 'name', 'batch_no']);

        return Inertia::render('Students/Show', [
            'student' => $student,
            'availableProjects' => $availableProjects,
            'availableBatches' => $availableBatches,
        ]);
    }

    public function enrollBatch(Request $request, Student $student): RedirectResponse
    {
        $validated = $request->validate([
            'internship_id' => ['required', 'exists:internships,id'],
            'status' => ['required', 'string', 'max:50'],
            'progress' => ['required', 'integer', 'min:0', 'max:100'],
        ]);

        $student->internships()->syncWithoutDetaching([
            $validated['internship_id'] => [
                'status' => $validated['status'],
                'progress' => $validated['progress'],
                'joined_at' => now(),
            ],
        ]);

        // Update primary active internship pointer if needed
        $student->update([
            'internship_id' => $validated['internship_id'],
            'status' => $validated['status'],
        ]);

        return redirect()->route('students.show', $student->id)
            ->with('success', 'Student enrolled into new training batch successfully.');
    }

    public function updateBatchEnrollment(Request $request, Student $student, Internship $internship): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'string', 'max:50'],
            'progress' => ['required', 'integer', 'min:0', 'max:100'],
            'grade' => ['nullable', 'string', 'max:20'],
            'certificate_code' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string'],
        ]);

        $student->internships()->updateExistingPivot($internship->id, [
            'status' => $validated['status'],
            'progress' => $validated['progress'],
            'grade' => $validated['grade'] ?? null,
            'certificate_code' => $validated['certificate_code'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'completed_at' => $validated['status'] === 'completed' ? now() : null,
        ]);

        // If this is currently the active internship pointer, keep status in sync
        if ($student->internship_id === $internship->id) {
            $student->update([
                'status' => $validated['status'],
                'overall_progress' => $validated['progress'],
            ]);
        }

        return redirect()->route('students.show', $student->id)
            ->with('success', 'Batch enrollment progress and evaluation updated.');
    }

    public function setActiveBatch(Student $student, Internship $internship): RedirectResponse
    {
        $student->update([
            'internship_id' => $internship->id,
        ]);

        return redirect()->route('students.show', $student->id)
            ->with('success', "'{$internship->name}' is now set as the primary active batch.");
    }

    public function graduateBatch(Request $request, Student $student, Internship $internship): RedirectResponse
    {
        $grade = $request->input('grade', 'Distinction');
        $certCode = 'CERT-'.strtoupper(Str::random(8));

        $student->internships()->updateExistingPivot($internship->id, [
            'status' => 'completed',
            'progress' => 100,
            'grade' => $grade,
            'certificate_code' => $certCode,
            'completed_at' => now(),
        ]);

        if ($student->internship_id === $internship->id) {
            $student->update([
                'status' => 'completed',
                'overall_progress' => 100,
            ]);
        }

        return redirect()->route('students.show', $student->id)
            ->with('success', "Student graduated with certificate code {$certCode}!");
    }

    public function edit(Student $student): Response
    {
        $student->load('internships:id,name,batch_no');
        $internships = Internship::get(['id', 'name', 'batch_no']);

        return Inertia::render('Students/Edit', [
            'student' => $student,
            'internships' => $internships,
        ]);
    }

    public function update(UpdateStudentRequest $request, Student $student): RedirectResponse
    {
        $validated = $request->validated();

        $student->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'status' => $validated['status'],
            'overall_progress' => $validated['overall_progress'],
            'internship_id' => $validated['internship_id'] ?? null,
        ]);

        if (isset($validated['selected_internships'])) {
            $selectedIds = array_map('intval', $validated['selected_internships']);
            if (! empty($validated['internship_id']) && ! in_array((int) $validated['internship_id'], $selectedIds)) {
                $selectedIds[] = (int) $validated['internship_id'];
            }

            $existingPivotIds = $student->internships()->pluck('internships.id')->toArray();

            $syncData = [];
            foreach ($selectedIds as $id) {
                if (in_array($id, $existingPivotIds)) {
                    // Keep existing pivot parameters without overwriting progress/status/certs unless primary active
                    if ($id == ($validated['internship_id'] ?? null)) {
                        $syncData[$id] = [
                            'status' => $validated['status'],
                            'progress' => $validated['overall_progress'],
                        ];
                    } else {
                        $syncData[$id] = [];
                    }
                } else {
                    $syncData[$id] = [
                        'status' => $id == ($validated['internship_id'] ?? null) ? $validated['status'] : 'enrolled',
                        'progress' => $id == ($validated['internship_id'] ?? null) ? $validated['overall_progress'] : 0,
                        'joined_at' => now(),
                    ];
                }
            }

            $student->internships()->sync($syncData);
        }

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
