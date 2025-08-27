<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Client;

class ClientController extends Controller
{
     public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $query = Client::query();

        // Filter based on user role
        if (auth()->user()->isStaff()) {
            // Staff can only see clients they registered
            $query->whereHas('registrations', function($q) {
                $q->where('registered_by', auth()->id());
            });
        } elseif (auth()->user()->isAdmin()) {
            // Admin can see all clients in their department
            $query->whereHas('registrations.service.program', function($q) {
                $q->where('department_id', auth()->user()->department_id);
            });
        }
        // Super admin can see all clients (no additional filtering)

        $clients = $query->with('registrations.service.program.department')->paginate(15);

        return view('clients.index', compact('clients'));
    }

    public function show(Client $client)
    {
        // Authorization logic here
        $this->authorizeClientAccess($client);

        $client->load(['registrations.service.program.department', 'registrations.registeredBy']);

        return view('clients.show', compact('client'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'email' => 'nullable|email|unique:clients',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other'
        ]);

        $validated['client_id'] = Client::generateClientId();

        $client = Client::create($validated);

        return redirect()->route('clients.index')
                        ->with('success', 'Client created successfully');
    }

    public function update(Request $request, Client $client)
    {
        $this->authorizeClientAccess($client);

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'email' => 'nullable|email|unique:clients,email,' . $client->id,
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other'
        ]);

        $client->update($validated);

        return redirect()->route('clients.show', $client)
                        ->with('success', 'Client updated successfully');
    }

    private function authorizeClientAccess(Client $client)
    {
        $user = auth()->user();

        if ($user->isSuperAdmin()) {
            return; // Super admin can access all clients
        }

        if ($user->isAdmin()) {
            // Admin can access clients in their department
            $hasAccess = $client->registrations()
                               ->whereHas('service.program', function($q) use ($user) {
                                   $q->where('department_id', $user->department_id);
                               })
                               ->exists();

            if (!$hasAccess) {
                abort(403, 'Unauthorized access to client');
            }
        } elseif ($user->isStaff()) {
            // Staff can only access clients they registered
            $hasAccess = $client->registrations()
                               ->where('registered_by', $user->id)
                               ->exists();

            if (!$hasAccess) {
                abort(403, 'Unauthorized access to client');
            }
        }
    }
}
