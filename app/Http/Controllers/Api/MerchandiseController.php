<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Merchandise;
use Illuminate\Http\Request;

class MerchandiseController extends Controller
{
    /**
     * Get all merchandise
     */
    public function index()
    {
        $merchandise = Merchandise::all()->map(function ($item) {
            $item->image_url = $item->image ? asset($item->image) : null;
            return $item;
        });

        return response()->json($merchandise);
    }

    /**
     * Store new merchandise
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'category' => 'required|string',
            'price'    => 'required|numeric|min:0',
            'stock'    => 'required|integer|min:0',
            'status'   => 'required|in:In Stock,Out of Stock',
            'image'    => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:20480',
        ]);

        $imagePath = null;

        if ($request->hasFile('image')) {
            $image      = $request->file('image');
            $imageName  = time() . '_' . $image->getClientOriginalName();
            $imageDir   = base_path('assets/merchandise');

            // Create directory if it doesn't exist
            if (!file_exists($imageDir)) {
                mkdir($imageDir, 0755, true);
            }

            // Move image
            $image->move($imageDir, $imageName);

            // Save relative path
            $imagePath = 'assets/merchandise/' . $imageName;
        }

        $merchandise = Merchandise::create([
            'name'     => $request->name,
            'category' => $request->category,
            'price'    => $request->price,
            'stock'    => $request->stock,
            'status'   => $request->status,
            'image'    => $imagePath,
        ]);

        return response()->json([
            'message' => 'Merchandise created successfully',
            'data'    => $merchandise,
        ], 201);
    }

    /**
     * Get single merchandise
     */
    public function show(Merchandise $merchandise)
    {
        $merchandise->image_url = $merchandise->image
            ? asset($merchandise->image)
            : null;

        return response()->json($merchandise);
    }

    /**
     * Update merchandise
     */
    public function update(Request $request, Merchandise $merchandise)
    {
        $request->validate([
            'name'     => 'sometimes|string|max:255',
            'category' => 'sometimes|string',
            'price'    => 'sometimes|numeric|min:0',
            'stock'    => 'sometimes|integer|min:0',
            'status'   => 'sometimes|in:In Stock,Out of Stock',
            'image'    => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:20480',
        ]);

        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($merchandise->image && file_exists(base_path($merchandise->image))) {
                unlink(base_path($merchandise->image));
            }

            $image     = $request->file('image');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $imageDir  = base_path('assets/merchandise');

            if (!file_exists($imageDir)) {
                mkdir($imageDir, 0755, true);
            }

            $image->move($imageDir, $imageName);
            $merchandise->image = 'assets/merchandise/' . $imageName;
        }

        $merchandise->update(
            $request->only(['name', 'category', 'price', 'stock', 'status'])
        );

        return response()->json([
            'message' => 'Merchandise updated successfully',
            'data'    => $merchandise,
        ]);
    }

    /**
     * Delete merchandise
     */
    public function destroy(Merchandise $merchandise)
    {
        if ($merchandise->image && file_exists(base_path($merchandise->image))) {
            unlink(base_path($merchandise->image));
        }

        $merchandise->delete();

        return response()->json([
            'message' => 'Merchandise deleted successfully',
        ]);
    }
}
