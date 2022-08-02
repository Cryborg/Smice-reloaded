<?php

namespace App\Models;

use App\Http\User\Models\User;
use App\Interfaces\iProtected;
use App\Interfaces\iREST;
use App\Jobs\ShopGoogleRatingJob;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Support\Facades\DB;

/**
 * App\Models\Shop
 *
 * @property int $id
 * @property string $name
 * @property string|null $street
 * @property string|null $street2
 * @property string|null $postal_code
 * @property string|null $city
 * @property string|null $country
 * @property float|null $lat
 * @property float|null $lon
 * @property string|null $brand
 * @property int|null $created_by
 * @property int|null $country_id
 * @property int|null $language_id
 * @property string|null $code_totem
 * @property bool $show_percent
 * @property int|null $source_id
 * @property string|null $phones
 * @property string|null $work_days
 * @property string|null $work_hours
 * @property string|null $info
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property string|null $google_place_id
 * @property float $google_rating
 * @property string|null $custom_date1
 * @property string|null $custom_date2
 * @property string|null $custom_date3
 * @property string|null $custom_date4
 * @property bool|null $custom_boolean1
 * @property bool|null $custom_boolean2
 * @property string|null $custom_text1
 * @property string|null $custom_text2
 * @property string|null $website_url
 * @property bool $monday
 * @property bool $tuesday
 * @property bool $wednesday
 * @property bool $thursday
 * @property bool $friday
 * @property bool $saturday
 * @property bool $sunday
 * @property bool $disabled
 * @property string|null $last_report_sent_at
 * @property int|null $user_ratings_total
 * @property int|null $price_level
 * @property string|null $smicer_info
 * @property string|null $private_info
 * @property string|null $monday_hours
 * @property string|null $tuesday_hours
 * @property string|null $wednesday_hours
 * @property string|null $thursday_hours
 * @property string|null $friday_hours
 * @property string|null $saturday_hours
 * @property string|null $sunday_hours
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Axe[] $axes
 * @property-read \App\Models\Language|null $language
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Mission[] $missions
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Society[] $societies
 * @property-read \App\Models\Society|null $society
 * @property-read \App\Models\TodoistProject $todoistProject
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\ShopContact[] $shopContacts
 * @property-read \App\Models\User|null $createdBy
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\User[] $users
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Wave[] $waves
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\ShopCache[] $shopCaches
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel addPublicResources()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Shop minimum()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Shop relations()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieve()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieveAll()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Shop whereBrand($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Shop whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Shop whereCodeTotem($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Shop whereCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Shop whereCountryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Shop whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Shop whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Shop whereGooglePlaceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Shop whereGoogleRating($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Shop whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Shop whereInfo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Shop whereLanguageId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Shop whereLat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Shop whereLon($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Shop whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Shop wherePhones($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Shop wherePostalCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Shop whereShowPercent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Shop whereSourceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Shop whereStreet($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Shop whereStreet2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Shop whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Shop whereWorkDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Shop whereWorkHours($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Shop whereCustomBoolean1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Shop whereCustomBoolean2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Shop whereCustomDate1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Shop whereCustomDate2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Shop whereCustomDate3($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Shop whereCustomDate4($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Shop whereCustomText1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Shop whereCustomText2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Shop whereWebsiteUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Shop whereFriday($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Shop whereMonday($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Shop whereSaturday($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Shop whereSunday($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Shop whereThursday($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Shop whereTuesday($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Shop whereWednesday($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Shop whereDisabled($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Shop whereLastReportSentAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Shop wherePriceLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Shop whereUserRatingsTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Shop whereSmicerInfo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Shop whereFridayHours($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Shop whereMondayHours($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Shop wherePrivateInfo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Shop whereSaturdayHours($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Shop whereSundayHours($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Shop whereThursdayHours($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Shop whereTuesdayHours($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Shop whereWednesdayHours($value)
 * @mixin \Eloquent
 */
class Shop extends SmiceModel implements iREST, iProtected
{
    use DispatchesJobs;

