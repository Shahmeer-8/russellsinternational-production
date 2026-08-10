<?php

namespace App\Support;

/**
 * Curated pickers for the admin. The site owner is not technical, so icons and
 * colours are always chosen from these lists — a raw Tailwind class or Lucide
 * name is never typed by hand.
 */
class AdminChoices
{
    /**
     * Lucide icon name => friendly label. Must stay in sync with ICON_MAP in
     * src/lib/icons.ts; LanguageSectionAdminTest enforces this.
     *
     * @return array<string, string>
     */
    public static function icons(): array
    {
        return [
            'Globe' => 'Globe',
            'Languages' => 'Translate',
            'BookOpenText' => 'Open book',
            'MessageCircle' => 'Speech bubble',
            'ScrollText' => 'Document',
            'Award' => 'Award',
            'GraduationCap' => 'Graduation cap',
            'Briefcase' => 'Briefcase',
            'Plane' => 'Aeroplane',
            'Sparkles' => 'Sparkles',
            'TrendingUp' => 'Upward trend',
            'Users' => 'People',
            'ShieldCheck' => 'Shield with tick',
            'Headphones' => 'Headphones',
            'Code' => 'Code',
            'Brain' => 'Brain',
            'Palette' => 'Palette',
            'Server' => 'Server',
            'Shield' => 'Shield',
        ];
    }

    /**
     * Tailwind class pair => colour name.
     *
     * @return array<string, string>
     */
    public static function colors(): array
    {
        return [
            'bg-blue-50 text-blue-600' => 'Blue',
            'bg-cyan-50 text-cyan-600' => 'Cyan',
            'bg-indigo-50 text-indigo-600' => 'Indigo',
            'bg-emerald-50 text-emerald-600' => 'Green',
            'bg-amber-50 text-amber-600' => 'Amber',
            'bg-yellow-50 text-yellow-700' => 'Yellow',
            'bg-orange-50 text-orange-600' => 'Orange',
            'bg-red-50 text-red-600' => 'Red',
            'bg-rose-50 text-rose-600' => 'Rose',
            'bg-purple-50 text-purple-600' => 'Purple',
            'bg-slate-100 text-slate-600' => 'Grey',
        ];
    }
}
