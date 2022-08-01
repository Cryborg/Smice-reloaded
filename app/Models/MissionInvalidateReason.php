<?php

namespace App\Models;

use App\Interfaces\iProtected;
use App\Interfaces\iREST;
use App\Interfaces\iTranslatable;

/**
 * App\Models\MissionInvalidateReason
 *
 * @property int $id
 * @property mixed|null $name
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property string|null $deleted_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel addPublicResources()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel relations()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieve()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieveAll()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MissionInvalidateReason whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MissionInvalidateReason whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MissionInvalidateReason whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MissionInvalidateReason whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MissionInvalidateReason whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class MissionInvalidateReason extends SmiceModel implements iREST, iProtected
{
    protected $table = 'mission_invalidate_reasons';

    protected $primaryKey = 'id';

    public $timestamps = true;

    protected $fillable = [
        'name',
        'created_at',
        'updated_at',
    ];

    protected $jsons = [
        'name',
    ];

    public static function getURI()
    {
        return 'mission_invalidate_reasons';
    }

    public static function getName()
    {
        return 'mission_invalidate_reasons';
    }

    public function getModuleName()
    {
        return 'mission_invalidate_reasons';
    }


}