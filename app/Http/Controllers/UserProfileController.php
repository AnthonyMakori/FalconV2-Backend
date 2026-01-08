<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserProfileController extends Controller
{
    public function me(Request $request)
    {
        $user = $request->user();

        $subscription = $user->subscriptions()
            ->where('status', 'active')
            ->latest()
            ->first();

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'avatar' => $user->avatar, // nullable
            'member_since' => $user->created_at->toDateString(),
            'subscription' => $subscription ? [
                'plan' => $subscription->plan_name,
                'expires_at' => $subscription->expires_at,
            ] : null,
        ]);
    }
}
