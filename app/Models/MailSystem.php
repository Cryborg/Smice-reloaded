<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\MailSystem
 *
 * @property int $id
 * @property string $name
 * @property mixed|null $attachments
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MailSystem whereAttachments($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MailSystem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MailSystem whereName($value)
 * @mixin \Eloquent
 */
class MailSystem extends Model
{
    protected $table        = 'mail_system';

    protected $primaryKey   = 'id';

    public $timestamps      = false;

    protected $jsons        = [
        'attachments'
    ];
}