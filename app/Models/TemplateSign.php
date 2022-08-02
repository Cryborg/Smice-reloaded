<?php

namespace App\Models;

/**
 * App\Models\TemplateSign
 *
 * @property int $id
 * @property int $template_id
 * @property string $template_name
 * @property bool $is_deleted
 * @property int $expiration_days
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel addPublicResources()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel relations()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieve()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieveAll()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TemplateSign whereExpirationDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TemplateSign whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TemplateSign whereIsDeleted($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TemplateSign whereTemplateId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TemplateSign whereTemplateName($value)
 * @mixin \Eloquent
 */
class TemplateSign extends SmiceModel
{
    protected $table                = 'templatesign';

    protected $primarykey           = 'id';

    public $timestamps              = false;

    protected $fillable             = [

        'id',
        'template_id',
        'template_name',
        'is_deleted',
        'expiration_days'
    ];

    protected $hidden               = [];

    protected array $list_rows            = [
    ];

    protected array $rules                = [
    ];

    public static function getURI()
    {
        return 'templatesign';
    }

    public static function getName()
    {
        return 'templatesign';
    }

    public function getModuleName()
    {
        return 'templatesigns';
    }
}
