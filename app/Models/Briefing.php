<?php

namespace App\Models;

use App\Interfaces\iProtected;
use App\Interfaces\iREST;

/**
 * App\Models\Briefing
 *
 * @property int $id
 * @property string $name
 * @property string|null $document
 * @property int $society_id
 * @property int|null $created_by
 * @property-read \App\Models\Society $society
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel addPublicResources()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Briefing relations()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieve()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieveAll()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Briefing whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Briefing whereDocument($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Briefing whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Briefing whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Briefing whereSocietyId($value)
 * @property-read \App\Models\User|null $createdBy
 * @mixin \Eloquent
 */
class Briefing extends SmiceModel implements iREST, iProtected
{
    protected $table                = 'briefing';

    protected $primarykey           = 'id';

    public $timestamps              = false;

    protected $fillable             = [
        'name',
        'document',
        'society_id',
        'created_by'
    ];

    protected $hidden               = [];

    protected $list_rows            = [
        'name'
    ];

    protected $rules                = [
        'name'          => 'required|string|unique_with:briefing,society_id,{id}',
        'document'      => 'string',
        'society_id'    => 'required|integer',
        'created_by'    => 'required|integer'
    ];

    protected $files    = [
        'document'
    ];

    public static function getURI()
    {
        return 'briefings';
    }

    public static function getName()
    {
        return 'briefing';
    }

    public function getModuleName()
    {
        return 'briefings';
    }
    
    public function society()
    {
        return $this->belongsTo('App\Models\Society');
    }

    public function createdBy()
    {
        return$this->belongsTo('App\Models\User', 'created_by');
    }

   
}