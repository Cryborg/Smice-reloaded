<?php

namespace App\Models;

use App\Interfaces\iProtected;
use App\Interfaces\iREST;

/**
 * App\Models\MailTemplate
 *
 * @property int $id
 * @property string $name
 * @property string $html
 * @property mixed|null $attachments
 * @property int|null $created_by
 * @property int $society_id
 * @property string|null $description
 * @property-read \App\Models\Society $society
 * @property-read \App\Models\User|null $createdBy
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel addPublicResources()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel relations()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieve()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieveAll()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MailTemplate whereAttachments($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MailTemplate whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MailTemplate whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MailTemplate whereHtml($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MailTemplate whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MailTemplate whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MailTemplate whereSocietyId($value)
 * @mixin \Eloquent
 */
class MailTemplate extends SmiceModel implements iREST, iProtected
{
    protected $table            = 'mail_template';

    protected $primaryKey       = 'id';

    public $timestamps          = false;

    protected $jsons            = [
        'html',
        'attachments'
    ];

    protected array $translatable = [
        'html'
    ];

    protected $fillable         = [
        'name',
        'html',
        'attachments',
        'society_id',
        'created_by',
        'description'
    ];

    protected $hidden           = [
        'society_id',
        'created_by'
    ];

    protected array $list_rows            = [
        'name',
        'description'
    ];

    protected array $rules            = [
        'name'          => 'string|required|unique_with:mail_template,society_id,{id}',
        'html'          => 'string|required',
        'attachments'   => 'array_array:type,name,content',
        'created_by'    => 'integer|required',
        'description'   => 'string'
    ];

    public static function getURI()
    {
        return 'mail-templates';
    }

    public static function getName()
    {
        return 'mail_template';
    }

    public function getModuleName()
    {
        return 'mail_templates';
    }

    public function society()
    {
        return $this->belongsTo('App\Models\Society', 'society_id');
    }

    public function createdBy()
    {
        return $this->belongsTo('App\Models\User', 'created_by');
    }

    /**
     * @todo Revoir la liste des tags
     * @todo Créer une fonction dans la classe JsonCleaner pour vérifier le JSON de l'attribut "attachments"
     *
     * @return array
     */
    public static function  getTags()
    {
        return [
            'user'  =>
                [
                    'last_name'     => '{{user.last_name}}',
                    'first_name'    => '{{user.first_name}}',
                    'birthday'      => '{{user.birth_date}}',
                    'city'          => '{{user.city}}',
                    'rue'           => '{{user.street}}',
                    'email'         => '{{user.email}}',
                    'password'      => '{{user.password}}'
                ],
            'mission' =>
                [
                    'name'      => '{{mission.name}}'
                ],
            'program' =>
                [
                    'name'      => '{{program.name}}',
                    'info'      => '{{program.info}}'
                ]
        ];
    }
}
