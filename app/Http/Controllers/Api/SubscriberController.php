<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Subscriber;
use Illuminate\Support\Facades\Validator;

class SubscriberController extends Controller
{
    /**
     * Store a new subscriber.
     */
    public function store(Request $request)
    {
        // Validate request
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:subscribers,email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        // Create subscriber
        $subscriber = Subscriber::create([
            'name' => $request->name,
            'email' => $request->email,
            'status' => 'pending', // can later be 'active' after confirmation
        ]);

        // Optional: send confirmation email here

        return response()->json([
            'success' => true,
            'message' => 'Subscription successful!',
            'data' => $subscriber,
        ]);
    }
}
