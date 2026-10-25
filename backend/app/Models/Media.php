<?php

namespace App\Models;

use Database\Factories\MediaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Media extends Model
{
    /** @use HasFactory<MediaFactory> */
    use HasFactory;

    /** Allowed values for the "type" column. */
    public const TYPES = ['film', 'series', 'youtube', 'music', 'anime', 'comic', 'book', 'other'];

    /** Allowed values for the "status" column. */
    public const STATUSES = ['planned', 'in_progress', 'completed', 'dropped'];

    /**
     * "media" is already plural, so the table name is set explicitly.
     */
    protected $table = 'media';

    /**
     * The attributes that are mass assignable.
     * "user_id" is not listed: it always comes from the logged-in user.
     *
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'type',
        'status',
        'rating',
        'notes',
        'consumed_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'rating' => 'integer',
            'consumed_at' => 'date:Y-m-d',
        ];
    }

    /**
     * The user who owns this media entry.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
