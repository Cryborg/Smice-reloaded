<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Message
 *
 * @property int $id
 * @property string $content
 * @property int $sent_to
 * @property string $created_at
 * @property-read \App\Models\User $sentTo
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Message whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Message whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Message whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Message whereSentTo($value)
 * @mixin \Eloquent
 */
class Message extends Model
{
    const CREATED_AT = 'created';

    protected $table            = 'message';

    protected $primaryKey       = 'id';

    public $timestamps          = false;

    protected $fillable         = [
        'content',
        'sent_to',
    ];

    protected array $rules            = [
        'content'         => 'string|required',
        'sent_to'         => 'integer|required|read:sentTo|exists:user,id',
    ];

    public function sentTo()
    {
        return $this->belongsTo('App\Models\User', 'sent_to');
    }
}
