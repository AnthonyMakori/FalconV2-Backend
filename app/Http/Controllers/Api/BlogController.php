<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BlogController extends Controller
{
    // GET /api/blogs
    public function index(Request $request)
    {
        $blogs = Blog::query()
            ->when($request->search, fn ($q) =>
                $q->where('title', 'like', "%{$request->search}%")
            )
            ->when($request->category, fn ($q) =>
                $q->where('category', $request->category)
            )
            ->when($request->status, fn ($q) =>
                $q->where('status', $request->status)
            )
            ->latest()
            ->paginate(10);

        return response()->json($blogs);
    }

    // POST /api/blogs
    public function store(Request $request)
    {
        $data = $request->validate([
            'title'    => 'required|string|max:255',
            'content'  => 'required|string',
            'author'   => 'nullable|string|max:255',
            'category' => 'required|string|max:255',
            'status'   => 'required|in:Published,Draft',
        ]);

        $data['slug'] = Str::slug($data['title']);
        $data['published_at'] = $data['status'] === 'Published' ? now() : null;

        $blog = Blog::create($data);

        return response()->json($blog, 201);
    }

    // GET /api/blogs/{id}
    public function show(Blog $blog)
    {
        return response()->json($blog);
    }

    // PUT /api/blogs/{id}
    public function update(Request $request, Blog $blog)
    {
        $data = $request->validate([
            'title'    => 'sometimes|string|max:255',
            'content'  => 'sometimes|string',
            'author'   => 'nullable|string|max:255',
            'category' => 'sometimes|string|max:255',
            'status'   => 'sometimes|in:Published,Draft',
        ]);

        if (isset($data['title'])) {
            $data['slug'] = Str::slug($data['title']);
        }

        if (isset($data['status'])) {
            $data['published_at'] = $data['status'] === 'Published' ? now() : null;
        }

        $blog->update($data);

        return response()->json($blog);
    }

    // DELETE /api/blogs/{id}
    public function destroy(Blog $blog)
    {
        $blog->delete();
        return response()->json(['message' => 'Blog deleted successfully']);
    }
}
