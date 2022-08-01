<?php

namespace App\Http\User\Models;

use App\Classes\Helpers\GeoHelper;
use App\Classes\Permissions\Permissions;
use App\Classes\Services\UserService;
use App\Classes\SmiceClasses\SmiceMailSystem;
use App\Exceptions\SmiceException;
use App\Http\Role\Models\Role;
use App\Jobs\UserProfileScoreJob;
use App\Models\SmiceModel;
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
use Spatie\Translatable\HasTranslations;

class User extends SmiceModel implements JWTSubject
{
    use HasFactory;
    use Notifiable;
    use DispatchesJobs;
    use SoftDeletes;
    use HasTranslations;

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

    public static function getURI()
    {
        return 'users';
    }

    public static function getName()
    {
        return 'user';
    }

    public function getModuleName()
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

    public function shops()
    {
        return $this->belongsToMany('App\Models\Shop', 'user_shop');
    }

    public function skills()
    {
        return $this->belongsToMany('App\Models\Skill', 'user_skill');
    }

    public function society()
    {
        return $this->belongsTo('App\Models\Society');
    }

    public function dashboards()
    {
        return $this->hasMany('App\Models\Dashboard');
    }

    public function currentSociety()
    {
        return $this->belongsTo('App\Models\Society', 'current_society_id');
    }

    public function roles()
    {
        return $this->belongsToMany('App\Models\Role', 'role_user')->select('id', 'name');
    }

    public function groups()
    {
        return $this->belongsToMany('App\Models\Group', 'group_user')->select('group.id', 'group.name');
    }

    public function createdBy()
    {
        return $this->belongsTo('App\Http\User\Models\User', 'created_by')->minimum();
    }

    public function payments()
    {
        return $this->hasMany('App\Models\Payment');
    }

    public function userPermission(): HasOne
    {
        return $this->hasOne('App\Models\UserPermission');
    }

    public function userActivity(): HasOne
    {
        return $this->hasOne('App\Http\User\Models\UserActivity');
    }

    public function userLogin(): HasOne
    {
        return $this->hasOne('App\Http\User\Models\UserLogin');
    }

    public function programs(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\Program', 'program_user');
    }

    public function exclusionSocieties(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\Society', 'user_exclusion_society');
    }

    public function exclusionShops(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\Shop', 'user_exclusion_shop');
    }

    public function waveUsers(): HasMany
    {
        return $this->hasMany('App\Models\WaveUser');
    }

    public function targets(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\WaveTarget', 'wave_user', 'user_id', 'wave_target_id');
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo('App\Models\Country');
    }

    public function todoistUser(): HasOne
    {
        return $this->hasOne('App\Models\TodoistUser');
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo('App\Models\Language');
    }

    public function gains(): HasMany
    {
        return $this->hasMany('App\Models\Gain');
    }

    public function voucher(): HasMany
    {
        return $this->hasMany('App\Models\Voucher');
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
            set: fn ($value) => strtolower($value),
        );
    }
}
