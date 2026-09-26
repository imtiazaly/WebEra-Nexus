<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreClientRequest;
use App\Http\Requests\UpdateClientRequest;
use App\Models\Client;
use App\Models\Service;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ClientController extends Controller
{
    public function index(): Response
    {
        $clients = Client::with('services:id,name')
            ->latest()
            ->paginate(15);

        $allClientServices = Service::forClients()
            ->withCount('clients')
            ->latest()
            ->get();

        return Inertia::render('Clients/Index', [
            'clients' => $clients,
            'all_client_services' => $allClientServices,
        ]);
    }

    public function create(): Response
    {
        $services = Service::where('is_active', true)->forClients()->get();

        return Inertia::render('Clients/Create', [
            'services' => $services,
        ]);
    }

    public function store(StoreClientRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $client = Client::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'company_name' => $validated['company_name'] ?? null,
            'status' => $validated['status'],
            'notes' => $validated['notes'] ?? null,
        ]);

        if (! empty($validated['services'])) {
            $attachData = [];
            foreach ($validated['services'] as $service) {
                $attachData[$service['id']] = [
                    'requirements' => $service['requirements'] ?? null,
                    'estimated_budget' => $service['estimated_budget'] ?? null,
                ];
            }
            $client->services()->attach($attachData);
        }

        return redirect()->route('clients.index')
            ->with('success', 'Client / Lead created successfully.');
    }

    public function show(Client $client): Response
    {
        $client->load(['services', 'projects']);

        return Inertia::render('Clients/Show', [
            'client' => $client,
        ]);
    }

    public function edit(Client $client): Response
    {
        $client->load('services');
        $services = Service::where('is_active', true)->forClients()->get();

        return Inertia::render('Clients/Edit', [
            'client' => $client,
            'services' => $services,
        ]);
    }

    public function update(UpdateClientRequest $request, Client $client): RedirectResponse
    {
        $validated = $request->validated();

        $client->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'company_name' => $validated['company_name'] ?? null,
            'status' => $validated['status'],
            'notes' => $validated['notes'] ?? null,
        ]);

        if (isset($validated['services'])) {
            $syncData = [];
            foreach ($validated['services'] as $service) {
                $syncData[$service['id']] = [
                    'requirements' => $service['requirements'] ?? null,
                    'estimated_budget' => $service['estimated_budget'] ?? null,
                ];
            }
            $client->services()->sync($syncData);
        }

        return redirect()->route('clients.index')
            ->with('success', 'Client / Lead updated successfully.');
    }

    public function destroy(Client $client): RedirectResponse
    {
        $client->delete();

        return redirect()->route('clients.index')
            ->with('success', 'Client / Lead deleted successfully.');
    }
}
