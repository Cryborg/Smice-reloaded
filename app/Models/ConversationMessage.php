<?php

namespace App\Models;

use App\Interfaces\iProtected;
use App\Interfaces\iREST;

/**
 * App\Models\ConversationMessage
 *
 * @property int $id
 * @property int|null $conversation_id
 * @property int|null $from
 * @property string $message
 * @property bool $read
 * @property string|null $read_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property string|null $deleted_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel addPublicResources()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel relations()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieve()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieveAll()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ConversationMessage whereConversationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ConversationMessage whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ConversationMessage whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ConversationMessage whereFrom($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ConversationMessage whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ConversationMessage whereMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ConversationMessage whereRead($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ConversationMessage whereReadAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ConversationMessage whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ConversationMessage extends SmiceModel implements iREST, iProtected
{
    protected $table = 'conversation_message';

    protected $fillable = [
        'conversation_id',
        'message',
        'read',
        'from'
    ];

    static public function getURI() // utiliser pour match avec le groupe de route
    {
        return 'tests';
    }

    static public function getName() // pour load le model
    {
        return 'test';
    }

    public function getModuleName() // pour load les permissions de l'utilisateur pour cette ressource
    {
        return 'tests';
    }

    public function from()
    {
        return $this->belongsTo('App\Models\User', 'from');
    }
}
