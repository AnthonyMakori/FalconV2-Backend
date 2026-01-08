<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMovieRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'release_year' => 'nullable|integer',
            'duration' => 'nullable|integer',
            'language' => 'nullable|string',
            'genre' => 'nullable|string',
            'status' => 'required|in:published,draft,archived',

            'poster' => 'nullable|image|max:2048',
            'trailer' => 'nullable|mimes:mp4,mov,mkv|max:51200',
            'movie' => 'nullable|mimes:mp4,mov,mkv|max:102400',

            'rental_price' => 'nullable|numeric',
            'purchase_price' => 'nullable|numeric',
            'rental_period' => 'nullable|integer',
            'free_preview' => 'boolean',
            'preview_duration' => 'nullable|integer',

            'seo_title' => 'nullable|string',
            'seo_description' => 'nullable|string',
            'seo_keywords' => 'nullable|string',

            'casts' => 'array',
            'casts.*' => 'string',

            'tags' => 'array',
            'tags.*' => 'string',

            'subtitles.*' => 'file|mimes:srt,vtt'
        ];
    }
}
