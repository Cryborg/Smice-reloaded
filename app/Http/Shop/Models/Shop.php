<?php

namespace App\Http\Shop\Models;

use App\Http\User\Models\User;
use App\Interfaces\iProtected;
use App\Interfaces\iREST;
use App\Models\Axe;
use App\Models\Language;
use App\Models\Mission;
use App\Models\ShopCache;
use App\Models\ShopContact;
use App\Models\SmiceModel;
use App\Models\Society;
use App\Models\TodoistProject;
use App\Models\Wave;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Support\Facades\DB;

use function App\Classes\Results\array_flatten;

class Shop extends SmiceModel implements iREST, iProtected
{
    use DispatchesJobs;

    public const DISABLED_YES = true;
    public const DISABLED_NO = false;

    protected $table = 'shop';

    protected $primaryKey = 'id';

    protected string $list_table = 'show_shops';

    public static function getURI(): string
    {
        return 'shops';
    }

    public static function getName(): string
    {
        return 'shop';
    }

    public function getModuleName(): string
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
        return $this->belongsToMany(Society::class, 'shop_society');
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_shop');
    }

    public function axes(): BelongsToMany
    {
        return $this->belongsToMany(Axe::class, 'shop_axe');
    }

    public function waves(): BelongsToMany
    {
        return $this->belongsToMany(Wave::class, 'wave_shop');
    }

    public function missions(): BelongsToMany
    {
        return $this->belongsToMany(Mission::class, 'mission');
    }

    public function shopContacts()
    {
        return $this->hasMany(ShopContact::class);
    }

    public function shopCaches()
    {
        return $this->hasMany(ShopCache::class);
    }

    public function todoistProject()
    {
        return $this->belongsTo(TodoistProject::class, 'shop_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
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

    public static function getAxeshop($axe_id): array
    {
        $ids = [];
        $shops = DB::table('shop_axe')->wherein('axe_id', $axe_id)->get();
        foreach ($shops as $shop) {
            $ids[] = $shop['shop_id'];
        }
        return $ids;
    }

    public static function getShopFromFilter($filters): array
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



    public static function getUsershop($shops_id): array
    {
        $ids = [];
        $users = DB::table('user_shop')->wherein('shop_id', $shops_id)->get();
        foreach ($users as $user) {
            $ids[] = $user['user_id'];
        }
        return $ids;
    }
}
