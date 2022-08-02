<?php

namespace App\Models;

use App\Http\User\Models\User;
use App\Interfaces\iProtected;
use App\Interfaces\iREST;
use App\Interfaces\iTranslatable;

class Group extends SmiceModel implements iREST, iProtected, iTranslatable
{
    protected $table = 'group';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected array $jsons = [
        'name',
    ];

    protected array $translatables = [
        'name',
    ];

    protected $fillable = [
        'name',
        'society_id',
        'created_by',
    ];

    protected $hidden = [
        'society_id',
        'created_by',
    ];

    protected array $list_rows = [
        'name',
    ];

    protected array $rules = [
        'society_id' => 'integer|required',
        'name' => 'array|required',
        'created_by' => 'integer|required',
    ];

    public static function getURI()
    {
        return 'groups';
    }

    public static function getName()
    {
        return 'group';
    }

    public function getModuleName()
    {
        return 'groups';
    }

    public function society()
    {
        return $this->belongsTo(Society::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'group_user');
    }
}
