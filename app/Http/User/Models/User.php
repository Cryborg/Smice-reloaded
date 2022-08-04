<?php

namespace App\Http\User\Models;

use App\Classes\Helpers\GeoHelper;
use App\Classes\Services\UserService;
use App\Classes\SmiceClasses\SmiceMailSystem;
use App\Exceptions\SmiceException;
use App\Http\Country\Models\Country;
use App\Http\Group\Models\Group;
use App\Http\Role\Models\Role;
use App\Http\Role\Permissions\Permissions;
use App\Http\Shop\Models\Shop;
use App\Http\Skill\Models\Skill;
use App\Jobs\UserProfileScoreJob;
use App\Models\Dashboard;
use App\Models\Gain;
use App\Models\Language;
use App\Models\Payment;
use App\Models\Program;
use App\Models\SmiceModel;
use App\Models\Society;
use App\Models\TodoistUser;
use App\Models\Voucher;
use App\Models\WaveTarget;
use App\Models\WaveUser;
use App\Traits\RequestToQueryable;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\Translatable\HasTranslations;

class User extends SmiceModel implements JWTSubject, Authenticatable
{
    use DispatchesJobs;
    use HasFactory;
    use HasTranslations;
    use Notifiable;
    use RequestToQueryable;
    use SoftDeletes;

    public const GENDER_MALE = 'male';
    public const GENDER_FEMALE = 'female';

    public const CANDIDATE_PROCESS_PROGRESS = 'progress';
    public const CANDIDATE_PROCESS_IN_REVIEW = 'in review';
    public const CANDIDATE_PROCESS_VALIDATED = 'validated';
    public const CANDIDATE_PROCESS_REFUSED = 'refused';

    protected $table = 'user';

    public $timestamps = true;

    protected string $list_table = 'show_users';

    protected $primaryKey = 'id';

    protected array $jsons = [
        'status_name',
        'groups'
    ];

