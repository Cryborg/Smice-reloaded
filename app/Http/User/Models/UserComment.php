<?php

namespace App\Http\User\Models;

use App\Models\SmiceModel;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Spatie\Translatable\HasTranslations;

/**
 * App\Models\UserComment
 *
 * @mixin \Eloquent
 * @property int $id
 * @property int $user_id
 * @property int $created_by
 * @property string|null $created_at
 * @property mixed $comment
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel addPublicResources()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel relations()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieve()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieveAll()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserComment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserComment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserComment whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserComment whereUpdatedAt($value)
 */
class UserComment extends SmiceModel
{
    use DispatchesJobs;

    protected $table = 'user_comment';

    protected $primaryKey = 'id';

    protected $jsons = [
        'user_id',
        'created_by',
        'created_at',
        'comment'
    ];

    protected array $translatable = [
        'user_id',
        'created_by',
        'created_at',
        'comment'
    ];

    protected $fillable = [
        'user_id',
        'created_by',
        'created_at',
        'comment'
    ];

    protected $hidden = [];

    protected $list_rows = [];

    protected $rules = [
        'user_id'    => 'required|integer|read:user',
        'created_by' => 'required|integer|read:user',
        'created_at' => 'required',
        'updated_at' => 'required',
        'comment'    => 'string|required'
    ];

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }
}
