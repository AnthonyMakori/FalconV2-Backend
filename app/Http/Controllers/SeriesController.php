<?php

namespace App\Http\Controllers;

use App\Models\Series;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SeriesController extends Controller
{
    public function index()
    {
        return response()->json(Series::all(), 200);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string',
            'category' => 'required|string',
            'price' => 'required|numeric',
            'release_date' => 'required|date',
            'episodes' => 'required|integer',
            'poster' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5048',
        ]);

        $data = $request->all();

        if ($request->hasFile('poster')) {
            $posterPath = $request->file('poster')->store('posters', 'public');
            $data['poster'] = $posterPath;
        }

        $series = Series::create($data);
        return response()->json($series, 201);
    }

    public function show($id)
    {
        $series = Series::findOrFail($id);
        return response()->json($series, 200);
    }

    public function update(Request $request, $id)
    {
        $series = Series::findOrFail($id);

        $request->validate([
            'title' => 'sometimes|required|string',
            'category' => 'sometimes|required|string',
            'price' => 'sometimes|required|numeric',
            'release_date' => 'sometimes|required|date',
            'episodes' => 'sometimes|required|integer',
            'poster' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5048',
        ]);

        $data = $request->all();

        if ($request->hasFile('poster')) {
            // Delete the old poster if it exists
            if ($series->poster) {
                Storage::disk('public')->delete($series->poster);
            }

            // Store new poster
            $posterPath = $request->file('poster')->store('posters', 'public');
            $data['poster'] = $posterPath;
        }

        $series->update($data);
        return response()->json($series, 200);
    }

    public function destroy($id)
    {
        $series = Series::findOrFail($id);

        // Delete the poster if it exists
        if ($series->poster) {
            Storage::disk('public')->delete($series->poster);
        }

        $series->delete();
        return response()->json(['message' => 'Series deleted successfully'], 200);
    }

    public function uploadEpisode(Request $request)
    {
        $request->validate([
            'series_id' => 'required|exists:series,id',
            'episode_file' => 'required|file|mimes:mp4,mkv,avi',
        ]);

        $path = $request->file('episode_file')->store('episodes', 'public');

        return response()->json(['message' => 'Episode uploaded successfully', 'path' => $path], 200);
    }

    public function getUpcomingSeries()
    {
        $upcomingSeries = Series::where('release_date', '>', Carbon::now())
            ->orderBy('release_date', 'asc')
            ->get();

        return response()->json($upcomingSeries);
    }
}
