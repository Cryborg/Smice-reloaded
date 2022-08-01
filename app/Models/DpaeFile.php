<?php

namespace App\Models;

use Carbon\Carbon;

/**
 * App\Models\DpaeFile
 *
 * @property int $id
 * @property string $filename
 * @property int $count
 * @property string $created_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel addPublicResources()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel relations()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieve()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieveAll()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\DpaeFile whereCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\DpaeFile whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\DpaeFile whereFilename($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\DpaeFile whereId($value)
 * @mixin \Eloquent
 */
class DpaeFile extends SmiceModel
{
	protected $table            = 'dpae_file';

	protected $primaryKey       = 'id';

	public $timestamps          = false;

	protected $fillable         = [
		'filename',
        'count',
        'created_at',
	];

	protected $rules             = [
		'filename'              => 'string|required',
        'count'                 => 'integer|required',
        'created_at'            => 'string|required',
	];

    protected static function boot()
    {
        parent::boot();

        self::creating(function (self $dpaeFile) {
            $dpaeFile->created_at = Carbon::now();
        });
    }
}