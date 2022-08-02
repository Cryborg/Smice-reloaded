<?php

namespace App\Models;

/**
 * App\Models\Task
 *
 * @property int $id
 * @property string $name
 * @property int $user_id
 * @property bool $status
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel addPublicResources()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel relations()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieve()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieveAll()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Task whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Task whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Task whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Task whereUserId($value)
 * @mixin \Eloquent
 */
class Task extends SmiceModel
{
    protected $table                = 'task';

    protected $primaryKey           = 'id';

    public $timestamps              = false;

    protected $fillable             = [];

    protected $hidden               = [
        'user_id'
    ];

    protected array $rules                = [
        'name'      => 'string|required|unique_with:task,user_id,{id}',
        'user_id'   => 'integer|required|exists:user,id',
        'status'    => 'boolean'
    ];

    private static $tasks           = [
        'Compléter votre profil',
        'Renseigner une adresse email',
        'Renseigner un mot de passe',
        'Gagner la ligue des champions'
    ];

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    public static function getTasks()
    {
        return static::$tasks;
    }
}
