<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Registration;
use App\Models\Program;
use App\Models\Service;
use App\Models\Department;
use App\Models\ClientRegistration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    /**
     * Display a listing of available reports.
     */
    public function index()
    {
        $user = Auth::user();

        // Get basic statistics based on user role
        $stats = $this->getBasicStats($user);

        return view('reports.index', compact('stats'));
    }

    /**
     * Display the specified report.
     */
    public function show(Request $request, $reportType)
    {
        $user = Auth::user();

        switch ($reportType) {
            case 'clients':
                return $this->clientReport($request, $user);
            case 'registrations':
                return $this->registrationReport($request, $user);
            case 'services':
                return $this->serviceReport($request, $user);
            case 'programs':
                return $this->programReport($request, $user);
            default:
                abort(404, 'Report not found');
        }
    }

    /**
     * Generate client report.
     */
    private function clientReport(Request $request, $user)
    {
        $query = Client::query();

        // Apply role-based filtering
        if ($user->isStaff()) {
            $query->whereHas('registrations', function($q) use ($user) {
                $q->where('registered_by', $user->id);
            });
        } elseif ($user->isAdmin()) {
            $query->whereHas('registrations.service.program', function($q) use ($user) {
                $q->where('department_id', $user->department_id);
            });
        }

        // Apply date filters
        if ($request->has('start_date') && $request->start_date) {
            $query->where('created_at', '>=', Carbon::parse($request->start_date));
        }
        if ($request->has('end_date') && $request->end_date) {
            $query->where('created_at', '<=', Carbon::parse($request->end_date)->endOfDay());
        }

        $clients = $query->with(['registrations.service.program.department'])
                        ->orderBy('created_at', 'desc')
                        ->paginate(15);

        $summary = [
            'total_clients' => $query->count(),
            'active_clients' => $query->where('is_active', true)->count(),
            'recent_clients' => $query->where('created_at', '>=', Carbon::now()->subDays(30))->count(),
        ];

        return view('reports.clients', compact('clients', 'summary'));
    }

    /**
     * Generate registration report.
     */
    private function registrationReport(Request $request, $user)
    {
        $query = ClientRegistration::query();

        // Apply role-based filtering
        if ($user->isStaff()) {
            $query->where('registered_by', $user->id);
        } elseif ($user->isAdmin()) {
            $query->whereHas('service.program', function($q) use ($user) {
                $q->where('department_id', $user->department_id);
            });
        }

        // Apply filters
        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }
        if ($request->has('start_date') && $request->start_date) {
            $query->where('registered_at', '>=', Carbon::parse($request->start_date));
        }
        if ($request->has('end_date') && $request->end_date) {
            $query->where('registered_at', '<=', Carbon::parse($request->end_date)->endOfDay());
        }

        $registrations = $query->with(['client', 'service.program.department', 'registeredBy'])
                              ->orderBy('registered_at', 'desc')
                              ->paginate(15);

        $summary = [
            'total_registrations' => $query->count(),
            'active_registrations' => $query->where('status', 'active')->count(),
            'completed_registrations' => $query->where('status', 'completed')->count(),
            'cancelled_registrations' => $query->where('status', 'cancelled')->count(),
        ];

        return view('reports.registrations', compact('registrations', 'summary'));
    }

    /**
     * Generate service report.
     */
    private function serviceReport(Request $request, $user)
    {
        $query = Service::query();

        // Apply role-based filtering
        if ($user->isAdmin()) {
            $query->whereHas('program', function($q) use ($user) {
                $q->where('department_id', $user->department_id);
            });
        } elseif ($user->isStaff()) {
            $query->whereHas('program', function($q) use ($user) {
                $q->where('department_id', $user->department_id);
            });
        }

        $services = $query->withCount(['registrations'])
                         ->with(['program.department'])
                         ->orderBy('registrations_count', 'desc')
                         ->paginate(15);

        $summary = [
            'total_services' => $query->count(),
            'active_services' => $query->where('is_active', true)->count(),
            'total_registrations' => ClientRegistration::whereHas('service', function($q) use ($query) {
                $q->whereIn('id', $query->pluck('id'));
            })->count(),
        ];

        return view('reports.services', compact('services', 'summary'));
    }

    /**
     * Generate program report.
     */
    private function programReport(Request $request, $user)
    {
        $query = Program::query();

        // Apply role-based filtering
        if ($user->isAdmin()) {
            $query->where('department_id', $user->department_id);
        } elseif ($user->isStaff()) {
            $query->where('department_id', $user->department_id);
        }

        $programs = $query->withCount(['services', 'registrations'])
                         ->with(['department'])
                         ->orderBy('registrations_count', 'desc')
                         ->paginate(15);

        $summary = [
            'total_programs' => $query->count(),
            'active_programs' => $query->where('is_active', true)->count(),
            'total_services' => Service::whereIn('program_id', $query->pluck('id'))->count(),
        ];

        return view('reports.programs', compact('programs', 'summary'));
    }

    /**
     * Get basic statistics for dashboard.
     */
    private function getBasicStats($user)
    {
        $stats = [];

        if ($user->isSuperAdmin()) {
            $stats = [
                'total_clients' => Client::count(),
                'total_registrations' => ClientRegistration::count(),
                'total_programs' => Program::count(),
                'total_services' => Service::count(),
                'active_registrations' => ClientRegistration::where('status', 'active')->count(),
            ];
        } elseif ($user->isAdmin()) {
            $stats = [
                'total_clients' => Client::whereHas('registrations.service.program', function($q) use ($user) {
                    $q->where('department_id', $user->department_id);
                })->count(),
                'total_registrations' => ClientRegistration::whereHas('service.program', function($q) use ($user) {
                    $q->where('department_id', $user->department_id);
                })->count(),
                'total_programs' => Program::where('department_id', $user->department_id)->count(),
                'total_services' => Service::whereHas('program', function($q) use ($user) {
                    $q->where('department_id', $user->department_id);
                })->count(),
                'active_registrations' => ClientRegistration::whereHas('service.program', function($q) use ($user) {
                    $q->where('department_id', $user->department_id);
                })->where('status', 'active')->count(),
            ];
        } else {
            $stats = [
                'my_clients' => Client::whereHas('registrations', function($q) use ($user) {
                    $q->where('registered_by', $user->id);
                })->count(),
                'my_registrations' => ClientRegistration::where('registered_by', $user->id)->count(),
                'active_registrations' => ClientRegistration::where('registered_by', $user->id)
                                                    ->where('status', 'active')
                                                    ->count(),
            ];
        }

        return $stats;
    }

    /**
     * Export report data (can be extended for Excel, PDF export).
     */
    public function export(Request $request, $reportType)
    {
        // This method can be extended to export reports in various formats
        // For now, it's a placeholder
        return response()->json(['message' => 'Export functionality coming soon']);
    }
}
