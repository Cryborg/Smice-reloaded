<?php

namespace App\Http\Skill\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddSkillsRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'skills' => 'required|array|exists:skill,id',
        ];
    }
}
