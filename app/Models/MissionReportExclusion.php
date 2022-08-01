<?php

namespace App\Models;

use App\Interfaces\iProtected;
use App\Interfaces\iREST;

class MissionReportExclusion extends SmiceModel implements iREST, iProtected
{

  
	protected $table            = 'mission_report_exclusion';

	protected $primaryKey       = 'id';

	public $timestamps          = false;

    protected $fillable = [
        'mission_id',
        'group_id',
    ];

    protected $jsons = [];
    

    protected $hidden = [];

    protected $rules = [];

    public static function getURI()
    {
        return 'mission_report_exclusion';
    }

    public static function getName()
    {
        return 'mission_report_exclusion';
    }

    public function getModuleName()
    {
        return 'mission_report_exclusion';
    }

    public function mission()
    {
        return $this->belongsTo('App\Models\Mission');
    }
    
}