<?php

namespace App\Models;

use App\Interfaces\iProtected;
use App\Interfaces\iREST;

/**
 * App\Models\AnswerImage
 *
 * @property int $id
 * @property int $answer_id
 * @property string $url
 * @property bool $checked_blur
 * @property-read \App\Models\Answer $answer
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel addPublicResources()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel relations()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieve()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieveAll()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AnswerImage whereAnswerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AnswerImage whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AnswerImage whereUrl($value)
 * @mixin \Eloquent
 */
class AnswerImage extends SmiceModel implements iREST, iProtected
{
    protected $table                = 'answer_images';

    protected $primaryKey           = 'id';

    public    $timestamps           = false;

    protected $fillable             = [
        'url',
        'answer_id',
        'checked_blur',
        'save_before_blur'
    ];

    protected $hidden               = [
    ];

    protected $rules                = [
        'url'          => 'string|required',
        'answer_id'    => 'integer|required|exists:answer,id',
    ];

    public static function getURI()
    {
        return 'answer_images';
    }

    public static function getName()
    {
        return 'answer_image';
    }

    public function getModuleName()
    {
        return 'answer_images';
    }

    public function answer()
    {
        return $this->belongsTo('App\Models\Answer');
    }
}
