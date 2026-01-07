<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Merchandise;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MerchandiseController extends Controller
{
    // Get all merchandise
    public function index()
    {
        return response()->json(Merchandise::all());
    }

    // Store new merchandise
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string',
            'price' => 'required|numeric',
            'stock' => 'required|integer',
            'status' => 'required|in:In Stock,Out of Stock',
            'image' => 'nullable|image|max:20480', 
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('merchandise', 'public');
        }

        $merchandise = Merchandise::create([
            'name' => $request->name,
            'category' => $request->category,
            'price' => $request->price,
            'stock' => $request->stock,
            'status' => $request->status,
            'image' => $imagePath,
        ]);

        return response()->json($merchandise, 201);
    }

    // Get single merchandise
    public function show(Merchandise $merchandise)
    {
        return response()->json($merchandise);
    }

    // Update merchandise
    public function update(Request $request, Merchandise $merchandise)
    {
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'category' => 'sometimes|string',
            'price' => 'sometimes|numeric',
            'stock' => 'sometimes|integer',
            'status' => 'sometimes|in:In Stock,Out of Stock',
            'image' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('image')) {
            if ($merchandise->image) {
                Storage::disk('public')->delete($merchandise->image);
            }
            $merchandise->image = $request->file('image')->store('merchandise', 'public');
        }

        $merchandise->update($request->only(['name', 'category', 'price', 'stock', 'status']));

        return response()->json($merchandise);
    }

    // Delete merchandise
    public function destroy(Merchandise $merchandise)
    {
        if ($merchandise->image) {
            Storage::disk('public')->delete($merchandise->image);
        }

        $merchandise->delete();
        return response()->json(['message' => 'Merchandise deleted']);
    }
}
