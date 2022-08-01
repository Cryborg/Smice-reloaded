<?php

namespace App\Http\User\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserCreateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'api_key' => 'string',
            'birth_date' => 'date',
            'city' => 'string',
            'country_id' => 'integer|exists:country,id',
            'country_name' => 'string',
            'created_by' => 'integer|read:users',
            'current_society_id' => 'integer',
            'disable_cache' => 'boolean',
            'email' => 'email|required|unique_with:user,society_id,{id}|unique:user',
            'first_name' => 'string|required',
            'gender' => 'string|in:male,female',
            'language_id' => 'integer|exists:language,id',
            'last_mission_date' => 'date',
            'last_name' => 'string|required',
            'lat' => 'numeric',
            'lon' => 'numeric',
            'parent_id' => 'alpha_dash',
            'password' => 'string|min:6|required|confirmed',
            'phone' => 'string',
            'picture' => 'string',
            'postal_code' => 'string',
            'registration_date' => 'date',
            'secret_key' => 'string',
            'sleep_by' => 'integer|required',
            'sleep_date' => 'required',
            'sleepstatus' => 'boolean',
            'society_id' => 'integer|required',
            'street' => 'string',
        ];
    }
}
