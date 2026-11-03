<?php

namespace App\Models;

use Database\Factories\FarmFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Farm extends Model
{
    /** @use HasFactory<FarmFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'location',
        'latitude',
        'longitude',
        'total_area_hectares',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'total_area_hectares' => 'decimal:4',
        ];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withPivot('role')->withTimestamps();
    }

    public function fields(): HasMany
    {
        return $this->hasMany(Field::class);
    }

    public function scopeAccessibleTo(Builder $query, User $user): Builder
    {
        if ($user->hasRole('Admin')) {
            return $query;
        }

        return $query->where(function (Builder $query) use ($user): void {
            $query->where('owner_id', $user->id)
                ->orWhereHas('members', fn (Builder $members) => $members->whereKey($user->id));
        });
    }
}
