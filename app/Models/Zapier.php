<?php

namespace App\Models;

use App\Jobs\ZapierRunnerJob;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Bus;

/**
 * App\Models\Zapier
 *
 * @property int $id
 * @property int $society_id
 * @property string $event
 * @property string $target_url
 * @property-read \App\Models\Society $society
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Zapier whereEvent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Zapier whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Zapier whereSocietyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Zapier whereTargetUrl($value)
 * @mixin \Eloquent
 */
class Zapier extends Model
{
    CONST ZAPIER_QUEUE      = 'zapier';

    protected $table        = 'zapier_hook';

    protected $primaryKey   = 'id';

    public $timestamps      = false;

    protected $fillable     = [
        'event',
        'target_url'
    ];

    protected $hidden       = [];

    protected array $rules        = [];

    /*
     * Les "triggers" disponibles sur Zapier
     */
    private static $events  = [
        'new_user'
    ];

    public function         society()
    {
        return $this->belongsTo('App\Models\Society');
    }

    static public function  eventExists($event = null)
    {
        return in_array($event, static::$events);
    }

    public static function  send($event, $society_id, array $payload)
    {
        if (!static::eventExists($event)) {
            return false;
        } else {
            $zapier_hooks = self::where([
                'event' => $event,
                'society_id' => $society_id
            ])->get();

            if (!$zapier_hooks->isEmpty()) {
                Bus::dispatch(
                    (new ZapierRunnerJob($zapier_hooks, $payload))->onQueue(self::ZAPIER_QUEUE)
                );
            }
        }

        return true;
    }
}
