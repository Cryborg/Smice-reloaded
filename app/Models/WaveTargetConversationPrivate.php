<?php

namespace App\Models;

use App\Interfaces\iProtected;
use App\Interfaces\iREST;
use Carbon\Carbon;

class WaveTargetConversationPrivate extends SmiceModel implements iREST, iProtected
{
    protected $table                = 'wave_target_conversation_private';

    protected $primaryKey           = 'id';

    public $timestamps = false;

    const STATUS_IN_PROGRESS = 'in progress';
    const STATUS_CLOSE = 'close';

    protected $fillable             = [
        'message',
        'wave_target_id',
        'question_id',
        'status',
        'created_by'
    ];

    protected $hidden               = [
        'created_by'
    ];

    protected array $rules                = [
        'message'                  => 'required|string',
        'wave_target_id'           => 'required|integer|read:targets',
        'question_id'              => 'integer|read:questions',
        'status'                   => 'in:' . self::STATUS_IN_PROGRESS . ',' . self::STATUS_CLOSE,
        'created_by'               => 'required|integer|read:users'
    ];
    protected array $list_rows = [
        'message',
        'wave_target_id',
        'question_id',
        'status',
        'created_by',
    ];

    public static function getURI()
    {
        return 'conversations_private';
    }

    public static function getName()
    {
        return 'conversations_private';
    }

    public function getModuleName()
    {
        return 'conversations_private';
    }

    protected static function boot()
    {
        parent::boot();

        self::creating(function (self $waveTargetConversation) {
            $waveTargetConversation->created_at = Carbon::now();
        });
    }

    public function scopeRelations($query)
    {
        $query->with('createdBy');
    }

    public function waveTarget()
    {
        return $this->belongsTo('App\Models\WaveTarget');
    }

    public function question()
    {
        return $this->belongsTo('App\Models\Question');
    }

    public function society()
    {
        return $this->belongsTo(Society::class, ($this->waveTarget) ? $this->waveTarget->mission->society_id : null);
    }

    public function createdBy()
    {
        return $this->belongsTo('App\Models\User', 'created_by');
    }
}
