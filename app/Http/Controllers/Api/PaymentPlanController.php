<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PaymentPlan;


class PaymentPlanController extends Controller
{
    public function index()
    {
        return PaymentPlan::latest()->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'price' => 'required|numeric',
            'currency' => 'required|string',
            'duration' => 'required|string',
            'features' => 'required|string',
        ]);

        return PaymentPlan::create($validated);
    }

    public function show(PaymentPlan $paymentPlan)
    {
        return $paymentPlan;
    }

    public function update(Request $request, PaymentPlan $paymentPlan)
    {
        $paymentPlan->update($request->only([
            'name', 'price', 'currency', 'duration', 'features'
        ]));

        return $paymentPlan;
    }

    public function toggleStatus(PaymentPlan $paymentPlan)
    {
        $paymentPlan->update([
            'is_active' => ! $paymentPlan->is_active
        ]);

        return $paymentPlan;
    }

    public function destroy(PaymentPlan $paymentPlan)
    {
        $paymentPlan->delete();
        return response()->noContent();
    }
}
