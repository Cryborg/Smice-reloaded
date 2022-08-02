<?php

namespace App\Models;

use App\Interfaces\iProtected;
use App\Interfaces\iREST;
use App\Interfaces\iTranslatable;

/**
 * App\Models\Checklist
 *
 * @property int $id
 * @property mixed $name
 * @property int $society_id
 * @property int $created_by
 * @property-read \App\Models\Society $society
 * @property-read \App\Models\User $createdBy
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel addPublicResources()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel relations()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieve()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieveAll()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Checklist whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Checklist whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Checklist whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Checklist whereSocietyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Checklist ordered()
 * @mixin \Eloquent
 */
class Checklist extends SmiceModel implements iREST, iProtected, iTranslatable
{
    protected $table                = 'checklist';

    protected $primarykey           = 'id';

    public $timestamps              = false;

    protected $jsons = [
        'name'
    ];

    protected array $translatables    = [
        'name'
    ];

    protected array $list_rows        = [
        'name',
        'society_id',
        'created_by'
    ];

    protected $fillable         = [
        'name',
        'society_id',
        'created_by'
    ];

    protected $hidden           = [
        'society_id',
        'created_by'
    ];

    protected array $rules                = [
        'name'          => 'required|string|unique_with:briefing,society_id,{id}',
        'society_id'    => 'required|integer',
        'created_by'    => 'required|integer'
    ];

    public function scopeOrdered($query)
    {
        return $query->orderBy('name', 'asc')->get();
    }

    public static function getURI()
    {
        return 'checklists';
    }

    public static function getName()
    {
        return 'checklist';
    }

    public function getModuleName()
    {
        return 'checklists';
    }

    public function society()
    {
        return $this->belongsTo('App\Models\Society');
    }

    public function createdBy()
    {
        return $this->belongsTo('App\Models\User', 'created_by');
    }
}
