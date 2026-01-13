<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:posts,slug,' . $this->post?->id,
            'category_id' => 'required|exists:post_categories,id', // Sesuai nama tabel baru
            'status' => 'required|in:DRAFT,PUBLISHED,ARCHIVED',
            'content' => 'required|string',
            'excerpt' => 'nullable|string',
            
            // Media
            'thumbnail' => 'nullable|image|max:2048', // 2MB
            
            // Event Meta (Nullable)
            'event_location' => 'nullable|string',
            'event_date_start' => 'nullable|date',
            'event_date_end' => 'nullable|date|after_or_equal:event_date_start',

            // Files (Array)
            'attachments.*' => 'nullable|file|max:10240', // Max 10MB per file
            'galleries.*' => 'nullable|image|max:5120', // Max 5MB per foto
        ];
    }
}