<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Client;
use App\Models\Service;
use App\Models\ClientRegistration;

class StaffController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('staff');
    }

    public function dashboard()
    {
        $stats = [
            'my_registrations' => ClientRegistration::where('registered_by', auth()->id())->count(),
            'pending_services' => ClientRegistration::where('registered_by', auth()->id())
                                                  ->where('status', 'pending')
                                                  ->count(),
            'completed_today' => ClientRegistration::where('registered_by', auth()->id())
                                                 ->where('status', 'completed')
                                                 ->whereDate('updated_at', today())
                                                 ->count(),
        ];

        return view('staff.dashboard', compact('stats'));
    }

    // Client Management
    public function clients()
    {
        $clients = Client::with(['registrations' => function($query) {
                            $query->where('registered_by', auth()->id());
                        }])->paginate(15);

        return view('staff.clients.index', compact('clients'));
    }

    public function createClient(Request $request)
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

        return redirect()->route('staff.clients')
                        ->with('success', 'Client created successfully');
    }

    // Service Registration
    public function registrations()
    {
        $registrations = ClientRegistration::where('registered_by', auth()->id())
                                         ->with(['client', 'service.program'])
                                         ->orderBy('created_at', 'desc')
                                         ->paginate(15);

        return view('staff.registrations.index', compact('registrations'));
    }

    public function registerClient(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'service_id' => 'required|exists:services,id',
            'registration_date' => 'required|date',
            'service_date' => 'nullable|date',
            'notes' => 'nullable|string'
        ]);

        // Verify service belongs to staff's department
        $service = Service::whereHas('program', function($query) {
                             $query->where('department_id', auth()->user()->department_id);
                         })->findOrFail($validated['service_id']);

        $validated['registered_by'] = auth()->id();

        ClientRegistration::create($validated);

        return redirect()->route('staff.registrations')
                        ->with('success', 'Client registered for service successfully');
    }

    public function updateRegistrationStatus(ClientRegistration $registration, Request $request)
    {
        // Verify registration belongs to staff
        if ($registration->registered_by !== auth()->id()) {
            abort(403, 'Unauthorized action');
        }

        $validated = $request->validate([
            'status' => 'required|in:pending,approved,completed,cancelled',
            'notes' => 'nullable|string'
        ]);

        $registration->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Registration status updated successfully'
        ]);
    }
}
