<?php

namespace App\Models;

use App\Interfaces\iREST;


/**
 * App\Models\ActionplanComments
 *
 * @property int $id
 * @property int $actionplan_id
 * @property string $comment
 * @property int $created_by
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read \App\Models\User $createdBy
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel addPublicResources()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel relations()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieve()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieveAll()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ActionplanComments whereActionplanId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ActionplanComments whereComment($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ActionplanComments whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ActionplanComments whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ActionplanComments whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ActionplanComments whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ActionplanComments extends SmiceModel implements iREST
{
    protected $table = 'actionplan_comments';

    protected $primaryKey = 'id';

    public $timestamps = true;

    protected $datesConvert = ['created_at'];

    protected $fillable = [
        'comment',
        'created_by',
        'created_at',
    ];

    protected $with = ['createdBy'];

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

    public function createdBy()
    {
        return $this->belongsTo('App\Models\User', 'created_by')->select(array('id', 'first_name', 'last_name', 'email', 'picture'));
    }

    public function getModuleName()
    {
        return 'actionplan_history';
    }

    //public function getCreatedAtAttribute($date)
    //{
    //    return \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $date)->format('Y-m-d');
    //}
}
