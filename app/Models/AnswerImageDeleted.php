<?php

namespace App\Models;

/**
 * App\Models\AnswerImageDeleted
 *
 * @property int $id
 * @property int $answer_id
 * @property string|null $url
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel addPublicResources()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel relations()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieve()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieveAll()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AnswerImageDeleted whereAnswerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AnswerImageDeleted whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AnswerImageDeleted whereUrl($value)
 * @mixin \Eloquent
 */
class AnswerImageDeleted extends SmiceModel
{
    protected $table            = 'answer_images_deleted';

    protected $primaryKey       = 'id';

    public $timestamps          = false;

    protected $fillable         = [
        'id',
        'answer_id',
        'url'
    ];

    protected $hidden           = [];

    protected array $rules            = [
        'answer_id'                 => 'integer|required',
        'url'                       => 'string'
    ];
}
