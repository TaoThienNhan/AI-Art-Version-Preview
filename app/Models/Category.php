<?php

namespace App\Models;

use App\Core\Enums\CategoryType;
use App\Core\Enums\Status;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Category extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'image',
        'description',
        'owner',
        'status',
        'type',
        'parent'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'status' => Status::class,
        'type' => CategoryType::class
    ];

    /**
     * @return BelongsTo
     */
    public function owner()
    : BelongsTo {
        return $this->belongsTo(User::class, 'owner', 'id');
    }
}
