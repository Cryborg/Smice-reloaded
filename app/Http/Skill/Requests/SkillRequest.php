<?php

namespace App\Http\Skill\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SkillRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => 'required|json',
            'description' => 'required|json',
            'visible' => 'required|boolean',
            'society_id' => 'required|integer|exists:society,id',
            'created_by' => 'required|integer|exists:user,id',
        ];
    }
}
