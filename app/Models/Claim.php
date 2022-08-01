<?php

namespace App\Models;

use App\Interfaces\iREST;
use App\Interfaces\iProtected;

/**
 * App\Models\Claim
 *
 * @property int $id
 * @property string $zendesk_id
 * @property int $wave_target_id
 * @property int|null $question_id
 * @property int $user_id
 * @property string|null $message
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel addPublicResources()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel relations()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieve()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieveAll()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Claim whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Claim whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Claim whereMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Claim whereQuestionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Claim whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Claim whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Claim whereWaveTargetId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Claim whereZendeskId($value)
 * @mixin \Eloquent
 */
class Claim extends SmiceModel implements iREST, iProtected
{
    protected $table        = 'claim';

    protected $primaryKey   = 'id';

    public $timestamps      = true;

    public static function getURI()
    {
        return 'claims';
    }

    public static function getName()
    {
        return 'claim';
    }

    public function getModuleName()
    {
        return 'claims';
    }

    public function question()
    {
        return $this->belongsTo('App\Models\Question');
    }
}
