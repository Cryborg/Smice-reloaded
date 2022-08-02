<?php

namespace App\Models;

use App\Interfaces\iREST;


/**
 * App\Models\ActionplanHistory
 *
 * @property int $id
 * @property int $actionplan_id
 * @property string $action
 * @property int $created_by
 * @property string $created_at
 * @property mixed|null $snapshot
 * @property-read \App\Models\User $createdBy
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel addPublicResources()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel relations()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieve()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieveAll()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ActionplanHistory whereAction($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ActionplanHistory whereActionplanId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ActionplanHistory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ActionplanHistory whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ActionplanHistory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ActionplanHistory whereSnapshot($value)
 * @mixin \Eloquent
 */
class ActionplanHistory extends SmiceModel implements iREST
{
    protected $table = 'actionplan_history';

    protected $primaryKey = 'id';

    protected $jsons            = ['snapshot'];

    public $timestamps = false;

    protected $fillable = [
        'action',
        'actionplan_id',
        'text',
        'created_by',
        'created_at',
        'snapshot',
    ];

    protected $hidden = [
    ];

    protected array $list_rows = [];

    protected array $rules = [
    ];

    public static function getURI()
    {
        return 'actionplan-history';
    }

    public static function getName()
    {
        return 'actionplan_history';
    }

    public function getModuleName()
    {
        return 'actionplan_history';
    }

    public function createdBy()
    {
        return $this->belongsTo('App\Models\User', 'created_by')->select(array('id', 'first_name', 'last_name', 'email', 'picture'));
    }
}
