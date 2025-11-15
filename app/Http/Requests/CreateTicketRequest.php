<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateTicketRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->isHomeowner();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'property_id' => 'required|exists:properties,id',
            'area_of_issue' => 'required|string|max:255',
            'description' => 'required|string|min:10',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120', // 5MB max
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
            'property_id.required' => 'Please select a property for this ticket.',
            'property_id.exists' => 'The selected property does not exist.',
            'description.min' => 'Please provide a detailed description (at least 10 characters).',
            'image.max' => 'The image size must not exceed 5MB.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Ensure property belongs to the authenticated user
        $property = \App\Models\Property::find($this->property_id);

        if ($property && $property->user_id !== $this->user()->id) {
            abort(403, 'You can only create tickets for your own properties.');
        }
    }
}
