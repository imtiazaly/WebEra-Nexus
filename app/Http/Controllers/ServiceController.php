<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreServiceRequest;
use App\Http\Requests\UpdateServiceRequest;
use App\Models\Service;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;

class ServiceController extends Controller
{
    public function store(StoreServiceRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $validated['slug'] = Str::slug($validated['name']).'-'.Str::random(4);
        $validated['is_active'] = $validated['is_active'] ?? true;

        Service::create($validated);

        return redirect()->back()
            ->with('success', 'Service / Track added successfully.');
    }

    public function update(UpdateServiceRequest $request, Service $service): RedirectResponse
    {
        $validated = $request->validated();
        if ($service->name !== $validated['name']) {
            $validated['slug'] = Str::slug($validated['name']).'-'.Str::random(4);
        }

        $service->update($validated);

        return redirect()->back()
            ->with('success', 'Service / Track updated successfully.');
    }

    public function destroy(Service $service): RedirectResponse
    {
        // Safe check for relationships
        if ($service->internships()->exists() || $service->clients()->exists()) {
            $service->update(['is_active' => false]);

            return redirect()->back()
                ->with('success', 'Service / Track archived (set to inactive) because it is linked to active records.');
        }

        $service->delete();

        return redirect()->back()
            ->with('success', 'Service / Track deleted successfully.');
    }

    public function toggle(Service $service): RedirectResponse
    {
        $service->update(['is_active' => ! $service->is_active]);

        return redirect()->back()
            ->with('success', 'Service status updated successfully.');
    }
}
