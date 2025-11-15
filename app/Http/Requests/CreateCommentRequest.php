<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateCommentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $ticket = $this->route('ticket');
        return $this->user()->can('view', $ticket);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'comment' => 'required|string|min:3|max:1000',
            'is_internal' => 'nullable|boolean',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'comment.required' => 'Please enter a comment.',
            'comment.min' => 'Comment must be at least 3 characters.',
            'comment.max' => 'Comment must not exceed 1000 characters.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Only builders and admins can create internal comments
        if ($this->is_internal && !$this->user()->isBuilder() && !$this->user()->isAdmin()) {
            $this->merge(['is_internal' => false]);
        }
    }
}
