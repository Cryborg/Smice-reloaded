<?php

namespace App\Models;

use App\Interfaces\iProtected;
use App\Interfaces\iREST;

/**
 * App\Models\Conversation
 *
 * @property int $id
 * @property int|null $wave_target_id
 * @property int|null $society_id
 * @property string $type
 * @property string $status
 * @property int|null $to
 * @property int|null $created_by
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property string|null $deleted_at
 * @property-read \App\Models\ConversationMessage $lastMessage
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\ConversationMessage[] $messages
 * @property-read \App\Models\Society|null $society
 * @property-read \App\Models\WaveTarget|null $waveTarget
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel addPublicResources()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Conversation byUserId($userId)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel relations()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieve()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieveAll()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Conversation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Conversation whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Conversation whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Conversation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Conversation whereSocietyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Conversation whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Conversation whereTo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Conversation whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Conversation whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Conversation whereWaveTargetId($value)
 * @mixin \Eloquent
 */
class Conversation extends SmiceModel implements iREST, iProtected
{
    protected $table = 'conversation';

    protected $fillable = [
        'wave_target_id',
        'type',
        'status',
        'to',
        'society_id',
        'created_by',
    ];

    static public function getURI() // utiliser pour match avec le groupe de route
    {
        return 'messages';
    }
    
    static public function getName() // pour load le model
    {
        return 'messages';
    }
    
    public function getModuleName() // pour load les permissions de l'utilisateur pour cette ressource
    {
        return 'messages';
    }

    public function messages()
    {
        return $this->hasMany('App\Models\ConversationMessage');
    }

    public function lastMessage()
    {
        return $this->hasOne('App\Models\ConversationMessage')->orderBy('created_at', 'desc');
    }

    public function waveTarget()
    {
        return $this->belongsTo('App\Models\WaveTarget');
    }

    public function readMessages($userId)
    {
        $lastMessage = $this->messages()->orderBy('created_at', 'desc')->limit(1)->first();

        if ($this->to === $userId) {
            $lastMessage->read = true;
            $lastMessage->save();
        }
    }

    /**
     * Scope a query to only include conversations by user Id.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByUserId($query, $userId)
    {
        return $query->where(function($query) use($userId){
            return $query->where('to', $userId)
                            ->orWhere('created_by', $userId);
        });
    }

    public function society()
    {
        return $this->belongsTo('App\Models\Society');
    }
}