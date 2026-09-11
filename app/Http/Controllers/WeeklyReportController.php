<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreWeeklyReportRequest;
use App\Http\Requests\UpdateWeeklyReportRequest;
use App\Models\Student;
use App\Models\WeeklyReport;
use Illuminate\Http\RedirectResponse;

class WeeklyReportController extends Controller
{
    public function store(StoreWeeklyReportRequest $request, Student $student): RedirectResponse
    {
        $validated = $request->validated();
        $validated['submitted_at'] = now();

        $student->weeklyReports()->create($validated);

        return redirect()->route('students.show', $student->id)
            ->with('success', 'Weekly report submitted successfully.');
    }

    public function update(UpdateWeeklyReportRequest $request, WeeklyReport $weeklyReport): RedirectResponse
    {
        $weeklyReport->update($request->validated());

        return redirect()->route('students.show', $weeklyReport->student_id)
            ->with('success', 'Weekly report feedback updated.');
    }
}
