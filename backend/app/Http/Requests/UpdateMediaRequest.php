<?php

namespace App\Http\Requests;

use App\Models\Media;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMediaRequest extends FormRequest
{
    /**
     * Ownership is checked in the controller (other users' media returns 404).
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Same rules as creating, but "sometimes" lets PATCH send only the changed fields.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'type' => ['sometimes', 'required', Rule::in(Media::TYPES)],
            'status' => ['sometimes', 'required', Rule::in(Media::STATUSES)],
            'rating' => ['nullable', 'integer', 'between:1,10'],
            'notes' => ['nullable', 'string'],
            'consumed_at' => ['nullable', 'date'],
        ];
    }
}
