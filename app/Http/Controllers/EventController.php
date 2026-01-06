<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EventController extends Controller
{
    /**
     * GET /api/events
     */
    public function index(Request $request)
    {
        $events = Event::query()
            ->when($request->search, fn ($q) =>
                $q->where('name', 'like', "%{$request->search}%")
            )
            ->when($request->type, fn ($q) =>
                $q->where('type', $request->type)
            )
            ->when($request->status, fn ($q) =>
                $q->where('status', $request->status)
            )
            ->orderBy('date', 'desc')
            ->paginate(10);

        // Add full poster URL
        $events->getCollection()->transform(function ($event) {
            if ($event->poster) {
                $event->poster = asset('storage/' . $event->poster);
            }
            return $event;
        });

        return response()->json([
            'data' => $events
        ]);
    }

    /**
     * POST /api/events
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'date'     => 'required|date',
            'location' => 'required|string|max:255',
            'type'     => 'required|string|max:100',
            'status'   => 'required|in:Upcoming,Completed,Cancelled',
            'poster'   => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        if ($request->hasFile('poster')) {
            $data['poster'] = $request->file('poster')
                ->store('events', 'public');
        }

        $event = Event::create($data);

        if ($event->poster) {
            $event->poster = asset('storage/' . $event->poster);
        }

        return response()->json($event, 201);
    }

    /**
     * GET /api/events/{event}
     */
    public function show(Event $event)
    {
        if ($event->poster) {
            $event->poster = asset('storage/' . $event->poster);
        }

        return response()->json($event);
    }

    /**
     * PUT /api/events/{event}
     */
    public function update(Request $request, Event $event)
    {
        $data = $request->validate([
            'name'     => 'sometimes|string|max:255',
            'date'     => 'sometimes|date',
            'location' => 'sometimes|string|max:255',
            'type'     => 'sometimes|string|max:100',
            'status'   => 'sometimes|in:Upcoming,Completed,Cancelled',
            'poster'   => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        if ($request->hasFile('poster')) {
            if ($event->poster) {
                Storage::disk('public')->delete($event->poster);
            }

            $data['poster'] = $request->file('poster')
                ->store('events', 'public');
        }

        $event->update($data);

        if ($event->poster) {
            $event->poster = asset('storage/' . $event->poster);
        }

        return response()->json($event);
    }

    /**
     * DELETE /api/events/{event}
     */
    public function destroy(Event $event)
    {
        if ($event->poster) {
            Storage::disk('public')->delete($event->poster);
        }

        $event->delete();

        return response()->json([
            'message' => 'Event deleted successfully'
        ]);
    }
}
