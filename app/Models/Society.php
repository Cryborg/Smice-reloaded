<?php

namespace App\Models;

use App\Exceptions\SmiceException;
use App\Interfaces\iProtected;
use App\Interfaces\iREST;
use Illuminate\Support\Facades\DB;
use Artisan;


/**
 * App\Models\Society
 *
 * @property int $id
 * @property string $name
 * @property string|null $email
 * @property string|null $street
 * @property string|null $street2
 * @property string|null $postal_code
 * @property string|null $city
 * @property string|null $country_name
 * @property float|null $lat
 * @property float|null $lon
 * @property string|null $logo
 * @property int|null $created_by
 * @property int|null $society_id
 * @property int|null $country_id
 * @property int|null $language_id
 * @property string|null $code_totem
 * @property bool $show_percent
 * @property int|null $source_id
 * @property string|null $sub_domain
 * @property bool $is_qcm
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property bool $retrieve_google_reviews
 * @property string|null $last_refresh_data
 * @property string|null $zoho_id_client
 * @property string|null $zoho_secret
 * @property-read \App\Models\Society|null $society
 * @property-read \App\Models\User|null $createdBy
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Alias[] $alias
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Dashboard[] $dashboards
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\AxeDirectory[] $directories
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Group[] $groups
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Program[] $programs
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Question[] $questions
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Role[] $roles
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Scenario[] $scenarios
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Sequence[] $sequences
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Shop[] $shops
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\MailTemplate[] $templates
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\User[] $users
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Wave[] $waves
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel addPublicResources()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Society minimum()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel relations()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieve()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieveAll()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Society whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Society whereCodeTotem($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Society whereCountryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Society whereCountryName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Society whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Society whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Society whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Society whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Society whereIsQcm($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Society whereLanguageId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Society whereLat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Society whereLogo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Society whereLon($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Society whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Society wherePostalCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Society whereShowPercent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Society whereSocietyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Society whereSourceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Society whereStreet($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Society whereStreet2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Society whereSubDomain($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Society whereTodoistApiKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Society whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Society whereRetrieveGoogleReviews($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Society whereLastRefreshData($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Society whereZohoIdClient($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Society whereZohoSecret($value)
 * @mixin \Eloquent
 */
class Society extends SmiceModel implements iREST, iProtected
{
    protected $table                = 'society';

    protected $primaryKey           = 'id';

    /**
     * The id of the children societies.
     * @var array
     */
    private ?array $children_id            = null;

    protected $fillable             = [
        'name',
        'email',
        'street',
        'street2',
        'postal_code',
        'city',
        'country_name',
        'lat',
        'lon',
        'country_id',
        'society_id',
        'language_id',
        'logo',
        'created_by',
        'retrieve_google_reviews',
        'last_refresh_data',
        'blogurl'
    ];

    protected $hidden               = [
        'sub_domain',
        'pivot',
        'is_qcm',
        'created_by',
        'zoho_id_client',
        'zoho_secret',
        'todoist_api_key',
    ];

    protected array $list_rows            = [
        'id',
        'name',
        'street',
        'postal_code',
        'city',
        'country_name',
        'logo'
    ];

    protected array $rules                = [
        'name'            => 'string|required|unique:society,name,{id}',
        'email'           => 'email',
        'street'          => 'string',
        'street2'         => 'string',
        'postal_code'     => 'string',
        'city'            => 'string',
        'country_name'    => 'string',
        'lat'             => 'numeric',
        'lon'             => 'numeric',
        'logo'            => 'string',
        'url'             => 'string|unique:society,url,{id}',
        'society_id'      => 'integer',
        'language_id'     => 'integer|exists:language,id',
        'country_id'      => 'integer|exists:country,id',
        'created_by'      => 'integer',
        'zoho_id_client'  => 'string',
        'zoho_secret'     => 'string',
        'retrieve_google_reviews' => 'boolean',
        'last_refresh_data' => 'date'
    ];

    protected array $exportable     = [
        'id',
        'name',
        'email',
        'street',
        'street2',
        'postal_code',
        'city',
        'country_name',
        'lat',
        'lon',
        'logo',
    ];

    protected array $files               = [
        'logo'
    ];

    protected static function boot()
    {
        parent::boot();

        self::deleting(function($society)
        {
            if ($society->is_qcm)
            {
                throw new SmiceException(
                    SmiceException::HTTP_INTERNAL_SERVER_ERROR,
                    SmiceException::E_RESOURCE,
                    'The administration company can not be deleted.'
                );
            }
        });


        self::created(function (self $society) {

            //Prepare view for dashboard
            Artisan::call('society:createview', ['societyId' => $society->id]);

        });
    }

    public static function getURI()
    {
        return 'societies';
    }

    public static function getName()
    {
        return 'society';
    }

    public function getModuleName()
    {
        return 'societies';
    }

    public function society()
    {
        return $this->belongsTo('App\Models\Society', 'society_id');
    }

    public function users()
    {
        return $this->hasMany('App\Models\User');
    }

    public function dashboards()
    {
        return $this->hasMany('App\Models\Dashboard');
    }

    public function directories()
    {
        return $this->hasMany('App\Models\AxeDirectory');
    }

    public function programs()
    {
        return $this->hasMany('App\Models\Program');
    }

    public function shops()
    {
        return $this->belongsToMany(Shop::class, 'shop_society');
    }

    public function groups()
    {
        return $this->hasMany('App\Models\Group');
    }

    public function templates()
    {
        return $this->hasMany('App\Models\MailTemplate');
    }

    public function scenarios()
    {
        return $this->hasMany('App\Models\Scenario');
    }

    public function waves()
    {
        return $this->hasMany('App\Models\Wave');
    }

    public function roles()
    {
        return $this->hasMany('App\Models\Role');
    }

    public function questions()
    {
        return $this->hasMany('App\Models\Question');
    }

    public function createdBy()
    {
        return $this->belongsTo('App\Models\User', 'created_by');
    }

    public function sequences()
    {
        return $this->hasMany('App\Models\Sequence');
    }

    public function alias()
    {
        return $this->hasMany('App\Models\Alias');
    }

    public function scopeMinimum($query)
    {
        $query->select('id', 'name');
    }

    /**
     * Returns the children id.
     * @return array
     */
    public function    getChildrenId()
    {
        return $this->children_id;
    }

    /**
     * Load the list of id of all the children societies.
     * @return array
     */
    public function     loadChildrenId()
    {
        if ($this->children_id === null && $this->getKey())
        {
            $result = DB::select('SELECT get_society_children('. $this->getKey() .')');

            $this->children_id = array_values(array_flatten($result));
        }

        return $this->children_id;
    }
}
