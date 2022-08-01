<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\ConversationWaveMission
 *
 * @property int $id
 * @property int|null $conversation_id
 * @property int|null $wave_id
 * @property int|null $mission_id
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ConversationWaveMission whereConversationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ConversationWaveMission whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ConversationWaveMission whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ConversationWaveMission whereMissionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ConversationWaveMission whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ConversationWaveMission whereWaveId($value)
 * @mixin \Eloquent
 */
class ConversationWaveMission extends Model
{
    protected $table = 'conversation_wave_mission';

    protected $fillable = [
        'conversation_id',
        'wave_id',
        'mission_id',
    ];
}
