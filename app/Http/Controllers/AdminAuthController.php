<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Laravel\Passport\HasApiTokens;


class AdminAuthController extends Controller
{
    public function register(Request $request)
    {
        // Check if there are already two admins
        if (Admin::count() >= 3) {
            return response()->json(['error' => 'Maximum number of admins reached'], 403);
        }
    
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:admins',
            'password' => 'required|string|min:8|confirmed'
        ]);
    
        $admin = Admin::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);
    
        return response()->json(['admin' => $admin], 201);
    }
    

    public function login(Request $request)
{
    $credentials = $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    // Find the admin by email
    $admin = Admin::where('email', $request->email)->first();

    // Check if admin exists and password is correct
    if (!$admin || !Hash::check($request->password, $admin->password)) {
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    // Generate Passport token
    $token = $admin->createToken('AdminToken')->accessToken;

    return response()->json([
        'token' => $token,
        'admin' => $admin
    ], 200);
}
}

