<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Merchandise;
use Illuminate\Http\Request;

class MerchandiseController extends Controller
{
    /**
     * GET /api/merchandise
     */
    public function index()
    {
        $merchandise = Merchandise::latest()->get();

        $merchandise->transform(function ($item) {
            if ($item->image) {
                $item->image_url = asset($item->image); // image stored in assets folder
            }
            return $item;
        });

        return response()->json($merchandise);
    }

    /**
     * POST /api/merchandise
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'category' => 'required|string',
            'price'    => 'required|numeric|min:0',
            'stock'    => 'required|integer|min:0',
            'status'   => 'required|in:In Stock,Out of Stock',
            'image'    => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:20480',
        ]);

        // Handle image upload to assets folder
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $imageDir = base_path('assets/merchandise');

            if (!file_exists($imageDir)) {
                mkdir($imageDir, 0755, true);
            }

            $image->move($imageDir, $imageName);
            $data['image'] = 'assets/merchandise/' . $imageName;
        }

        $merchandise = Merchandise::create($data);

        if ($merchandise->image) {
            $merchandise->image_url = asset($merchandise->image);
        }

        return response()->json([
            'message' => 'Merchandise created successfully',
            'data'    => $merchandise,
        ], 201);
    }

    /**
     * GET /api/merchandise/{merchandise}
     */
    public function show(Merchandise $merchandise)
    {
        if ($merchandise->image) {
            $merchandise->image_url = asset($merchandise->image);
        }

        return response()->json($merchandise);
    }

    /**
     * PUT /api/merchandise/{merchandise}
     */
    public function update(Request $request, Merchandise $merchandise)
    {
        $data = $request->validate([
            'name'     => 'sometimes|string|max:255',
            'category' => 'sometimes|string',
            'price'    => 'sometimes|numeric|min:0',
            'stock'    => 'sometimes|integer|min:0',
            'status'   => 'sometimes|in:In Stock,Out of Stock',
            'image'    => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:20480',
        ]);

        // Handle image upload to assets folder
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($merchandise->image && file_exists(base_path($merchandise->image))) {
                unlink(base_path($merchandise->image));
            }

            $image = $request->file('image');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $imageDir = base_path('assets/merchandise');

            if (!file_exists($imageDir)) {
                mkdir($imageDir, 0755, true);
            }

            $image->move($imageDir, $imageName);
            $data['image'] = 'assets/merchandise/' . $imageName;
        }

        $merchandise->update($data);

        if ($merchandise->image) {
            $merchandise->image_url = asset($merchandise->image);
        }

        return response()->json([
            'message' => 'Merchandise updated successfully',
            'data'    => $merchandise,
        ]);
    }

    /**
     * DELETE /api/merchandise/{merchandise}
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
