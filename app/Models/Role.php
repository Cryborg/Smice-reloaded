<?php

namespace App\Models;

use App\Classes\Permissions\Permissions;
use App\Http\User\Models\User;
use App\Interfaces\iProtected;
use App\Interfaces\iPublicable;
use App\Interfaces\iREST;
use App\Traits\HasCreatedBy;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\DB;

class Role extends SmiceModel implements iREST, iProtected, iPublicable
{
    use HasCreatedBy;

    protected $table = 'role';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected array $jsons = [
        'simple_permissions',
        'advanced_permissions',
        'backoffice_menu_permissions',
        'homeboard_permissions'
    ];

    protected $fillable = [
        'society_id',
        'name',
        'description',
        'created_by',
        'public',
        'backoffice_access',
        'field_officer',
        'review_access',
        'simple_permissions',
        'advanced_permissions',
        'backoffice_menu_permissions',
        'homeboard_permissions',
        'download_passage_proof',
        'import_update_user',
        'edit_survey'
    ];

    protected $hidden = [
        'pivot',
        'society_id',
        'created_by'
    ];

    protected array $list_rows = [
        'name',
        'public',
        'backoffice_access',
        'field_officer',
        'review_access',
        'description',
        'download_passage_proof',
        'import_update_user',
        'edit_survey'
    ];

    protected array $rules = [
        'society_id' => 'required|exists:society,id',
        'public' => 'boolean',
        'backoffice_access' => 'boolean',
        'field_officer' => 'boolean',
        'review_access' => 'boolean',
        'name' => 'required|string|unique_with:role,society_id,{id}',
        'description' => 'string',
        'created_by' => 'integer',
        'simple_permissions' => 'array',
        'advanced_permissions' => 'array',
        'backoffice_menu_permissions' => 'array',
        'homeboard_permissions' => 'array',
        'download_passage_proof' => 'boolean',
        'import_update_user' => 'boolean',
        'edit_survey' => 'boolean'
    ];

    public static function getURI(): string
    {
        return 'roles';
    }

    public static function getName(): string
    {
        return 'role';
    }

    public function getModuleName(): string
    {
        return 'roles';
    }

    public static function boot()
    {
        parent::boot();

        self::creating(function (self $role) {
            $role->simple_permissions = Permissions::getSimplePermissions();
            $role->advanced_permissions = Permissions::generateAdvancedPermissions($role->simple_permissions);
            $role->backoffice_menu_permissions = Permissions::getBackofficeMenuPermissions();
            $role->homeboard_permissions = Permissions::getHomeboardPermissions();
        });

        self::updating(function (self $role) {
            //$test = Permissions::generateAdvancedPermissions($role->getAttribute('simple_permissions'));
            //dd($test);
            if (!$role->advanced_mode_only &&
                !Permissions::areDifferent(
                    $role->getAttribute('advanced_permissions'),
                    json_decode($role->getOriginal('advanced_permissions'), true)
                )
            ) {
                $role->advanced_permissions = Permissions::generateAdvancedPermissions($role->simple_permissions);
            } else {
                $role->advanced_mode_only = true;
            }

            $role->_deleteJsonPermissions();
        });

        self::deleting(function (self $role) {
            $role->_deleteJsonPermissions();
        });
    }

    public function society(): BelongsTo
    {
        return $this->belongsTo(Society::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'role_user');
    }

    /**
     * Si l'utilisateur n'est pas connecté en tant que QCM,
     * on supprime les champs "public" et "advanced_permissions"
     *
     * @param User $user
     * @param array $params
     * @return bool
     */
    public function creatingEvent(User $user, array $params = []): bool
    {
        if (!$user->society->is_qcm) {
            unset($this->attributes['public']);
            unset($this->attributes['advanced_permissions']);
        }

        return true;
    }

    public function updatingEvent(User $user, array $params = []): bool
    {
        if (!$user->society->is_qcm) {
            unset($this->attributes['public']);
            //unset($this->attributes['advanced_permissions']);
        }

        return true;
    }

    private function _deleteJsonPermissions()
    {
        DB::statement(
            'UPDATE "user_permission"
          SET permissions = null, advanced_permissions = null, backoffice_menu_permissions = null, homeboard_permissions = null
          WHERE user_id IN (SELECT user_id FROM role_user WHERE role_id = ' . $this->getKey() . ')'
        );
    }
}
