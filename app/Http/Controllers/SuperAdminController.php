<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Department;
use App\Models\Report;
use Illuminate\Support\Facades\Hash;

class SuperAdminController extends Controller
{
     public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('super.admin');
    }

    public function dashboard()
    {
        $stats = [
            'total_users' => User::count(),
            'active_users' => User::active()->count(),
            'total_departments' => Department::count(),
            'total_reports' => Report::count(),
        ];

        return view('super-admin.dashboard', compact('stats'));
    }

    // User Management
    public function users()
    {
        $users = User::with(['role', 'department'])->paginate(15);
        return view('super-admin.users.index', compact('users'));
    }

    public function toggleUserStatus(User $user)
    {
        $user->update(['is_active' => !$user->is_active]);

        return response()->json([
            'success' => true,
            'message' => $user->is_active ? 'User enabled successfully' : 'User disabled successfully',
            'status' => $user->is_active
        ]);
    }

    // Department Management
    public function departments()
    {
        $departments = Department::withCount(['users', 'programs'])->paginate(15);
        return view('super-admin.departments.index', compact('departments'));
    }

    public function createDepartment(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:departments',
            'description' => 'nullable|string'
        ]);

        Department::create($validated);

        return redirect()->route('super-admin.departments')
                        ->with('success', 'Department created successfully');
    }

    // Global Reports
    public function reports()
    {
        $reports = Report::with(['generatedBy', 'department'])
                        ->orderBy('created_at', 'desc')
                        ->paginate(15);

        return view('super-admin.reports.index', compact('reports'));
    }

    public function generateGlobalReport(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        // Generate report logic here
        $reportData = $this->generateReportData($validated['type'], $validated['start_date'], $validated['end_date']);

        Report::create([
            'title' => ucfirst($validated['type']) . ' Report - Global',
            'type' => $validated['type'],
            'data' => $reportData,
            'generated_by' => auth()->id(),
            'report_period_start' => $validated['start_date'],
            'report_period_end' => $validated['end_date']
        ]);

        return redirect()->route('super-admin.reports')
                        ->with('success', 'Report generated successfully');
    }

    private function generateReportData($type, $startDate, $endDate)
    {
        // Implement report generation logic based on type
        return [];
    }
}
