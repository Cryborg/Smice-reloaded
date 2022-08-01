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
 * @property string|null $text
 * @property string|null $language_code
 * @property string|null $transcription_job_status
 * @property string|null $type
 * @property-read \App\Models\Answer $answer
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel addPublicResources()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel relations()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieve()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieveAll()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AnswerImage whereAnswerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AnswerImage whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AnswerImage whereUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AnswerFile whereLanguageCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AnswerFile whereText($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AnswerFile whereTranscriptionJobStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AnswerFile whereType($value)
 * @mixin \Eloquent
 */
class AnswerFile extends SmiceModel implements iREST, iProtected
{
    protected $table                = 'answer_files';

    protected $primaryKey           = 'id';

    public    $timestamps           = false;

    protected $fillable             = [
        'url',
        'answer_id',
    ];

    protected $hidden               = [
    ];

    protected $rules                = [
        'url'          => 'string|required',
        'answer_id'    => 'integer|required|exists:answer,id',
    ];

    public static function getURI()
    {
        return 'answer_files';
    }

    public static function getName()
    {
        return 'answer_file';
    }

    public function getModuleName()
    {
        return 'answer_files';
    }

    public function answer()
    {
        return $this->belongsTo('App\Models\Answer');
    }
}
