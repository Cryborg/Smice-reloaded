<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\SelectedColor
 *
 * @property int $color_id
 * @property int $answer_id
 * @property-read \App\Models\Color $color
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SelectedColor whereAnswerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SelectedColor whereColorId($value)
 * @mixin \Eloquent
 */
class SelectedColor extends Model
{
    protected $table = 'selected_colors';

    protected $primaryKey = 'answer_id';

    public $timestamps = false;

    protected $fillable = ['color_id', 'answer_id'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function color()
    {
        return $this->belongsTo(Color::class, 'color_id');
    }

    protected static function boot()
    {
        parent::boot();
        self::updating(function(self $selected_colors) {
            if ($selected_colors->color_id == '') {
                $selected_colors->color_id = null;
            }
        });
    }
}
