<?php

namespace App\Http\User\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserListRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'filter' => [
                'group' => '',
                'id' => '',
            ]
        ];
    }
}
