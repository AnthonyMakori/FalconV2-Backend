<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;

class EventController extends Controller
{
    /**
     * GET /api/events
     */
    public function index(Request $request)
    {
        $query = Event::query()
            ->where('date', '>=', now())
            ->when($request->search, fn ($q) =>
                $q->where('name', 'like', "%{$request->search}%")
            )
            ->when($request->type, fn ($q) =>
                $q->where('type', $request->type)
            )
            ->when($request->status, fn ($q) =>
                $q->where('status', $request->status)
            )
            ->orderBy('date');

        // Use pagination if requested, otherwise return top 5
        $events = $request->has('paginate')
            ? $query->paginate(10)
            : $query->limit(5)->get();

        $eventsCollection = $events instanceof \Illuminate\Pagination\AbstractPaginator
            ? $events->getCollection()
            : $events;

        $eventsCollection->transform(function ($event) {
            if ($event->poster) {
                $event->poster_url = asset($event->poster); // now using assets folder URL
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
            'name'        => 'required|string|max:255',
            'date'        => 'required|date',
            'location'    => 'required|string|max:255',
            'type'        => 'required|string|max:100',
            'status'      => 'required|in:Upcoming,Completed,Cancelled',
            'price'       => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'poster'      => 'nullable|image|mimes:jpg,jpeg,png,webp|max:20480',
        ]);

        // Handle poster upload to assets folder
        if ($request->hasFile('poster')) {
            $poster = $request->file('poster');
            $posterName = time() . '_' . $poster->getClientOriginalName();
            $posterDir = base_path('assets/events');

            if (!file_exists($posterDir)) {
                mkdir($posterDir, 0755, true);
            }

            $poster->move($posterDir, $posterName);
            $data['poster'] = 'assets/events/' . $posterName;
        }

        $event = Event::create($data);

        if ($event->poster) {
            $event->poster_url = asset($event->poster);
        }

        return response()->json($event, 201);
    }

    /**
     * GET /api/events/{event}
     */
    public function show(Event $event)
    {
        if ($event->poster) {
            $event->poster_url = asset($event->poster);
        }

        return response()->json($event);
    }

    /**
     * PUT /api/events/{event}
     */
    public function update(Request $request, Event $event)
    {
        $data = $request->validate([
            'name'        => 'sometimes|string|max:255',
            'date'        => 'sometimes|date',
            'location'    => 'sometimes|string|max:255',
            'type'        => 'sometimes|string|max:100',
            'status'      => 'sometimes|in:Upcoming,Completed,Cancelled',
            'price'       => 'sometimes|numeric|min:0',
            'description' => 'nullable|string',
            'poster'      => 'nullable|image|mimes:jpg,jpeg,png,webp|max:20480',
        ]);

        if ($request->hasFile('poster')) {
            // Delete old poster if exists
            if ($event->poster && file_exists(base_path($event->poster))) {
                unlink(base_path($event->poster));
            }

            $poster = $request->file('poster');
            $posterName = time() . '_' . $poster->getClientOriginalName();
            $posterDir = base_path('assets/events');

            if (!file_exists($posterDir)) {
                mkdir($posterDir, 0755, true);
            }

            $poster->move($posterDir, $posterName);
            $data['poster'] = 'assets/events/' . $posterName;
        }

        $event->update($data);

        if ($event->poster) {
            $event->poster_url = asset($event->poster);
        }

        return response()->json($event);
    }

    /**
     * DELETE /api/events/{event}
     */
    public function destroy(Event $event)
    {
        if ($event->poster && file_exists(base_path($event->poster))) {
            unlink(base_path($event->poster));
        }

        $event->delete();

        return response()->json([
            'message' => 'Event deleted successfully'
        ]);
    }
}
