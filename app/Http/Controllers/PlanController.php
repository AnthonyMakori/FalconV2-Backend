<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use Illuminate\Http\Request;

class PlanController extends Controller
{
    // Fetch all plans
    public function index()
    {
        $plans = Plan::all();
        return response()->json($plans);
    }

    // Fetch a single plan by ID
    public function show($id)
    {
        $plan = Plan::findOrFail($id);
        return response()->json($plan);
    }

    // Store a new plan
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'duration' => 'required|integer|min:1',  // duration in months/days
            'description' => 'nullable|string',
        ]);

        $plan = Plan::create([
            'name' => $request->name,
            'price' => $request->price,
            'duration' => $request->duration,
            'description' => $request->description,
        ]);

        return response()->json($plan, 201);
    }

    // Update an existing plan
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'nullable|string|max:255',
            'price' => 'nullable|numeric|min:0',
            'duration' => 'nullable|integer|min:1',
            'description' => 'nullable|string',
        ]);

        $plan = Plan::findOrFail($id);

        $plan->update($request->only('name', 'price', 'duration', 'description'));

        return response()->json($plan);
    }

    // Delete a plan
    public function destroy($id)
    {
        $plan = Plan::findOrFail($id);
        $plan->delete();

        return response()->json(['message' => 'Plan deleted successfully']);
    }
}

