<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\TransferStatus
 *
 * @property int $id
 * @property mixed $status
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TransferStatus whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TransferStatus whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TransferStatus whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TransferStatus whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class TransferStatus extends Model
{
    protected $table                = 'transfer_status';

    protected $primaryKey           = 'id';

    protected $fillable             = [
        'status'
    ];

    protected $jsons                = [
        'status'
    ];

    protected $hidden               = [
        'id',
        'created_at',
        'updated_at'
    ];

    protected array $rules                = [
        'status'                => 'array|required',
    ];
}
