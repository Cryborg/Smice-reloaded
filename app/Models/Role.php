<?php

namespace App\Models;

use App\Classes\Permissions\Permissions;
use App\Interfaces\iProtected;
use App\Interfaces\iPublicable;
use App\Interfaces\iREST;
use Illuminate\Support\Facades\DB;

/**
 * App\Models\Role
 *
 * @property int $id
 * @property string $name
 * @property bool $public
 * @property bool $backoffice_access
 * @property bool $field_officer
 * @property bool $advanced_mode_only
 * @property mixed $simple_permissions
 * @property mixed $advanced_permissions
 * @property mixed $backoffice_menu_permissions
 * @property int $society_id
 * @property int $created_by
 * @property string|null $description
 * @property bool $review_access
 * @property mixed $homeboard_permissions
 * @property bool $download_passage_proof
 * @property bool $import_update_user
 * @property-read \App\Models\Society $society
 * @property-read \App\Models\User $createdBy
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\User[] $users
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel addPublicResources()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel relations()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieve()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieveAll()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Role whereAdvancedModeOnly($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Role whereAdvancedPermissions($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Role whereBackofficeAccess($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Role whereBackofficeMenuPermissions($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Role whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Role whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Role whereFieldOfficer($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Role whereHomeboardPermissions($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Role whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Role whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Role wherePublic($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Role whereReviewAccess($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Role whereSimplePermissions($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Role whereSocietyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Role whereDownloadPassageProof($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Role whereImportUpdateUser($value)
 * @mixin \Eloquent
 */
class Role extends SmiceModel implements iREST, iProtected, iPublicable
{
    protected $table                = 'role';

    protected $primaryKey           = 'id';

    public $timestamps              = false;

    protected $jsons                = [
        'simple_permissions',
        'advanced_permissions',
        'backoffice_menu_permissions',
        'homeboard_permissions'
    ];

    protected $fillable             = [
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

    protected $hidden               = [
        'pivot',
        'society_id',
        'created_by'
    ];

    protected $list_rows            = [
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

    protected $rules                = [
        'society_id'                    => 'required|integer',
        'public'                        => 'boolean',
        'backoffice_access'             => 'boolean',
        'field_officer'                 => 'boolean',
        'review_access'                 => 'boolean',
        'name'                          => 'required|string|unique_with:role,society_id,{id}',
        'description'                   => 'string',
        'created_by'                    => 'integer',
        'simple_permissions'            => 'array',
        'advanced_permissions'          => 'array',
        'backoffice_menu_permissions'   => 'array',
        'homeboard_permissions'         => 'array',
        'download_passage_proof'        => 'boolean',
        'import_update_user'            => 'boolean',
        'edit_survey'                   => 'boolean'
    ];

    public static function getURI()
    {
        return 'roles';
    }

    public static function getName()
    {
        return 'role';
    }

    public function getModuleName()
    {
        return 'roles';
    }

    static function boot()
    {
        parent::boot();

        self::creating(function(self $role)
        {
            $role->simple_permissions = Permissions::getSimplePermissions();
            $role->advanced_permissions = Permissions::generateAdvancedPermissions($role->simple_permissions);
            $role->backoffice_menu_permissions = Permissions::getBackofficeMenuPermissions();
            $role->homeboard_permissions = Permissions::getHomeboardPermissions();
        });

        self::updating(function(self $role)
        {
            //$test = Permissions::generateAdvancedPermissions($role->getAttribute('simple_permissions'));
            //dd($test);
            if (!$role->advanced_mode_only &&
                !Permissions::areDifferent(
                    $role->getAttribute('advanced_permissions'),
                    json_decode($role->getOriginal('advanced_permissions'), true))
            ){
                $role->advanced_permissions = Permissions::generateAdvancedPermissions($role->simple_permissions);
            } else {
                $role->advanced_mode_only = true;
            }

            $role->_deleteJsonPermissions();
        });

        self::deleting(function(self $role)
        {
            $role->_deleteJsonPermissions();
        });
    }

    public function society()
    {
        return $this->belongsTo('App\Models\Society');
    }

    public function createdBy()
    {
        return$this->belongsTo('App\Models\User', 'created_by');
    }

    public function users()
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
    public function     creatingEvent(User $user, array $params = [])
    {
        if (!$user->society->is_qcm) {
            unset($this->attributes['public']);
            unset($this->attributes['advanced_permissions']);
        }

        return true;
    }

    public function     updatingEvent(User $user, array $params = [])
    {
        if (!$user->society->is_qcm) {
            unset($this->attributes['public']);
            //unset($this->attributes['advanced_permissions']);
        }

        return true;
    }

    private function    _deleteJsonPermissions()
    {
        DB::statement('UPDATE "user_permission"
          SET permissions = null, advanced_permissions = null, backoffice_menu_permissions = null, homeboard_permissions = null
          WHERE user_id IN (SELECT user_id FROM role_user WHERE role_id = '. $this->getKey() . ')'
        );
    }
}