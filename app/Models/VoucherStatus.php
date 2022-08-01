<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\VoucherStatus
 *
 * @property int $id
 * @property mixed $status
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\VoucherStatus whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\VoucherStatus whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\VoucherStatus whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\VoucherStatus whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class VoucherStatus extends Model
{
    protected $table = 'voucher_status';

    protected $primaryKey = 'id';

    protected $fillable = [
        'filename',
        'operations',
        'amount',
        'created_by',
        'status',
    ];

    protected $jsons = [
        'status',
    ];

    public static $status = [
        'En attente',
        'annulee',
        'traitee'
    ];

    protected $hidden = [];

    protected $rules = [
        'filename' => 'string|required',
        'operations' => 'integer|required',
        'amount' => 'decimal|required',
        'created_by' => 'integer|required',
        'status' => 'string|required'
    ];
}
