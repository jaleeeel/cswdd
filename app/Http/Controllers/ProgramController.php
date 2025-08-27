<?php

namespace App\Http\Controllers;

use App\Models\Program;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;


class ProgramController extends Controller
{
    /**
     * Display a listing of programs.
     */
    public function index()
    {
        $user = Auth::user();

        if ($user->isSuperAdmin()) {
            $programs = Program::with('department')->get();
        } elseif ($user->isAdmin()) {
            $programs = Program::where('department_id', $user->department_id)
                              ->with('department')
                              ->get();
        } else {
            // Staff can view programs from their department
            $programs = Program::where('department_id', $user->department_id)
                              ->where('is_active', true)
                              ->with('department')
                              ->get();
        }

        return view('programs.index', compact('programs'));
    }

    /**
     * Show the form for creating a new program.
     */
    public function create()
    {
        $this->authorize('create', Program::class);

        $departments = Department::where('is_active', true)->get();

        return view('programs.create', compact('departments'));
    }

    /**
     * Store a newly created program in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create', Program::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'department_id' => 'required|exists:departments,id',
            'is_active' => 'boolean'
        ]);

        $program = Program::create($validated);

        return redirect()->route('programs.index')
                        ->with('success', 'Program created successfully.');
    }

    /**
     * Display the specified program.
     */
    public function show(Program $program)
    {
        $this->authorize('view', $program);

        $program->load(['department', 'services']);

        return view('programs.show', compact('program'));
    }

    /**
     * Show the form for editing the specified program.
     */
    public function edit(Program $program)
    {
        $this->authorize('update', $program);

        $departments = Department::where('is_active', true)->get();

        return view('programs.edit', compact('program', 'departments'));
    }

    /**
     * Update the specified program in storage.
     */
    public function update(Request $request, Program $program)
    {
        $this->authorize('update', $program);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'department_id' => 'required|exists:departments,id',
            'is_active' => 'boolean'
        ]);

        $program->update($validated);

        return redirect()->route('programs.index')
                        ->with('success', 'Program updated successfully.');
    }

    /**
     * Remove the specified program from storage.
     */
    public function destroy(Program $program)
    {
        $this->authorize('delete', $program);

        // Check if program has associated services
        if ($program->services()->count() > 0) {
            return redirect()->back()
                           ->with('error', 'Cannot delete program with associated services.');
        }

        $program->delete();

        return redirect()->route('programs.index')
                        ->with('success', 'Program deleted successfully.');
    }

    /**
     * Get programs by department (AJAX endpoint).
     */
    public function getByDepartment(Department $department): JsonResponse
    {
        $programs = Program::where('department_id', $department->id)
                          ->where('is_active', true)
                          ->select('id', 'name')
                          ->get();

        return response()->json($programs);
    }
}
