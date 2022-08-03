<?php

namespace App\Http\Skill\Models;

use App\Http\User\Models\User;
use App\Interfaces\iProtected;
use App\Interfaces\iREST;
use App\Interfaces\iTranslatable;
use App\Models\SmiceModel;

class Skill extends SmiceModel implements iREST, iProtected, iTranslatable
{
    protected $table = 'skill';

    protected $primaryKey = 'id';

    protected array $jsons = [
        'name',
        'description'
    ];

    protected array $translatables = [
        'name',
        'description'
    ];

    protected $fillable = [
        'name',
        'description',
        'visible',
        'society_id',
        'created_by',
    ];

    protected $hidden = [
        'society_id',
        'created_by'
    ];

    protected array $list_rows = [
        'name',
        'description',
        'visible',
    ];

    public static function getURI()
    {
        return 'skills';
    }

    public static function getName()
    {
        return 'skill';
    }

    public function getModuleName()
    {
        return 'skills';
    }

    public function society()
    {
        return $this->belongsTo('App\Models\Society');
    }

    public function users()
    {
        return $this->belongsToMany('App\Models\User', 'user_skill');
    }

    public function createdBy()
    {
        return $this->belongsTo('App\Models\User', 'created_by');
    }
}
