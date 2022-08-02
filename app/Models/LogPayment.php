<?php

namespace App\Models;

/**
 * App\Models\LogPayment
 *
 * @property int $id
 * @property int $user_id
 * @property int $montant_cheque
 * @property float $montant_virement
 * @property string $date
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property int $status_id
 * @property-read \App\Models\TransferStatus $status
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel addPublicResources()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel relations()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieve()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieveAll()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\LogPayment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\LogPayment whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\LogPayment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\LogPayment whereMontantCheque($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\LogPayment whereMontantVirement($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\LogPayment whereStatusId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\LogPayment whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\LogPayment whereUserId($value)
 * @mixin \Eloquent
 */
class LogPayment extends SmiceModel
{
    protected $table 	  = 'log_payment';

	protected $primaryKey = 'id';

	public $timestamps    = true;

	protected $fillable	  = [
		'user_id',
		'montant_cheque',
		'montant_virement',
		'status_id',
		'date'
	];

	protected array $rules = [
		'user_id' 			=> 'integer|required',
		'montant_cheque' 	=> 'integer|required',
		'montant_virement' 	=> 'decimal|required',
		'status_id'   		=> 'integer|required',
		'date' 			 	=> 'date|required'
	];

	protected $hidden = [];

    public static $status = [
    	'En attente de validation',
        'Demande annulee',
        'Paiement programme',
        'Paiement effectue',
        'Probleme de reglement'
    ];

	public function user()
	{
		return $this->belongsTo('App\Models\User');
	}

	public function status()
	{
		return $this->belongsTo('App\Models\TransferStatus');
	}
}
