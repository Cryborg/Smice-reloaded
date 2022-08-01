<?php

namespace App\Http\Role\Models;

use App\Classes\Permissions\Permissions;
use App\Http\User\Models\User;
use App\Interfaces\iProtected;
use App\Interfaces\iPublicable;
use App\Interfaces\iREST;
use App\Models\SmiceModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\DB;

class Role extends SmiceModel implements iREST, iProtected, iPublicable
{
    protected $table = 'role';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected array $jsons = [
        'advanced_permissions',
        'backoffice_menu_permissions',
        'homeboard_permissions',
        'simple_permissions',
    ];

    protected $fillable = [
        'advanced_permissions',
        'backoffice_access',
        'backoffice_menu_permissions',
        'created_by',
        'description',
        'download_passage_proof',
        'edit_survey',
        'field_officer',
        'homeboard_permissions',
        'import_update_user',
        'name',
        'public',
        'review_access',
        'simple_permissions',
        'society_id',
    ];

    protected $hidden = [
        'pivot',
        'society_id',
        'created_by'
    ];

    protected array $list_rows = [
        'backoffice_access',
        'description',
        'download_passage_proof',
        'edit_survey',
        'field_officer',
        'import_update_user',
        'name',
        'public',
        'review_access',
    ];

    protected array $rules = [
        'advanced_permissions' => 'array',
        'backoffice_access' => 'boolean',
        'backoffice_menu_permissions' => 'array',
        'created_by' => 'integer',
        'description' => 'string',
        'download_passage_proof' => 'boolean',
        'edit_survey' => 'boolean',
        'field_officer' => 'boolean',
        'homeboard_permissions' => 'array',
        'import_update_user' => 'boolean',
        'name' => 'required|string|unique_with:role,society_id,{id}',
        'public' => 'boolean',
        'review_access' => 'boolean',
        'simple_permissions' => 'array',
        'society_id' => 'required|integer',
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
        return $this->belongsTo('App\Models\Society');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo('App\Models\User', 'created_by');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\User', 'role_user');
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
