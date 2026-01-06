<?php

namespace App\Http\Controllers;

use App\Models\Director;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class DirectorController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Director::all(), 200);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'age' => 'required|integer|min:18',
            'movies' => 'required|integer|min:0',
            'series' => 'required|integer|min:0',
            'ranking' => 'required|integer|min:1',
        ]);

        $director = Director::create($request->all());
        return response()->json($director, 201);
    }

    public function show($id): JsonResponse
    {
        $director = Director::find($id);
        return $director ? response()->json($director, 200) : response()->json(['message' => 'Director not found'], 404);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $director = Director::find($id);
        if (!$director) {
            return response()->json(['message' => 'Director not found'], 404);
        }

        $director->update($request->all());
        return response()->json($director, 200);
    }

    public function destroy($id): JsonResponse
    {
        $director = Director::find($id);
        if (!$director) {
            return response()->json(['message' => 'Director not found'], 404);
        }

        $director->delete();
        return response()->json(['message' => 'Director deleted successfully'], 200);
    }
}
