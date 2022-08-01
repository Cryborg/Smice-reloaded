<?php

namespace App\Http\User\Models;

use App\Interfaces\iProtected;
use App\Interfaces\iREST;
use App\Models\SmiceModel;
use Illuminate\Foundation\Bus\DispatchesJobs;

class UserPermission extends SmiceModel implements iREST, iProtected
{
    use DispatchesJobs;

    protected $table = 'user_permission';

    public $timestamps = false;

    protected $jsons = [
        'advanced_permissions',
        'backoffice_menu_permissions',
        'homeboard_permissions',
        'permissions',
        'report_visible_fields',
        'shop_visible_fields',
    ];

    protected $fillable = [
        'advanced_permissions',
        'backoffice_menu_permissions',
        'download_passage_proof',
        'homeboard_permissions',
        'permissions',
        'report_visible_fields',
        'shop_visible_fields',
    ];

    protected $hidden = [];

    protected $list_rows = [];

    public static function getURI()
    {
        return 'permissions';
    }

    public static function getName()
    {
        return 'permission';
    }

    public function getModuleName()
    {
        return 'permissions';
    }

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }
}