    const DISABLED_YES          = true;
    const DISABLED_NO           = false;

    protected $table = 'shop';

    protected $primaryKey = 'id';

    protected string $list_table = 'show_shops';

    public static function getURI()
    {
        return 'shops';
    }

    public static function getName()
    {
        return 'shop';
    }

    public function getModuleName()
    {
        return 'shops';
    }

    protected $fillable = [
        'name',
        'street',
        'street2',
        'postal_code',
        'city',
        'country',
        'lat',
        'lon',
        'code_totem',
        'country_id',
        'language_id',
        'phones',
        'work_days',
        'work_hours',
        'info',
        'brand',
        'created_by',
        'google_place_id',
        'google_rating',
        'custom_date1',
        'custom_date2',
        'custom_date3',
        'custom_date4',
        'custom_boolean1',
        'custom_boolean2',
        'custom_text1',
        'custom_text2',
        'monday',
        'tuesday',
        'wednesday',
        'thursday',
        'friday',
        'saturday',
        'sunday',
        'monday_hours',
        'tuesday_hours',
        'wednesday_hours',
        'thursday_hours',
        'friday_hours',
        'saturday_hours',
        'sunday_hours',
        'website_url',
        'disabled',
        'last_report_sent_at',
        'smicer_info',
        'private_info'
    ];

    protected $hidden = [
        'url',
        'created_by',
        'pivot'
    ];

    protected array $list_rows = [
        'name',
        'brand',
        'street',
        'postal_code',
        'city',
        'country',
        'phones',
        'lat',
        'lon',
        'code_totem',
        'work_days',
        'google_rating',
        'custom_date1',
        'custom_date2',
        'custom_date3',
        'custom_date4',
        'custom_boolean1',
        'custom_boolean2',
        'custom_text1',
        'custom_text2',
        'work_hours',
        'last_report_sent_at',
    ];

    protected array $rules = [
        'name' => 'string|required',
        'code_totem' => 'alpha_num',
        'street' => 'string',
        'street2' => 'string',
        'postal_code' => 'string',
        'city' => 'string',
        'country' => 'string',
        'brand' => 'string',
        'lat' => 'numeric',
        'lon' => 'numeric',
        'language_id' => 'integer|exists:language,id',
        'country_id' => 'integer|exists:country,id',
        'phones' => 'string',
        'work_days' => 'string',
        'work_hours' => 'string',
        'info' => 'string',
        'created_by' => 'integer|required',
        'google_place_id' => 'string',
        'google_rating' => 'integer',
        'custom_date1'       => 'date',
        'custom_date2'       => 'date',
        'custom_date3'       => 'date',
        'custom_date4'       => 'date',
        'custom_boolean1'    => 'boolean',
        'custom_boolean2'    => 'boolean',
        'custom_text1'       => 'string',
        'custom_text2'       => 'string',
        'monday' => 'boolean',
        'tuesday' => 'boolean',
        'wednesday' => 'boolean',
        'thursday' => 'boolean',
        'friday' => 'boolean',
        'saturday' => 'boolean',
        'sunday' => 'boolean',
        'monday_hours' => 'string',
        'tuesday_hours' => 'string',
        'wednesday_hours' => 'string',
        'thursday_hours' => 'string',
        'friday_hours' => 'string',
        'saturday_hours' => 'string',
        'sunday_hours' => 'string',
        'website_url' => 'string',
        'disabled' => 'boolean',
        'last_report_sent_at' => 'date',
        'smicer_info' => 'string',
        'private_info' => 'string'
    ];

    protected array $exportable = [
        'name',
        'street',
        'street2',
        'postal_code',
        'brand',
        'city',
        'country',
        'lat',
        'lon',
        'code_totem',
        'phones',
        'work_days',
        'work_hours',
        'info',
        'smicer_info',
        'private_info',
        'axes',
        'axe'
    ];

