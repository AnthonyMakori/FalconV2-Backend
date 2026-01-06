<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    // Get all subscriptions
    public function index()
    {
        return response()->json(Subscription::with(['user', 'plan'])->get());
    }

    // Store a new subscription
    public function store(Request $request)
{
    $request->validate([
        'user_id' => 'required|exists:users,id',
        'plan_id' => 'required|exists:plans,id',
        'amount' => 'required|numeric|min:0',
        'start_date' => 'required|date',
        'end_date' => 'required|date|after:start_date',
    ]);

    // If validation passes, create the subscription
    $subscription = Subscription::create([
        'user_id' => $request->user_id,
        'plan_id' => $request->plan_id,
        'amount' => $request->amount,
        'status' => 'active',
        'start_date' => $request->start_date,
        'end_date' => $request->end_date,
    ]);

    return response()->json($subscription, 201);
}


    // Get a single subscription
    public function show($id)
    {
        $subscription = Subscription::with(['user', 'plan'])->findOrFail($id);
        return response()->json($subscription);
    }

    // Update a subscription (e.g., cancel or extend)
    public function update(Request $request, $id)
    {
        $subscription = Subscription::findOrFail($id);

        $request->validate([
            'status' => 'in:active,cancelled,expired',
            'end_date' => 'date|after:start_date',
        ]);

        $subscription->update($request->all());

        return response()->json($subscription);
    }

    // Delete a subscription
    public function destroy($id)
    {
        $subscription = Subscription::findOrFail($id);
        $subscription->delete();

        return response()->json(['message' => 'Subscription deleted successfully.']);
    }
}

