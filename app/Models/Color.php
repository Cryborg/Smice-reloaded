<?php

namespace App\Models;

use App\Interfaces\iProtected;
use App\Interfaces\iREST;
use App\Interfaces\iTranslatable;

/**
 * App\Models\Color
 *
 * @property int $id
 * @property int $survey_id
 * @property string $hex
 * @property mixed $description
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\QuestionRow[] $answers
 * @property-read \App\Models\Society $society
 * @property-read \App\Models\Survey $survey
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel addPublicResources()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Color bySurvey($surveyId)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel relations()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieve()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieveAll()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Color whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Color whereHex($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Color whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Color whereSurveyId($value)
 * @mixin \Eloquent
 */
class Color extends SmiceModel
{
    protected $table = 'color';

    public $timestamps = false;

    protected $fillable = ['survey_id', 'hex', 'description'];

    const COLOR_RED     = ['hex' => '#ff0000', 'description' => 'No'];
    const COLOR_GREEN   = ['hex' => '#00ff00', 'description' => 'Yes'];

    /**
     * @return string
     */
    public static function getURI()
    {
        return 'colors';
    }

    /**
     * @return string
     */
    public static function getName()
    {
        return 'color';
    }

    /**
     * @return string
     */
    public function getModuleName()
    {
        return 'colors';
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function survey()
    {
        return $this->belongsTo(Survey::class);
    }

//    /**
//     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
//     */
//    public function society()
//    {
//        return $this->belongsTo(Society::class, $this->survey->society_id);
//    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function answers()
    {
        return $this->belongsToMany(QuestionRow::class, 'selected_colors', 'color_id', 'answer_id');
    }

    /**
     * @param $query
     * @param $surveyId
     * @return mixed
     */
    public function scopeBySurvey($query, $surveyId)
    {
        return $query->where('survey_id', $surveyId);
    }
}
