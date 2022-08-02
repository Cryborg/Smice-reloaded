<?php

namespace App\Models;

/**
 * App\Models\AnswerFileDeleted
 *
 * @property int $id
 * @property int $survey_id
 * @property int $answer_id
 * @property string $url
 * @property string $text
 * @property string $language_code
 * @property string $transcription_job_status
 * @property string $type
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel addPublicResources()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel relations()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieve()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieveAll()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AnswerFileDeleted whereAnswerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AnswerFileDeleted whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AnswerFileDeleted whereLanguageCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AnswerFileDeleted whereSurveyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AnswerFileDeleted whereText($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AnswerFileDeleted whereTranscriptionJobStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AnswerFileDeleted whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AnswerFileDeleted whereUrl($value)
 * @mixin \Eloquent
 */
class AnswerFileDeleted extends SmiceModel
{
    protected $table            = 'answer_files_deleted';

    protected $primaryKey       = 'id';

    public $timestamps          = false;

    protected $fillable         = [
        'id',
        'answer_id',
        'url',
        'text',
        'language_code',
        'transcription_job_status',
        'type'
    ];

    protected $hidden           = [];

    protected array $rules            = [
        'answer_id'                 => 'integer|required',
        'url'                       => 'string',
        'text'                      => 'string',
        'language_code'             => 'string',
        'transcription_job_status'  => 'string',
        'type'                      => 'string'
    ];
}
