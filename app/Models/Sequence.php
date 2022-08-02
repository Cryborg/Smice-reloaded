<?php

namespace App\Models;

use App\Exceptions\SmiceException;
use App\Interfaces\iProtected;
use App\Interfaces\iREST;
use App\Interfaces\iTranslatable;
use Illuminate\Support\Facades\DB;

/**
 * App\Models\Sequence
 *
 * @property int $id
 * @property mixed $name
 * @property mixed|null $info
 * @property string|null $description
 * @property bool $library
 * @property bool $default_sequence
 * @property int $created_by
 * @property int $society_id
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\SurveyItem[] $items
 * @property-read \App\Models\Society $society
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Survey[] $surveys
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel addPublicResources()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Sequence minimum()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel relations()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieve()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieveAll()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Sequence whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Sequence whereDefaultSequence($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Sequence whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Sequence whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Sequence whereInfo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Sequence whereLibrary($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Sequence whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Sequence whereSocietyId($value)
 * @mixin \Eloquent
 */
class Sequence extends SmiceModel implements iREST, iProtected, iTranslatable
{
    protected $table = 'sequence';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $jsons = [
        'name',
        'info'
    ];

    protected array $translatable = [
        //'name',
        'info'
    ];

    protected $fillable = [
        'society_id',
        'name',
        'info',
        'description',
        'created_by',
        'library'
    ];

    protected $hidden = [
        'society_id',
        'created_by'
    ];

    protected array $list_rows = [
        'name',
        'library',
        'description',
        'default_sequence'
    ];

    protected array $rules = [
        'name' => 'array|required',
        'society_id' => 'integer|required',
        'library' => 'boolean',
        'description' => 'string',
        'created_by' => 'integer|required',
        'info' => 'array'
    ];

    public static function getURI()
    {
        return 'sequences';
    }

    public static function getName()
    {
        return 'sequence';
    }

    public function getModuleName()
    {
        return 'sequences';
    }

    static function boot()
    {
        parent::boot();

        self::updating(function (self $sequence) {
            if ($sequence->default_sequence) {
                throw new SmiceException(
                    SmiceException::HTTP_INTERNAL_SERVER_ERROR,
                    SmiceException::E_RESOURCE,
                    'The default sequence can not be modified.'
                );
            }
        });

        self::deleting(function (self $sequence) {
            if ($sequence->default_sequence) {
                throw new SmiceException(
                    SmiceException::HTTP_INTERNAL_SERVER_ERROR,
                    SmiceException::E_RESOURCE,
                    'The default sequence can not be deleted.'
                );
            }

            foreach ($sequence->items as $survey_item) {
                $survey_item->delete();
            }

            return true;
        });
    }

    public function society()
    {
        return $this->belongsTo('App\Models\Society');
    }

    public function surveys()
    {
        return $this->belongsToMany('App\Models\Survey', 'survey_item', 'item_id', 'survey_id')
            ->wherePivot('type', '=', SurveyItem::ITEM_SEQUENCE);
    }

    public function items()
    {
        return $this->hasMany('App\Models\SurveyItem', 'item_id')
            ->where('type', SurveyItem::ITEM_SEQUENCE);
    }

    public function scopeMinimum($query)
    {
        $query->select('id', 'name');
    }

    /**
     * Determines if a sequence is in the library or not
     * by running an SQL statement.
     * @param null $sequence_id
     * @param null $society_id
     */
    public static function inLibrary($sequence_id = null, $society_id = null)
    {
        return DB::affectingStatement('SELECT id FROM sequence WHERE society_id = :society_id AND library = true AND id = :id',
            [
                'society_id' => intval($society_id),
                'id' => intval($sequence_id)
            ]
        );
    }
}
