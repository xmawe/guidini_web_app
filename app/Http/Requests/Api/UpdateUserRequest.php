<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => 'required|email|unique:users,email,' . Auth::id(),
            'firstName' => 'required|string|max:255',
            'cityId' => ['required', 'exists:cities,id'], // Validate the city ID
            'lastName' => 'required|string|max:255',
            'phoneNumber' => [
                'required',
                'string',
                Rule::unique('users', 'phone_number')->ignore(Auth::user()->id), // Ignore the current record's phone number
            ],
        ];
    }
    protected function prepareForValidation()
    {
        $this->merge([
            'email' => $this->email ?? null,
            'first_name' => $this->firstName ?? null,
            'last_name' => $this->lastName ?? null,
            'phone_number' => $this->phoneNumber ?? null,
            'city_id' => $this->cityId ?? null
        ]);
    }
}
