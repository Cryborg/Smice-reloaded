<?php

namespace App\Models;

use App\Interfaces\iREST;

/**
 * App\Models\ShopContact
 *
 * @mixin \Eloquent
 * @property int $id
 * @property int $shop_id
 * @property string $first_name
 * @property string $last_name
 * @property string|null $phone
 * @property string|null $phone_mobile
 * @property string|null $email
 * @property-read \App\Models\Shop $shop
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ShopContact whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ShopContact whereFirstName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ShopContact whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ShopContact whereLastName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ShopContact wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ShopContact wherePhoneMobile($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ShopContact whereShopId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel addPublicResources()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel relations()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieve()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SmiceModel retrieveAll()
 */
class ShopContact extends SmiceModel implements iREST
{
    protected $table = 'shop_contact';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'shop_id',
        'first_name',
        'last_name',
        'phone',
        'phone_mobile',
        'email',
    ];

    protected $hidden = [
        'shop_id',
    ];

    protected array $list_rows = [];

    protected array $rules = [
        'shop_id' => 'required|integer|exists:shop,id',
        'first_name' => 'string',
        'last_name' => 'string',
        'phone' => 'string',
        'phone_mobile' => 'string',
        'email' => 'email',
    ];

    public static function getURI()
    {
        return 'shop-contacts';
    }

    public static function getName()
    {
        return 'shop_contact';
    }

    public function getModuleName()
    {
        return 'shop_contacts';
    }

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }
}
