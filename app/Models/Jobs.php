<?php

namespace App\Models;

/**
 * App\Models\Jobs
 *
 * @property int $id
 * @property string $queue
 * @property string $payload
 * @property int $attempts
 * @property int $reserved
 * @property int|null $reserved_at
 * @property int $available_at
 * @property int $created_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel addPublicResources()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel relations()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieve()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieveAll()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Jobs whereAttempts($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Jobs whereAvailableAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Jobs whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Jobs whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Jobs wherePayload($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Jobs whereQueue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Jobs whereReserved($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Jobs whereReservedAt($value)
 * @mixin \Eloquent
 */
class Jobs extends SmiceModel
{
    protected $table = 'jobs';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $rules = [
        'queue'        => 'string|required',
        'payload'      => 'string|required',
        'attempts'     => 'integer|required',
        'reserved'     => 'integer',
        'available_at' => 'integer|required',
        'created_at'   => 'integer|required'
    ];

    protected $list_rows = [
        'queue',
        'payload',
        'attempts',
        'reserved',
        'created_at'
    ];
}