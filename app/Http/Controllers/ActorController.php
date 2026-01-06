<?php

namespace App\Http\Controllers;

use App\Models\Actor;
use Illuminate\Http\Request;

class ActorController extends Controller
{
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'movies' => 'required|integer',
            'series' => 'required|integer',
            'role' => 'required|string|max:255',
        ]);

        $actor = Actor::create($validatedData);

        return response()->json([
            'message' => 'Actor added successfully!',
            'actor' => $actor
        ], 201);
    }
    public function index()
    {
        $actors = Actor::all();

        return response()->json($actors);
    }
}

