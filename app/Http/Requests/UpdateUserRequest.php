<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'        => 'required|string|max:255',
            'email'       => "required|email|unique:users,email,{$this->user},user_id",
            'roles'       => 'required|string',
            'division_id' => 'nullable|exists:divisions,division_id',
        ];
    }
}
