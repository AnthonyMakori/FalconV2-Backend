<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Staff;

class StaffController extends Controller
{
    // Get all staff
    public function index()
    {
        $staff = Staff::all();
        return response()->json($staff);
    }

    // Get a single staff
    public function show($id)
    {
        $staff = Staff::findOrFail($id);
        return response()->json($staff);
    }

    // Create new staff
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:staff,email',
            'role' => 'required|string|max:255',
            'department' => 'required|string|max:255',
            'status' => 'required|in:Active,On Leave,Inactive',
            'last_active' => 'nullable|date',
            'join_date' => 'nullable|date',
            'avatar' => 'nullable|string',
        ]);

        $staff = Staff::create($validated);

        return response()->json($staff, 201);
    }

    // Update staff
    public function update(Request $request, $id)
    {
        $staff = Staff::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|unique:staff,email,'.$id,
            'role' => 'sometimes|required|string|max:255',
            'department' => 'sometimes|required|string|max:255',
            'status' => 'sometimes|required|in:Active,On Leave,Inactive',
            'last_active' => 'nullable|date',
            'join_date' => 'nullable|date',
            'avatar' => 'nullable|string',
        ]);

        $staff->update($validated);

        return response()->json($staff);
    }

    // Delete staff
    public function destroy($id)
    {
        $staff = Staff::findOrFail($id);
        $staff->delete();

        return response()->json(['message' => 'Staff removed successfully']);
    }
}
