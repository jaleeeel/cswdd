<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\Program;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class ServiceController extends Controller
{
    /**
     * Display a listing of services.
     */
    public function index()
    {
        $user = Auth::user();

        if ($user->isSuperAdmin()) {
            $services = Service::with(['program.department'])->get();
        } elseif ($user->isAdmin()) {
            $services = Service::whereHas('program', function($query) use ($user) {
                            $query->where('department_id', $user->department_id);
                        })
                        ->with(['program.department'])
                        ->get();
        } else {
            // Staff can view services from their department
            $services = Service::whereHas('program', function($query) use ($user) {
                            $query->where('department_id', $user->department_id);
                        })
                        ->where('is_active', true)
                        ->with(['program.department'])
                        ->get();
        }

        return view('services.index', compact('services'));
    }

    /**
     * Show the form for creating a new service.
     */
    public function create()
    {
        $this->authorize('create', Service::class);

        $user = Auth::user();

        if ($user->isSuperAdmin()) {
            $programs = Program::where('is_active', true)
                              ->with('department')
                              ->get();
        } else {
            $programs = Program::where('department_id', $user->department_id)
                              ->where('is_active', true)
                              ->with('department')
                              ->get();
        }

        return view('services.create', compact('programs'));
    }

    /**
     * Store a newly created service in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create', Service::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'program_id' => 'required|exists:programs,id',
            'duration_hours' => 'nullable|integer|min:1',
            'max_participants' => 'nullable|integer|min:1',
            'is_active' => 'boolean'
        ]);

        $service = Service::create($validated);

        return redirect()->route('services.index')
                        ->with('success', 'Service created successfully.');
    }

    /**
     * Display the specified service.
     */
    public function show(Service $service)
    {
        $this->authorize('view', $service);

        $service->load(['program.department', 'registrations.client']);

        return view('services.show', compact('service'));
    }

    /**
     * Show the form for editing the specified service.
     */
    public function edit(Service $service)
    {
        $this->authorize('update', $service);

        $user = Auth::user();

        if ($user->isSuperAdmin()) {
            $programs = Program::where('is_active', true)
                              ->with('department')
                              ->get();
        } else {
            $programs = Program::where('department_id', $user->department_id)
                              ->where('is_active', true)
                              ->with('department')
                              ->get();
        }

        return view('services.edit', compact('service', 'programs'));
    }

    /**
     * Update the specified service in storage.
     */
    public function update(Request $request, Service $service)
    {
        $this->authorize('update', $service);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'program_id' => 'required|exists:programs,id',
            'duration_hours' => 'nullable|integer|min:1',
            'max_participants' => 'nullable|integer|min:1',
            'is_active' => 'boolean'
        ]);

        $service->update($validated);

        return redirect()->route('services.index')
                        ->with('success', 'Service updated successfully.');
    }

    /**
     * Remove the specified service from storage.
     */
    public function destroy(Service $service)
    {
        $this->authorize('delete', $service);

        // Check if service has associated registrations
        if ($service->registrations()->count() > 0) {
            return redirect()->back()
                           ->with('error', 'Cannot delete service with existing registrations.');
        }

        $service->delete();

        return redirect()->route('services.index')
                        ->with('success', 'Service deleted successfully.');
    }

    /**
     * Get services by program (AJAX endpoint).
     */
    public function getByProgram(Program $program): JsonResponse
    {
        $services = Service::where('program_id', $program->id)
                          ->where('is_active', true)
                          ->select('id', 'name', 'duration_hours', 'max_participants')
                          ->get();

        return response()->json($services);
    }
}
