<?php

namespace App\Models;

/**
 * App\Models\FailedJobs
 *
 * @property int $id
 * @property string $connection
 * @property string $queue
 * @property string $payload
 * @property string $failed_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel addPublicResources()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel relations()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieve()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieveAll()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\FailedJobs whereConnection($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\FailedJobs whereFailedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\FailedJobs whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\FailedJobs wherePayload($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\FailedJobs whereQueue($value)
 * @mixin \Eloquent
 */
class FailedJobs extends SmiceModel
{
    protected $table            = 'failed_jobs';

    protected $primaryKey       = 'id';

    public $timestamps          = false;

    protected $fillable         = [];

    protected $hidden           = [
        'payload',
    ];

    protected array $rules            = [];

    protected array $list_rows        = [
        'queue',
        'failed_at'
    ];
}
