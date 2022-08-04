<?php

namespace App\Http\Group\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GroupRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'society_id' => 'integer|required|exists:society,id',
            'name' => 'array|required',
            'created_by' => 'integer|required|exists:user,id',
        ];
    }
}
