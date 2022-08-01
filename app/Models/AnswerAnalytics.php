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
 * @property string|null $score
 * @property string|null $keyphrases
 * @property string|null $text
 * @property-read \App\Models\Answer $answer
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel addPublicResources()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel relations()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieve()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieveAll()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AnswerImage whereAnswerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AnswerImage whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AnswerImage whereUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AnswerAnalytics whereKeyphrases($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AnswerAnalytics whereScore($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AnswerAnalytics whereText($value)
 * @mixin \Eloquent
 * @property string|null $magnitude
 * @property string|null $keyphrases_score
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AnswerAnalytics whereKeyphrasesScore($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AnswerAnalytics whereMagnitude($value)
 */
class AnswerAnalytics extends SmiceModel implements iREST, iProtected
{
    protected $table                = 'answer_analytics';

    protected $primaryKey           = 'id';

    public    $timestamps           = false;

    protected $fillable             = [
        'score',
        'keyphrases',
    ];

    protected $hidden               = [
    ];


    public static function getURI()
    {
        return 'answer_analytics';
    }

    public static function getName()
    {
        return 'answer_analytics';
    }

    public function getModuleName()
    {
        return 'answer_analytics';
    }

    public function answer()
    {
        return $this->belongsTo('App\Models\Answer');
    }
}
