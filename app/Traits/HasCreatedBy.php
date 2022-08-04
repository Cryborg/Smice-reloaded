<?php

namespace App\Traits;

use App\Http\User\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait HasCreatedBy
{
//    protected static function bootHasCreatedBy(): void
//    {
//        static::creating(
//            static function (Model $model) {
//                if (!isset($model->created_by)) {
//                    $model->created_by = auth()->id() ?? 1;
//                }
//            }
//        );
//    }

    /**
     * @return BelongsTo
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
