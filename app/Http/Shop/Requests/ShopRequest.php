<?php

namespace App\Http\Shop\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ShopRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'brand' => 'string',
            'city' => 'string',
            'code_totem' => 'alpha_num',
            'country' => 'string',
            'country_id' => 'integer|exists:country,id',
            'created_by' => 'integer|required',
            'custom_boolean1' => 'boolean',
            'custom_boolean2' => 'boolean',
            'custom_date1' => 'date',
            'custom_date2' => 'date',
            'custom_date3' => 'date',
            'custom_date4' => 'date',
            'custom_text1' => 'string',
            'custom_text2' => 'string',
            'disabled' => 'boolean',
            'friday' => 'boolean',
            'friday_hours' => 'string',
            'google_place_id' => 'string',
            'google_rating' => 'integer',
            'info' => 'string',
            'language_id' => 'integer|exists:language,id',
            'last_report_sent_at' => 'date',
            'lat' => 'numeric',
            'lon' => 'numeric',
            'monday' => 'boolean',
            'monday_hours' => 'string',
            'name' => 'string|required',
            'phones' => 'string',
            'postal_code' => 'string',
            'private_info' => 'string',
            'saturday' => 'boolean',
            'saturday_hours' => 'string',
            'smicer_info' => 'string',
            'street' => 'string',
            'street2' => 'string',
            'sunday' => 'boolean',
            'sunday_hours' => 'string',
            'thursday' => 'boolean',
            'thursday_hours' => 'string',
            'tuesday' => 'boolean',
            'tuesday_hours' => 'string',
            'website_url' => 'string',
            'wednesday' => 'boolean',
            'wednesday_hours' => 'string',
            'work_days' => 'string',
            'work_hours' => 'string',
        ];
    }
}
