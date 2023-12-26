<?php

namespace App\Models;

use App\Core\Enums\Status;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Tags\HasTags;

class Gallery extends Model
{
    use HasFactory, HasTags;

    protected $fillable = [
        'image',
        'title',
        'slug',
        'description',
        'category_id',
        'user_id',
        'status'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'status' => Status::class,
    ];

    /**
     * @return BelongsTo
     */
    public function category()
    : BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * @return BelongsTo
     */
    public function owner()
    : BelongsTo {
        return $this->belongsTo(User::class, 'user_id');
    }
}
