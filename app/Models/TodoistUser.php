<?php

namespace App\Models;

/**
 * App\Models\TodoistUser
 *
 * @property int $id
 * @property int $todoist_api_user_id
 * @property int $user_id
 * @property int $society_id
 * @property string $created_at
 * @property int $created_by
 * @property-read \App\Models\Society $society
 * @property-read \App\Models\User $user
 * @property-read \App\Models\User $createdBy
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel addPublicResources()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel relations()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieve()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieveAll()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TodoistUser whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TodoistUser whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TodoistUser whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TodoistUser whereSocietyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TodoistUser whereTodoistApiUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TodoistUser whereUserId($value)
 * @mixin \Eloquent
 */
class TodoistUser extends SmiceModel
{
    protected $table = 'todoist_user';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'todoist_api_user_id',
        'user_id',
        'society_id',
    ];

    protected $hidden = [
        'society_id',
        'created_by'
    ];

    protected $rules = [
        'todoist_api_user_id' => 'integer|required',
        'user_id' => 'integer|required|read:users',
        'society_id' => 'integer|required|read:societies',
        'created_by' => 'integer|required|read:users',
    ];

    public function society()
    {
        return $this->belongsTo('App\Models\Society');
    }

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function createdBy()
    {
        return $this->belongsTo('App\Models\User', 'created_by');
    }
}