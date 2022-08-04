<?php

namespace App\Http\Shop\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShopResource extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'street' => $this->street,
            'street2' => $this->street2,
            'postal_code' => $this->postal_code,
            'city' => $this->city,
            'country' => $this->country,
            'lat' => $this->lat,
            'lon' => $this->lon,
            'brand' => $this->brand,
            'created_by' => $this->created_by,
            'country_id' => $this->country_id,
            'language_id' => $this->language_id,
            'code_totem' => $this->code_totem,
            'show_percent' => $this->show_percent,
            'source_id' => $this->source_id,
            'phones' => $this->phones,
            'work_days' => $this->work_days,
            'work_hours' => $this->work_hours,
            'info' => $this->info,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'google_place_id' => $this->google_place_id,
            'google_rating' => $this->google_rating,
            'custom_date1' => $this->custom_date1,
            'custom_date2' => $this->custom_date2,
            'custom_date3' => $this->custom_date3,
            'custom_date4' => $this->custom_date4,
            'custom_boolean1' => $this->custom_boolean1,
            'custom_boolean2' => $this->custom_boolean2,
            'custom_text1' => $this->custom_text1,
            'custom_text2' => $this->custom_text2,
            'website_url' => $this->website_url,
            'monday' => $this->monday,
            'tuesday' => $this->tuesday,
            'wednesday' => $this->wednesday,
            'thursday' => $this->thursday,
            'friday' => $this->friday,
            'saturday' => $this->saturday,
            'sunday' => $this->sunday,
            'disabled' => $this->disabled,
            'last_report_sent_at' => $this->last_report_sent_at,
            'user_ratings_total' => $this->user_ratings_total,
            'price_level' => $this->price_level,
            'smicer_info' => $this->smicer_info,
            'monday_hours' => $this->monday_hours,
            'tuesday_hours' => $this->tuesday_hours,
            'wednesday_hours' => $this->wednesday_hours,
            'thursday_hours' => $this->thursday_hours,
            'friday_hours' => $this->friday_hours,
            'saturday_hours' => $this->saturday_hours,
            'sunday_hours' => $this->sunday_hours,
            'private_info' => $this->private_info,
        ];
    }
}