    /**
     * The attributes that are not assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = [
        'api_key',
        'candidate_process',
        'deleted_at',
        'email_verified',
        'id',
        'secret_key',
        'updated_at',
        'zendesk_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'api_key',
        'password',
        'password_confirmation',
        'pivot',
        'secret_key',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    protected array $list_rows = [
        'city',
        'country',
        'email',
        'groups',
        'last_mission',
        'name',
        'phone',
        'postal_code',
        'registration',
        'roles',
        'score',
        'shops',
        'society',
        'status_id',
        'status_name',
        'validated_mission',
    ];

    protected array $exportable = [
        'birth_date',
        'city',
        'country_name',
        'email',
        'first_name',
        'gender',
        'id',
        'last_name',
        'lat',
        'lon',
        'password',
        'phone',
        'picture',
        'postal_code',
        'send_report_exclusion',
        'sleepstatus',
        'street',
    ];

    protected array $files = ['picture'];

    public static array $allowedIncludes = [
        'country',
        'groups',
        'roles',
        'shops',
        'society',
    ];

    public static array $allowedSorts = [];

    protected $with = [
        'country'
    ];

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier(): mixed
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims(): array
    {
        return [];
    }

    public static function getURI(): string
    {
        return 'users';
    }

    public static function getName(): string
    {
        return 'user';
    }

    public function getModuleName(): string
    {
        return 'users';
    }

    protected static function boot()
    {
        parent::boot();

        self::creating(function (self $user) {
            if (User::where('email', strtolower($user->email))->first()) {
                throw new SmiceException(
                    SmiceException::HTTP_BAD_REQUEST,
                    SmiceException::E_VARIABLE,
                    'Email already exists.'
                );
            }
            if (User::withTrashed()->where('email', strtolower($user->email))->first()) {
                throw new SmiceException(
                    SmiceException::HTTP_BAD_REQUEST,
                    SmiceException::E_VARIABLE,
                    'User already exists but has been deleted.'
                );
            }

            //check if user already exists
            $user->secret_key = Str::random(50);
            $user->api_key = Str::random(255);
            $user->current_society_id = $user->society_id;
            $user->registration_date = date('Y-m-d', time());
            $user->password = Hash::make($user->secret_key . $user->password);
            $user->password_confirmation = $user->password;
            $user->candidate_process = self::CANDIDATE_PROCESS_PROGRESS;
            if ($user->street && (!$user->lat || !$user->lon)) { // check avec les autres
                $coordinates = GeoHelper::getLatLonByStreet($user->street, $user->city);
                if ($coordinates['lat'] > 0) {
                    $user->lat = $coordinates['lat'];
                    $user->lon = $coordinates['lon'];
                }
            }
        });

        self::created(function (self $user) {
            $send_credential_info = request('send_credential_info', false);
            $password = request('password', false);
            if ($send_credential_info) {
                SmiceMailSystem::send(
                    SmiceMailSystem::NEW_ACCOUNT,
                    function (SmiceMailSystem $message) use ($user, $password) {
                        $message->to([$user->id]);
                        $message->subject('Bienvenue sur Smice');
                        $message->addMergeVars([
                            $user->id => [
                                'email' => $user->email,
                                'password' => $password,
                            ]
                        ]);
                    },
                    $user->language->code
                );
            }


            // Ajouter le rôle de smiceur
            $role = Role::where('id', 2)->first();
            if ($role) {
                $user->roles()->attach([$role->getKey()]);
            }
            // Si user est un smiceur, on ajoute une mission test
            if ($user->society_id == 1) {
                UserService::addMissionTest($user->id);
            }

            $user->reloadPermissions();

            $userActivity = new UserActivity();
            $userActivity->user_id = $user->getKey();
            $userActivity->save();
        });

        self::updating(function (self $user) {
            $user->email = strtolower($user->email);
            $password = $user->password;
            $original_password = $user->getOriginal('password');

            if ($password != $original_password && !is_null($original_password)) {
                $user->password = Hash::make($user->secret_key . $password);
                $user->password_confirmation = $user->password;
            }
        });
    }

    public function shops(): BelongsToMany
    {
        return $this->belongsToMany(Shop::class, 'user_shop');
    }

    public function skills(): BelongsToMany
    {
        return $this->belongsToMany(Skill::class, 'user_skill');
    }

    public function society(): BelongsTo
    {
        return $this->belongsTo(Society::class);
    }

    public function dashboards(): HasMany
    {
        return $this->hasMany(Dashboard::class);
    }

    public function currentSociety(): BelongsTo
    {
        return $this->belongsTo(Society::class, 'current_society_id');
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_user')->select('id', 'name');
    }

    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(Group::class, 'group_user');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by')->minimum();
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function userPermission(): HasOne
    {
        return $this->hasOne(UserPermission::class);
    }

    public function userActivity(): HasOne
    {
        return $this->hasOne(UserActivity::class);
    }

    public function userLogin(): HasOne
    {
        return $this->hasOne(UserLogin::class);
    }

    public function programs(): BelongsToMany
    {
        return $this->belongsToMany(Program::class, 'program_user');
    }

    public function exclusionSocieties(): BelongsToMany
    {
        return $this->belongsToMany(Society::class, 'user_exclusion_society');
    }

    public function exclusionShops(): BelongsToMany
    {
        return $this->belongsToMany(Shop::class, 'user_exclusion_shop');
    }

    public function waveUsers(): HasMany
    {
        return $this->hasMany(WaveUser::class);
    }

    public function targets(): BelongsToMany
    {
        return $this->belongsToMany(WaveTarget::class, 'wave_user', 'user_id', 'wave_target_id');
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function todoistUser(): HasOne
    {
        return $this->hasOne(TodoistUser::class);
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }

    public function gains(): HasMany
    {
        return $this->hasMany(Gain::class);
    }

    public function voucher(): HasMany
    {
        return $this->hasMany(Voucher::class);
    }

    public function scopeRelations($query)
    {
        $query->with(
            'society',
            'currentSociety',
            'country',
            'createdBy',
            'language',
            'roles',
            'exclusionSocieties',
            'exclusionShops',
            'userPermission',
            'userActivity.userLevel'
        );
    }

    public function scopeServer($query)
    {
        $query->with([
            'currentSociety',
            'language',
            'roles'
        ]);
    }

    public function scopeMinimum($query)
    {
        $query->selectRaw('id, CONCAT(first_name, \' \', last_name) as name');
    }

    public function synced($synced_model): bool
    {
        if ($synced_model === 'roles') {
            $this->userPermission->permissions = null;
            $this->userPermission->advanced_permissions = null;
            $this->userPermission->backoffice_menu_permissions = null;
            $this->userPermission->homeboard_permissions = null;
            $this->userPermission->save();
        }
    }

    /**
     * Cette fonction sert à recharger les permissions simples et avancées
     * de l'utilisateur. Elle est appelée lorsqu'un utilisateur se créer un compte,
     * lorsqu'il se connecte et lorsque qu'on charge le permissionChecker.
     */
    public function reloadPermissions()
    {
        $roles = $this->roles()->select('*')->get();
        $simple_permissions = Permissions::getSimplePermissions();
        $advanced_permissions = Permissions::getAdvancedPermissions();
        $backoffice_menu_permissions = Permissions::getBackofficeMenuPermissions();
        $homeboard_permissions = Permissions::getHomeboardPermissions();
        $this->isadmin = false;
        $userPermission = $this->userPermission ?? new UserPermission();
        $userPermission->user_id = $this->id;
        $userPermission->review_access = false;
        $userPermission->edit_survey = false;
        foreach ($roles as $role) {
            $simple_permissions = Permissions::mergePermissions($simple_permissions, $role->simple_permissions);
            $advanced_permissions = Permissions::mergePermissions($advanced_permissions, $role->advanced_permissions);
            if ($role->backoffice_access) {
                $this->isadmin = true;
                $backoffice_menu_permissions = Permissions::mergePermissions(
                    $backoffice_menu_permissions,
                    $role->backoffice_menu_permissions
                );
                $homeboard_permissions = Permissions::mergePermissions(
                    $homeboard_permissions,
                    $role->homeboard_permissions
                );
            }

            if ($role->review_access) {
                $userPermission->review_access = true;
            }

            if ($role->download_passage_proof) {
                $userPermission->download_passage_proof = true;
            }

            if ($role->import_update_user) {
                $userPermission->import_update_user = true;
            }
            if ($role->edit_survey) {
                $userPermission->edit_survey = true;
            }
        }

        $userPermission->permissions = $simple_permissions;
        $userPermission->advanced_permissions = $advanced_permissions;
        $userPermission->backoffice_menu_permissions = $backoffice_menu_permissions;
        $userPermission->homeboard_permissions = $homeboard_permissions;
        $userPermission->save();
    }


