<?php

namespace App\Models;


/**
 * App\Models\PasswordRecovery
 *
 * @property int $id
 * @property string $token
 * @property string $expire
 * @property int $user_id
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel addPublicResources()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel relations()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieve()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieveAll()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PasswordRecovery whereExpire($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PasswordRecovery whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PasswordRecovery whereToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PasswordRecovery whereUserId($value)
 * @mixin \Eloquent
 */
class PasswordRecovery extends SmiceModel
{
    protected $table        = 'password_recovery';

    protected $primaryKey   = 'id';

    public $timestamps      = false;

    public $fillable         = [
        'user_id',
        'token',
        'expire'
    ];

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }
}