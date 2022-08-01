<?php

namespace App\Models;

use App\Interfaces\iProtected;
use App\Interfaces\iREST;

/**
 * App\Models\Document
 *
 * @property int $id
 * @property int $document_id
 * @property int $templatesign_id
 * @property string $document_name
 * @property int $document_size
 * @property int $document_order
 * @property int $total_page
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel addPublicResources()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel relations()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieve()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieveAll()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Document whereDocumentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Document whereDocumentName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Document whereDocumentOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Document whereDocumentSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Document whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Document whereTemplatesignId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Document whereTotalPage($value)
 * @mixin \Eloquent
 */
class Document extends SmiceModel implements iREST, iProtected
{
    protected $table                = 'document';

    protected $primarykey           = 'id';

    public $timestamps              = false;
    
    protected $fillable             = [
        'id',
        'document_id',
        'document_name',
        'document_size',
        'document_order',
        'total_page',
    ];

    protected $hidden               = [];

    protected $list_rows            = [
    ];

    protected $rules                = [
    ];

    public static function getURI()
    {
        return 'document';
    }

    public static function getName()
    {
        return 'document';
    }

    public function getModuleName()
    {
        return 'documents';
    }
}
