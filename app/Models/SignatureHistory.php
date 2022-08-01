<?php

namespace App\Models;

/**
 * App\Models\SignatureHistory
 *
 * @property-read \App\Models\Signature $signature
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel addPublicResources()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel relations()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieve()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieveAll()
 * @mixin \Eloquent
 */
class SignatureHistory extends SmiceModel
{
    protected $table                = 'signature_history';

    protected $primarykey           = 'id';

    public $timestamps              = false;

    protected $fillable             = [
        'id',
        'signature_id',
        'request_status',
        'action',
        'reason',
        'performed_by_email',
        'performed_at',
        'activity',
        'operation_type',
        'action_id',
        'performed_by_name'
    ];

    public function signature()
    {
        return $this->belongsTo('App\Models\Signature');
    }
}