    public function creatingEvent(User $user, array $params = []): bool
    {
        self::created(function (self $shop) use ($user) {
            DB::table('shop_society')->insert([
                [
                    'society_id' => $user->current_society_id,
                    'shop_id' => $shop->id
                ]
            ]);
        });
    }

    public function updatingEvent(User $user, array $params = []): bool
    {
        self::updated(function (self $shop) use ($user) {
            //DB::table('shop_society')->insert([
            //    [
            //        'society_id' => $user->current_society_id,
            //        'shop_id' => $shop->id
            //    ]
            //]);
        });
    }

    public function save(array $options = []): bool
    {
        return parent::save($options);

        //if ($res) {
        //    $job = (new ShopGoogleRatingJob($this))->onQueue('rating');
        //    $this->dispatch($job);
        //}
    }

    public function societies(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\Society', 'shop_society');
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo('App\Models\Language');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\User', 'user_shop');
    }

    public function axes(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\Axe', 'shop_axe');
    }

    public function waves(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\Wave', 'wave_shop');
    }

    public function missions(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\Mission', 'mission');
    }

    public function shopContacts()
    {
        return $this->hasMany('App\Models\ShopContact');
    }

    public function shopCaches()
    {
        return $this->hasMany('App\Models\ShopCache');
    }

    public function todoistProject()
    {
        return $this->belongsTo('App\Models\TodoistProject', 'shop_id');
    }

    public function createdBy()
    {
        return $this->belongsTo('App\Models\User', 'created_by');
    }

    public function scopeRelations($query)
    {
        $query->with('axes', 'shopContacts');
    }

    public function scopeMinimum($query)
    {
        $query->select('id', 'name');
    }

    public static function getRestrictedshop($user_id, $society_id, $current_society_id, $no_pass = false)
    {
        $sid = [];
        //Recupérer tous les points de vente non attachés à un user
        if (($society_id == 1 || $society_id == 113) && $no_pass === false) {
            $shops = Shop::listQuery()->where('society_id', $current_society_id)->orderBy('name');
        } else {
            //Récupére tous les points de vente non attachés à un user
            //$shops_id = DB::table('show_userless_shops')->where('society_id', $current_society_id)->select('id');
            //foreach ($shops_id->get() as $id) {
            //    $sid[] = $id['id'];
            //}

            //Récuperer tous les points de vente attachés à un user
            $shops_id = DB::table('show_users_shops')->where('user_id', $user_id)->where('society_id', $current_society_id)->select('id');
            foreach ($shops_id->get() as $id) {
                $sid[] = $id['id'];
            }

            $shops_id = DB::table('show_shops_society')->whereIn('id', $sid)->where('society_id', $current_society_id)->select("id");;

            foreach ($shops_id->get() as $id) {
                $sid[] = $id['id'];
            }
            $shops = Shop::whereIn('id', $sid)->orderBy('name');
        }

        return $shops;
    }

    public static function getAxeshop($axe_id)
    {
        $ids = [];
        $shops = DB::table('shop_axe')->wherein('axe_id', $axe_id)->get();
        foreach ($shops as $shop) {
            $ids[] = $shop['shop_id'];
        }
        return $ids;
    }

    public static function getShopFromFilter($filters)
    {

        $shop = [];
        array_push($shop, self::getAxeshop($filters['axes']));
        array_push($shop, self::getAxeshop($filters['axes_as_filter']));
        array_push($shop, $filters['shop']);
        $i = 0;

        $intersect_shop_id = [];
        foreach ($shop as $res) {
            $res = array_flatten($res);
            if (count($res) > 0) {
                if ($i > 0) {
                    $intersect_shop_id = array_intersect($res, $intersect_shop_id);
                } else {
                    $intersect_shop_id = $res;
                }
            $i++;
            }
        }
        return $intersect_shop_id;
    }



    public static function getUsershop($shops_id)
    {
        $ids = [];
        $users = DB::table('user_shop')->wherein('shop_id', $shops_id)->get();
        foreach ($users as $user) {
            $ids[] = $user['user_id'];
        }
        return $ids;
    }
}
