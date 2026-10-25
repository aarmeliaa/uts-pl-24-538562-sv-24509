<?php

namespace App\Http\Requests;

use App\Models\Media;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMediaRequest extends FormRequest
{
    /**
     * Any logged-in user may create media (the route is behind auth:sanctum).
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Validation rules. A failed rule makes Laravel answer with 422 automatically.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in(Media::TYPES)],
            'status' => ['required', Rule::in(Media::STATUSES)],
            'rating' => ['nullable', 'integer', 'between:1,10'],
            'notes' => ['nullable', 'string'],
            'consumed_at' => ['nullable', 'date'],
        ];
    }
}
