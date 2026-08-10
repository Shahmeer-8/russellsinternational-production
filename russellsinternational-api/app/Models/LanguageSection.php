<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class LanguageSection extends Model
{
    protected $fillable = [
        'slug', 'label', 'short_label', 'heading',
        'subtitle', 'icon_name', 'color_class', 'sort_order', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected $appends = ['tab_label'];

    /**
     * @return HasMany<LanguageProgram, $this>
     */
    public function programs(): HasMany
    {
        return $this->hasMany(LanguageProgram::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }

    /**
     * The mobile tab text. Falls back to the full label so the API always has a
     * usable value while the stored column stays blank for the admin field.
     */
    public function getTabLabelAttribute(): string
    {
        return filled($this->short_label) ? (string) $this->short_label : (string) $this->label;
    }

    protected static function booted(): void
    {
        static::saving(function (LanguageSection $section): void {
            if (filled($section->slug)) {
                return;
            }

            $base = Str::slug((string) $section->label) ?: 'section';
            $slug = $base;
            $suffix = 2;

            while (static::query()->where('slug', $slug)->whereKeyNot($section->getKey())->exists()) {
                $slug = "{$base}-{$suffix}";
                $suffix++;
            }

            $section->slug = $slug;
        });
    }
}
