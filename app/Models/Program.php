<?php

namespace App\Models;

use App\Interfaces\iProtected;
use App\Interfaces\iREST;
use App\Interfaces\iTranslatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;


/**
 * App\Models\Program
 *
 * @property int $id
 * @property mixed $name
 * @property int $society_id
 * @property int|null $created_by
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property bool $anonymous_mode
 * @property string|null $deleted_at
 * @property bool $autocheck_na
 * @property bool $blurFaces
 * @property int|null $order
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Society[] $manySociety
 * @property-read \App\Models\Society $society
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\User[] $users
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Wave[] $waves
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel addPublicResources()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Program minimum()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel relations()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieve()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieveAll()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Program whereAnonymousMode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Program whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Program whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Program whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Program whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Program whereSocietyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Program whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Program whereAutocheckNa($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Program whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Program whereOrder($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Program withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Program withoutTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Program onlyTrashed()
 * @method static bool|null forceDelete()
 * @method static bool|null restore()
 * @mixin \Eloquent
 */
class Program extends SmiceModel implements iREST, iProtected, iTranslatable
{
    protected $table                = 'program';

    use SoftDeletes;

    protected $primaryKey           = 'id';

    public $timestamps              = true;

    protected $jsons = [
        'name'
    ];

    protected array $translatable = [
        'name'
    ];

    protected $fillable             = [
        'name',
        'society_id',
        'anonymous_mode',
        'autocheck_na',
        'created_by',
        'blurFaces',
        'parent_id',
        'order',
    ];

    protected $hidden               = [
        'pivot',
        'created_by',
    ];

    protected array $rules                = [
        'name'              => 'array|required', // 'string|required|unique_with:program,society_id,{id}'
        'society_id'        => 'integer|required',
        'created_by'        => 'integer|required',
        'anonymous_mode'    => 'boolean|required',
        'order'             => 'integer|required|min:0',
    ];

    protected array $list_rows            = [
        'name',
        'society_id',
        'anonymous_mode',
        'order'
    ];

    protected array $exportable      = [
        'name'
    ];

    public static function getURI()
    {
        return 'programs';
    }

    public static function getName()
    {
        return 'program';
    }

    public function getModuleName()
    {
        return 'programs';
    }

    public function society()
    {
        return $this->belongsTo('App\Models\Society');
    }

    public function manySociety()
    {
        return $this->belongsToMany('App\models\Society');
    }

    public function users()
    {
        return $this->belongsToMany('App\Models\User', 'program_user');
    }

    public function group_user()
    {
        return $this->belongsToMany('App\Http\Group\Models\Group', 'program_group_user');
    }

    public function parent()
    {
        return $this->belongsTo('App\Models\Program', 'parent_id');
    }

    public function children()
    {
        return $this->hasMany('App\Models\Program', 'parent_id', 'id')->with('children')->orderBy('order');
    }

    public function scopeMinimum($query)
    {
        return $query->select('id', 'name');
    }

    public function scopeRelations($query)
    {
        return $query->with(
            'group_user',
            'parent'
        );
    }

    public static function getRestrictedprogram($user_id, $society_id, $current_society_id)
    {
        $sid = $pid = [];
        //Recupérer tous si uses est user smice
        if ($society_id == 1) {
            $programs = Program::where('society_id', $current_society_id)->orderBy('order');
        } else {
            //Récupére les groupes de l'utilisateur courant
            $groups_in_user = DB::table('group_user')->where('user_id', $user_id)->get(['group_id']);
            foreach ($groups_in_user as $group_id) {
                $groups[] = $group_id['group_id'];
            }
            if (isset($groups)) {
                //Récuperer tous programs liés aux groups
                $programs = DB::table('program_group_user')->whereIn('group_id', $groups)->get(['program_id']);
                foreach ($programs as $p) {
                    $pid[] = $p['program_id'];
                }
            }
            //Récuperer les programmes qui n'ont pas de restriction
            $pidu = [];
            $programs = DB::table('program_group_user')->get(['program_id']);
            foreach ($programs as $p) {
                $pidu[] = $p['program_id'];
            }

            $programs = Program::where('society_id', $current_society_id)->whereNotIn('id', $pidu)->get(['id']);
            foreach ($programs as $p) {
                $pid[] = $p['id'];
            }

            $programs = Program::where('society_id', $current_society_id)->whereIn('id', $pid)->orderBy('order');
        }

        return $programs;
    }
}
