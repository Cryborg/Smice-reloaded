<?php

namespace App\Http\User\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserPermissionRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'advanced_permissions' => 'json',
            'backoffice_menu_permissions' => 'json',
            'download_passage_proof' => 'boolean',
            'edit_survey' => 'boolean',
            'homeboard_permissions' => 'json',
            'import_update_user' => 'boolean',
            'permissions' => 'json',
            'report_visible_fields' => 'json',
            'review_access' => 'boolean',
            'shop_visible_fields' => 'json',
            'user_id' => 'required|integer|read:user',
        ];
    }
}
