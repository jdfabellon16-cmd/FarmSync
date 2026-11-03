<?php

namespace App\Models;

use Database\Factories\FieldFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Field extends Model
{
    /** @use HasFactory<FieldFactory> */
    use HasFactory, SoftDeletes;

    public const STATUSES = ['Fallow', 'Prepared', 'Planted', 'Growing', 'Harvested'];

    protected $fillable = [
        'name',
        'area_hectares',
        'soil_type',
        'irrigation_type',
        'status',
        'boundary',
        'latitude',
        'longitude',
    ];

    protected function casts(): array
    {
        return [
            'area_hectares' => 'decimal:4',
            'boundary' => 'array',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
        ];
    }

    public function farm(): BelongsTo
    {
        return $this->belongsTo(Farm::class);
    }
}
