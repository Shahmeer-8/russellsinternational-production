<?php

namespace Tests\Feature;

use App\Support\AdminChoices;
use Tests\TestCase;

/**
 * The icon and colour fields were free-text before, so live rows hold whatever was
 * typed. Turning them into pickers is only safe while every value already in the
 * database is offered as an option — otherwise Filament shows an empty select and
 * the owner's next save silently wipes the icon or colour.
 *
 * These lists were read from the production database. Add to them, never trim.
 */
class AdminChoicesCoverageTest extends TestCase
{
    /** Every icon_name found across services, stats, courses, why_choose_us_items, language_sections and language_programs. */
    private const ICONS_IN_USE = [
        'Award', 'BookOpen', 'BookOpenText', 'Brain', 'Briefcase', 'Code', 'Globe',
        'Globe2', 'GraduationCap', 'Headphones', 'Languages', 'Laptop',
        'MessageCircle', 'Palette', 'ScrollText', 'Server', 'Shield', 'ShieldCheck',
        'TrendingUp', 'Users',
    ];

    /** Every color_class found across the same tables. */
    private const COLOURS_IN_USE = [
        'bg-amber-50 text-amber-600',
        'bg-blue-50 text-blue-600',
        'bg-cyan-50 text-cyan-600',
        'bg-emerald-50 text-emerald-600',
        'bg-green-50 text-green-600',
        'bg-indigo-50 text-indigo-600',
        'bg-orange-50 text-orange-600',
        'bg-pink-50 text-pink-600',
        'bg-purple-50 text-purple-600',
        'bg-red-50 text-red-600',
        'bg-rose-50 text-rose-600',
        'bg-teal-50 text-teal-600',
        'bg-yellow-50 text-yellow-700',
    ];

    public function test_every_icon_already_in_the_database_is_offered_by_the_picker(): void
    {
        $offered = array_keys(AdminChoices::icons());

        foreach (self::ICONS_IN_USE as $icon) {
            $this->assertContains(
                $icon,
                $offered,
                "Icon [{$icon}] exists in live data but is not a picker option, so opening that record and saving would erase it."
            );
        }
    }

    public function test_every_colour_already_in_the_database_is_offered_by_the_picker(): void
    {
        $offered = array_keys(AdminChoices::colors());

        foreach (self::COLOURS_IN_USE as $colour) {
            $this->assertContains(
                $colour,
                $offered,
                "Colour [{$colour}] exists in live data but is not a picker option, so opening that record and saving would erase it."
            );
        }
    }

    public function test_the_frontend_registry_can_resolve_every_icon_the_picker_offers(): void
    {
        $registryPath = base_path('../src/lib/icons.ts');

        if (! is_file($registryPath)) {
            $this->markTestSkipped('The frontend registry lives beside the nested API copy only.');
        }

        $registry = (string) file_get_contents($registryPath);

        foreach (array_keys(AdminChoices::icons()) as $icon) {
            $this->assertMatchesRegularExpression(
                '/\b'.preg_quote($icon, '/').'\b/',
                $registry,
                "Icon [{$icon}] is offered in the admin but missing from src/lib/icons.ts, so it would render as the fallback."
            );
        }
    }

    public function test_every_colour_option_is_a_background_and_text_pair(): void
    {
        foreach (AdminChoices::colors() as $classes => $label) {
            $this->assertMatchesRegularExpression(
                '/^bg-[a-z]+-\d{2,3} text-[a-z]+-\d{2,3}$/',
                $classes,
                "Colour [{$label}] must be a 'bg-… text-…' pair or the frontend split produces broken classes."
            );
        }
    }

    public function test_colour_labels_are_unique_so_the_owner_can_tell_them_apart(): void
    {
        $labels = array_values(AdminChoices::colors());

        $this->assertSame(
            count($labels),
            count(array_unique($labels)),
            'Two colours share a label: '.implode(', ', array_diff_assoc($labels, array_unique($labels)))
        );
    }
}
