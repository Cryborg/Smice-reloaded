<?php

namespace App\Http\Group\Models;

use App\Http\User\Models\User;
use App\Interfaces\iProtected;
use App\Interfaces\iREST;
use App\Interfaces\iTranslatable;
use App\Models\SmiceModel;
use App\Models\Society;
use App\Traits\HasCreatedBy;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Group extends SmiceModel implements iREST, iProtected, iTranslatable
{
    use HasCreatedBy;

    protected $table = 'group';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected array $jsons = [
        'name',
    ];

    protected array $translatables = [
        'name',
    ];

    protected $fillable = [
        'name',
        'society_id',
        'created_by',
    ];

    protected $hidden = [
        'society_id',
        'created_by',
    ];

    protected array $list_rows = [
        'name',
    ];

    public static function getURI(): string
    {
        return 'groups';
    }

    public static function getName(): string
    {
        return 'group';
    }

    public function getModuleName(): string
    {
        return 'groups';
    }

    public function society(): BelongsTo
    {
        return $this->belongsTo(Society::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'group_user');
    }
}
