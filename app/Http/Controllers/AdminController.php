<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Program;
use App\Models\Service;
use App\Models\Report;

class AdminController extends Controller
{
    public function __construct()
    {
    $this->middleware('auth');
        $this->middleware('admin');
    }

    public function dashboard()
    {
        $department = auth()->user()->department;

        $stats = [
            'department_users' => $department->users()->count(),
            'active_staff' => $department->users()->active()->count(),
            'total_programs' => $department->programs()->count(),
            'department_reports' => Report::where('department_id', $department->id)->count(),
        ];

        return view('admin.dashboard', compact('stats', 'department'));
    }

    // Staff Management (within department)
    public function staff()
    {
        $staff = User::where('department_id', auth()->user()->department_id)
                    ->with('role')
                    ->paginate(15);

        return view('admin.staff.index', compact('staff'));
    }

    public function toggleStaffStatus(User $user)
    {
        // Check if user belongs to same department
        if ($user->department_id !== auth()->user()->department_id) {
            abort(403, 'Unauthorized action');
        }

        $user->update(['is_active' => !$user->is_active]);

        return response()->json([
            'success' => true,
            'message' => $user->is_active ? 'Staff enabled successfully' : 'Staff disabled successfully',
            'status' => $user->is_active
        ]);
    }

    // Program Management
    public function programs()
    {
        $programs = auth()->user()->department->programs()
                           ->withCount('services')
                           ->paginate(15);

        return view('admin.programs.index', compact('programs'));
    }

    public function createProgram(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string'
        ]);

        $validated['department_id'] = auth()->user()->department_id;

        Program::create($validated);

        return redirect()->route('admin.programs')
                        ->with('success', 'Program created successfully');
    }

    // Service Management
    public function services()
    {
        $services = Service::whereHas('program', function($query) {
                              $query->where('department_id', auth()->user()->department_id);
                          })
                          ->with(['program'])
                          ->paginate(15);

        return view('admin.services.index', compact('services'));
    }

    public function createService(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'program_id' => 'required|exists:programs,id',
            'fee' => 'nullable|numeric|min:0'
        ]);

        // Verify program belongs to admin's department
        $program = Program::where('id', $validated['program_id'])
                         ->where('department_id', auth()->user()->department_id)
                         ->firstOrFail();

        Service::create($validated);

        return redirect()->route('admin.services')
                        ->with('success', 'Service created successfully');
    }

    // Department Reports
    public function reports()
    {
        $reports = Report::where('department_id', auth()->user()->department_id)
                        ->with('generatedBy')
                        ->orderBy('created_at', 'desc')
                        ->paginate(15);

        return view('admin.reports.index', compact('reports'));
    }
}
