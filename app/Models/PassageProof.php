<?php

namespace App\Models;
use Carbon\Carbon;

/**
 * App\Models\PassageProof
 *
 * @property int $id
 * @property int $user_id
 * @property int $survey_id
 * @property string|null $url
 * @property string|null $position
 * @property string|null $barcode
 * @property int|null $wave_target_id
 * @property string|null $url2
 * @property string|null $url3
 * @property string|null $signature
 * @property float|null $lat
 * @property float|null $lon
 * @property-read \App\Models\Survey $survey
 * @property-read \App\Models\User $user
 * @property-read \App\Models\WaveTarget|null $waveTarget
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel addPublicResources()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel relations()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieve()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieveAll()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PassageProof whereBarcode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PassageProof whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PassageProof wherePosition($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PassageProof whereSurveyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PassageProof whereUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PassageProof whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PassageProof whereWaveTargetId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PassageProof whereUrl2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PassageProof whereUrl3($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PassageProof whereSignature($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PassageProof whereLat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PassageProof whereLon($value)
 * @mixin \Eloquent
 */
class PassageProof extends SmiceModel
{
    protected $table            = 'passage_proof';

    protected $primaryKey       = 'id';

    public $timestamps          = false;

    protected $fillable         = [
        'user_id',
        'survey_id',
        'url',
        'position',
        'barcode',
        'wave_target_id',
        'url2',
        'url3',
        'signature',
        'lat',
        'lon',
        'geoloc_timestamp'
    ];

    protected $hidden           = [];

    protected array $rules            = [
        'user_id'           => 'integer|required',
        'survey_id'         => 'integer|required',
        'url'               => 'string',
        'position'          => 'string',
        'barcode'           => 'string',
        'wave_target_id'    => 'integer',
        'url2'              => 'string',
        'url3'              => 'string',
        'signature'         => 'string',
        'geoloc_timestamp'  => 'timestamp'
    ];

    public function survey()
    {
        return $this->belongsTo('App\Models\Survey');
    }

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function waveTarget()
    {
        return $this->belongsTo('App\Models\WaveTarget');
    }

    protected static function boot()
    {
        parent::boot();

        self::creating(function (self $passage) {
            if ($passage->isDirty('position')) {
                $passage->geoloc_timestamp = Carbon::now();
            }
        });
        self::updating(function (self $passage) {
            if ($passage->isDirty('position')) {
                $passage->geoloc_timestamp = Carbon::now();
            }
        });
    }
}
