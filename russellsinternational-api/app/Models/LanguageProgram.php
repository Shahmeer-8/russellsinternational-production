<?php

namespace App\Models;

use App\Models\Concerns\NormalizesJsonLists;
use App\Support\Media;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LanguageProgram extends Model
{
    use NormalizesJsonLists;

    protected $fillable = [
        'language_section_id', 'flag_emoji', 'language_code', 'title', 'duration',
        'badge', 'description', 'benefits', 'color_class',
        'icon_name', 'image', 'sort_order', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'benefits' => 'array',
    ];

    protected $appends = ['image_url'];

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(LanguageSection::class, 'language_section_id');
    }

    public function getImageUrlAttribute(): ?string
    {
        return Media::url($this->image);
    }

    public function getBenefitsAttribute($value): array
    {
        return $this->normalizeList(json_decode($value ?? '[]', true));
    }
}
