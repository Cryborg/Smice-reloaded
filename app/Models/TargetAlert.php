<?php

namespace App\Models;


/**
 * App\Models\TargetAlert
 *
 * @property int $id
 * @property int $target_id
 * @property int $alert_id
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Alert[] $alert
 * @property-read \App\Models\WaveTarget $target
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel addPublicResources()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel relations()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieve()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieveAll()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TargetAlert whereAlertId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TargetAlert whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TargetAlert whereTargetId($value)
 * @mixin \Eloquent
 */
class TargetAlert extends SmiceModel
{
    protected $table                = 'target_alerts';

    public $timestamps              = false;

    protected $fillable             = [];

    protected $hidden               = [];

    protected array $rules                = [];


    public function target()
    {
        return $this->belongsTo('App\Models\WaveTarget');
    }

    public function alert()
    {
        return $this->belongsToMany('App\models\Alert');
    }
}
