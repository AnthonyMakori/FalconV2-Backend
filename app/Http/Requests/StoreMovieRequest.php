<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMovieRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'required|string',

            'release_year' => 'nullable|digits:4',
            'duration' => 'nullable|integer',
            'language' => 'nullable|string|max:255',
            'genre' => 'required|string|max:255',
            'status' => 'required|in:draft,published,archived',

            'rental_price' => 'nullable|numeric',
            'purchase_price' => 'nullable|numeric',
            'rental_period' => 'nullable|integer',
            'free_preview' => 'boolean',
            'preview_duration' => 'nullable|integer',

            'poster' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:51200',
            'trailer' => 'nullable|mimetypes:video/mp4,video/webm|max:204800',
            'subtitles.*' => 'nullable|mimes:srt,vtt|max:5120',

            'bunny_video_id' => 'nullable|string',

            'seo_title' => 'nullable|string|max:255',
            'seo_description' => 'nullable|string',
            'seo_keywords' => 'nullable|string',
        ];
    }
}
