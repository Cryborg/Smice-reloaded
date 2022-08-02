<?php

namespace App\Models;

/**
 * App\Models\GoogleReviews
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel addPublicResources()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel relations()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieve()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieveAll()
 * @mixin \Eloquent
 * @property int $id
 * @property string $google_place_id
 * @property int $shop_id
 * @property string|null $author_name
 * @property string|null $author_url
 * @property string|null $language
 * @property string|null $profile_photo_url
 * @property string|null $rating
 * @property string|null $relative_time_description
 * @property string|null $text
 * @property string|null $time
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon $created_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\GoogleReviews whereAuthorName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\GoogleReviews whereAuthorUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\GoogleReviews whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\GoogleReviews whereGooglePlaceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\GoogleReviews whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\GoogleReviews whereLanguage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\GoogleReviews whereProfilePhotoUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\GoogleReviews whereRating($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\GoogleReviews whereRelativeTimeDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\GoogleReviews whereShopId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\GoogleReviews whereText($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\GoogleReviews whereTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\GoogleReviews whereUpdatedAt($value)
 */
class GoogleReviews extends SmiceModel
{
    protected $table        = 'google_reviews';

    protected array $rules = [

    ];
}