    /**
     * @deprecated
     * @note Useless method
     *
     * Fonction sert a determiner si le user courant est relecteur ou non
     *
     */
    public function isReader(): bool
    {
        if (0/*$this->userPermission->advanced_permissions['targets']['read'] == PermissionMode::SELF*/) {
            return true;
        }
        return false;
    }

    /**
     * Set the current_society_id to the value of the society_id.
     */
    public function reloadCurrentSociety()
    {
        if ($this->current_society_id != $this->society_id) {
            DB::table('user')->where('id', $this->getKey())->update(['current_society_id' => $this->society_id]);
            $this->setRelation('currentSociety', $this->society);
        }
    }

    public function saveScore($user_id)
    {
        $user = self::find($user_id);

        $job = (new UserProfileScoreJob($user))->onQueue('score');
        $this->dispatch($job);
    }

    protected function email(): Attribute
    {
        return Attribute::make(
            set: fn($value) => strtolower($value),
        );
    }

    /**
     * Get the name of the unique identifier for the user.
     *
     * @return string
     */
    public function getAuthIdentifierName(): string
    {
        return 'id';
    }

    /**
     * Get the unique identifier for the user.
     *
     * @return mixed
     */
    public function getAuthIdentifier(): mixed
    {
        return $this->attributes[$this->getAuthIdentifierName()];
    }

    /**
     * Get the password for the user.
     *
     * @return string
     */
    public function getAuthPassword(): string
    {
        return $this->attributes['password'];
    }

    /**
     * Get the "remember me" token value.
     *
     * @return string
     */
    public function getRememberToken(): string
    {
        return $this->attributes[$this->getRememberTokenName()];
    }

    /**
     * Set the "remember me" token value.
     *
     * @param string $value
     * @return void
     */
    public function setRememberToken($value): void
    {
        $this->attributes[$this->getRememberTokenName()] = $value;
    }

    /**
     * Get the column name for the "remember me" token.
     *
     * @return string
     */
    public function getRememberTokenName(): string
    {
        return 'remember_token';
    }

    public static function allowedFilters(): array
    {
        return [
            AllowedFilter::exact('id'),

            AllowedFilter::scope('roles'),
            AllowedFilter::exact('roles.id'),

            AllowedFilter::scope('groups'),
            AllowedFilter::exact('groups.id'),

            AllowedFilter::scope('shops'),
            AllowedFilter::exact('shops.id'),
        ];
    }
}

