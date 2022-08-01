<?php

namespace App\Models;

/**
 * App\Models\TodoistProject
 *
 * @property int $id
 * @property int $todoist_api_project_id
 * @property int $shop_id
 * @property int $society_id
 * @property string $todoist_api_key
 * @property string $created_at
 * @property int $created_by
 * @property-read \App\Models\Shop $shop
 * @property-read \App\Models\Society $society
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel addPublicResources()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel relations()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieve()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieveAll()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TodoistProject whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TodoistProject whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TodoistProject whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TodoistProject whereShopId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TodoistProject whereSocietyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TodoistProject whereTodoistApiKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TodoistProject whereTodoistApiProjectId($value)
 * @mixin \Eloquent
 * @property-read \App\Models\User $createdBy
 */
class TodoistProject extends SmiceModel
{
    protected $table            = 'todoist_project';

    protected $primaryKey       = 'id';

    public $timestamps          = false;

    protected $fillable         = [
        'todoist_id',
        'shop_id',
        'society_id',
        'todoist_api_key'
    ];

    protected $hidden           = [
        'society_id',
        'created_by'
    ];

    protected $rules            = [
        'todoist_id'            => 'integer|required',
        'shop_id'               => 'integer|required|read:shop',
        'society_id'            => 'integer|required|read:society',
        'todoist_api_key'       => 'string|required'
    ];

    public function society()
    {
        return $this->belongsTo('App\Models\Society');
    }

    public function shop()
    {
        return $this->belongsTo('App\Models\Shop');
    }

    public function createdBy()
    {
        return $this->belongsTo('App\Models\User', 'created_by');
    }
}