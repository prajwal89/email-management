<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * App\Models\EmEmailVariant
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property int|null $sendable_id
 * @property string|null $sendable_type
 * @property bool $is_paused
 * @property bool $is_winner
 * @property int $exposure_percentage
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Model|\Eloquent $sendable
 */
class EmailVariant extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'em_email_variants';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'eventable_id',
        'eventable_type',
        'is_paused',
        'is_winner',
        'exposure_percentage',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_paused' => 'boolean',
        'is_winner' => 'boolean',
        'exposure_percentage' => 'integer',
    ];

    /**
     * Get the parent eventable model (it could be any model).
     */
    public function eventable(): MorphTo
    {
        return $this->morphTo();
    }
}
