<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'role'     => 'required|string',
            'status'   => 'required|string',
        ]);

        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role'     => $validated['role'],
            'status'   => ucfirst($validated['status']),
        ]);

        return response()->json([
            'message' => 'User created successfully',
            'user'    => $user,
        ], 201);
    }
    /**
     * GET /api/users
     * List users with search, filters & pagination
     */
    public function index(Request $request)
    {
        $query = User::query();

        // 🔍 Search (name or email)
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        // 🎭 Role filter
        if ($request->filled('role') && $request->role !== 'all') {
            $query->where('role', ucfirst($request->role));
        }

        // 🚦 Status filter
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', ucfirst($request->status));
        }

        // 📄 Select only what frontend needs
        $users = $query->select(
                'id',
                'name',
                'email',
                'role',
                'status',
                'subscription',
                'total_purchases',
                'last_login_at',
                'created_at'
            )
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json($users);
    }

    /**
     * PATCH /api/users/{user}/status
     */
    public function updateStatus(Request $request, User $user)
    {
        $request->validate([
            'status' => 'required|in:Active,Inactive,Pending,Blocked',
        ]);

        $user->update([
            'status' => $request->status,
        ]);

        return response()->json([
            'message' => 'User status updated successfully',
            'status' => $user->status,
        ]);
    }

    /**
     * DELETE /api/users/{user}
     */
    public function destroy(User $user)
    {
        $user->delete();

        return response()->json([
            'message' => 'User deleted successfully',
        ]);
    }
}
