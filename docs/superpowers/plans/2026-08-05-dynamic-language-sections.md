# Dynamic Language Sections Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Let the site owner add, reorder, hide and delete language sections (Arabic, French, …) on the Languages page from the admin panel, with no hardcoded content left in the frontend.

**Architecture:** A new `language_sections` table owns the tab label, heading, subtitle, icon, colour and order. `language_programs` gains a `language_section_id` foreign key and loses `language_code`. One API endpoint returns active sections with their active programs nested. `LanguagesSection.tsx` renders tabs from that response and drops its hardcoded `GROUPS`, `DEFAULT_PROGRAMS` and `normalizeGroup()`.

**Tech Stack:** Laravel 11 + Filament 3 (admin), MySQL (prod) / SQLite in-memory (tests), PHPUnit, PHPStan level 5, Laravel Pint; React 18 + TypeScript + Vite + TanStack Query + Tailwind + lucide-react (frontend), vitest.

**Spec:** `docs/superpowers/specs/2026-08-05-dynamic-language-sections-design.md`

## Global Constraints

- Follow existing conventions: Lucide icon name in `icon_name`, Tailwind class pair in `color_class`. Do **not** introduce `icon_key` / `color_key`.
- The admin never sees or types a Tailwind class. Colour is always a curated `Select` whose option values are class pairs.
- Every migration and data step is idempotent — running it twice must not duplicate or destroy data.
- The live Languages page must look identical after migration until the owner changes something.
- Backend commands run from `russellsinternational-api/`. Frontend commands run from the repo root.
- Backend tests: `php vendor/bin/phpunit`. Static analysis: `php vendor/bin/phpstan analyse --memory-limit=1G`. Formatting: `php vendor/bin/pint`.
- Frontend tests: `npx vitest run`.
- The existing backend suite (67 tests) must stay green and PHPStan must stay clean at every commit.
- Both codebases are kept in sync: after backend changes land in `russellsinternational/russellsinternational-api`, mirror the same files to the outer `russellinternational-api` copy (Task 10).
- Deploy is `git push production main`; Railway auto-deploys. Take a production DB backup before the deploy.

---

### Task 1: Shared icon registry

`ICON_MAP` is duplicated in `WhyChooseUs.tsx` and `FeaturedCourses.tsx` with different fallbacks. This feature needs a third copy, so consolidate first.

**Files:**
- Create: `src/lib/icons.ts`
- Create: `src/lib/icons.test.ts`
- Modify: `src/components/WhyChooseUs.tsx:1-13` (remove local `ICON_MAP`, import helper)
- Modify: `src/components/FeaturedCourses.tsx:21` (remove local `ICON_MAP`, import helper)

**Interfaces:**
- Consumes: nothing.
- Produces: `resolveIcon(name?: string | null, fallback?: ElementType): ElementType`, `ICON_MAP: Record<string, ElementType>`, `DEFAULT_ICON: ElementType` from `@/lib/icons`.

- [ ] **Step 1: Write the failing test**

Create `src/lib/icons.test.ts`:

```ts
import { describe, expect, it } from "vitest";
import { Award, Code, Globe } from "lucide-react";
import { DEFAULT_ICON, ICON_MAP, resolveIcon } from "@/lib/icons";

describe("resolveIcon", () => {
  it("returns the mapped icon for a known name", () => {
    expect(resolveIcon("Award")).toBe(Award);
  });

  it("falls back to the default icon for an unknown name", () => {
    expect(resolveIcon("NotARealIcon")).toBe(DEFAULT_ICON);
    expect(DEFAULT_ICON).toBe(Globe);
  });

  it("falls back for null, undefined and empty names", () => {
    expect(resolveIcon(null)).toBe(DEFAULT_ICON);
    expect(resolveIcon(undefined)).toBe(DEFAULT_ICON);
    expect(resolveIcon("")).toBe(DEFAULT_ICON);
  });

  it("honours a caller-supplied fallback so existing components keep their icon", () => {
    expect(resolveIcon("NotARealIcon", Code)).toBe(Code);
    expect(resolveIcon(null, Award)).toBe(Award);
  });

  it("exposes every icon the admin can pick", () => {
    for (const name of ["Award", "BookOpenText", "Code", "Globe", "Languages", "MessageCircle", "ScrollText"]) {
      expect(ICON_MAP[name], `${name} missing from ICON_MAP`).toBeTruthy();
    }
  });
});
```

- [ ] **Step 2: Run test to verify it fails**

Run: `npx vitest run src/lib/icons.test.ts`
Expected: FAIL — cannot resolve module `@/lib/icons`.

- [ ] **Step 3: Write minimal implementation**

Create `src/lib/icons.ts`:

```ts
import {
  Award,
  BookOpenText,
  Brain,
  Briefcase,
  Code,
  GraduationCap,
  Globe,
  Headphones,
  Languages,
  MessageCircle,
  Palette,
  Plane,
  ScrollText,
  Server,
  Shield,
  ShieldCheck,
  Sparkles,
  TrendingUp,
  Users,
} from "lucide-react";
import type { ElementType } from "react";

/**
 * Every icon the admin can choose, keyed by the Lucide name stored in
 * `icon_name`. Keep this in sync with LanguageSectionResource's icon picker.
 */
export const ICON_MAP: Record<string, ElementType> = {
  Award,
  BookOpenText,
  Brain,
  Briefcase,
  Code,
  GraduationCap,
  Globe,
  Headphones,
  Languages,
  MessageCircle,
  Palette,
  Plane,
  ScrollText,
  Server,
  Shield,
  ShieldCheck,
  Sparkles,
  TrendingUp,
  Users,
};

export const DEFAULT_ICON: ElementType = Globe;

/**
 * Resolve a stored icon name to a component. Unknown or missing names fall back
 * rather than throwing, so bad data can never blank out a page.
 */
export function resolveIcon(name?: string | null, fallback: ElementType = DEFAULT_ICON): ElementType {
  if (!name) {
    return fallback;
  }

  return ICON_MAP[name] ?? fallback;
}
```

- [ ] **Step 4: Run test to verify it passes**

Run: `npx vitest run src/lib/icons.test.ts`
Expected: PASS (5 tests).

- [ ] **Step 5: Point the two existing components at the shared registry**

In `src/components/WhyChooseUs.tsx`, delete the local `ICON_MAP` block and its lucide import line, then use the helper. The first lines become:

```tsx
import { Award } from "lucide-react";
import { useScrollReveal } from "@/hooks/useScrollReveal";
import { useWhyChooseUs } from "@/hooks/api";
import { resolveIcon } from "@/lib/icons";
```

and the mapping line becomes:

```tsx
    icon: resolveIcon(item.icon_name, Award),
```

In `src/components/FeaturedCourses.tsx`, delete the `const ICON_MAP = { ... }` line on line 21 and add the import:

```tsx
import { resolveIcon } from "@/lib/icons";
```

then change the mapping line to:

```tsx
    icon: resolveIcon(c.icon_name, Code),
```

Leave `Code` imported in `FeaturedCourses.tsx` and `Award` in `WhyChooseUs.tsx` — they are still used as fallbacks. Remove any lucide imports that are now unused, or `npx eslint` will flag them.

- [ ] **Step 6: Verify nothing regressed**

Run: `npx vitest run`
Expected: PASS, including the existing component tests.

Run: `npx tsc --noEmit`
Expected: no errors.

Run: `npx eslint src/lib/icons.ts src/components/WhyChooseUs.tsx src/components/FeaturedCourses.tsx`
Expected: no errors (no unused imports).

- [ ] **Step 7: Commit**

```bash
git add src/lib/icons.ts src/lib/icons.test.ts src/components/WhyChooseUs.tsx src/components/FeaturedCourses.tsx
git commit -m "Consolidate the duplicated icon maps into one registry"
```

---

### Task 2: `language_sections` table, model and seed

**Files:**
- Create: `russellsinternational-api/database/migrations/2026_08_05_000001_create_language_sections_table.php`
- Create: `russellsinternational-api/app/Models/LanguageSection.php`
- Create: `russellsinternational-api/database/seeders/LanguageSectionSeeder.php`
- Create: `russellsinternational-api/tests/Feature/LanguageSectionModelTest.php`

**Interfaces:**
- Consumes: nothing.
- Produces: `App\Models\LanguageSection` with `$fillable = ['slug','label','short_label','heading','subtitle','icon_name','color_class','sort_order','is_active']`, a `programs()` HasMany, a `scopeActive()`, and a `tab_label` accessor returning `short_label ?: label`. `Database\Seeders\LanguageSectionSeeder` seeds the three sections idempotently by `slug`.

- [ ] **Step 1: Write the failing test**

Create `russellsinternational-api/tests/Feature/LanguageSectionModelTest.php`:

```php
<?php

namespace Tests\Feature;

use App\Models\LanguageSection;
use Database\Seeders\LanguageSectionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LanguageSectionModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_slug_is_generated_from_the_label(): void
    {
        $section = LanguageSection::create([
            'label' => 'Arabic Tests',
            'heading' => 'Arabic Language & Exams',
        ]);

        $this->assertSame('arabic-tests', $section->slug);
    }

    public function test_duplicate_labels_get_distinct_slugs(): void
    {
        LanguageSection::create(['label' => 'Arabic Tests', 'heading' => 'One']);
        $second = LanguageSection::create(['label' => 'Arabic Tests', 'heading' => 'Two']);

        $this->assertSame('arabic-tests-2', $second->slug);
    }

    public function test_an_explicit_slug_is_kept(): void
    {
        $section = LanguageSection::create([
            'slug' => 'english',
            'label' => 'English Tests',
            'heading' => 'English Test Preparation',
        ]);

        $this->assertSame('english', $section->slug);
    }

    public function test_tab_label_falls_back_to_the_label_when_short_label_is_blank(): void
    {
        $withShort = LanguageSection::create(['label' => 'German Tests', 'short_label' => 'German', 'heading' => 'H']);
        $without = LanguageSection::create(['label' => 'Arabic Tests', 'heading' => 'H']);

        $this->assertSame('German', $withShort->tab_label);
        $this->assertSame('Arabic Tests', $without->tab_label);
        $this->assertNull($without->short_label, 'The raw column must stay blank so the admin field is honest.');
    }

    public function test_active_scope_hides_inactive_and_sorts_by_order(): void
    {
        LanguageSection::create(['label' => 'Zeta Scope', 'heading' => 'H', 'sort_order' => 30]);
        LanguageSection::create(['label' => 'Hidden Scope', 'heading' => 'H', 'sort_order' => 1, 'is_active' => false]);
        LanguageSection::create(['label' => 'Alpha Scope', 'heading' => 'H', 'sort_order' => 10]);

        // Scoped to this test's own records: the Task 3 migration seeds the three
        // real sections into every test database, so an exact whole-table
        // assertion here would be brittle.
        $labels = LanguageSection::active()
            ->get()
            ->pluck('label')
            ->filter(fn (string $label) => str_contains($label, 'Scope'))
            ->values()
            ->all();

        $this->assertSame(['Alpha Scope', 'Zeta Scope'], $labels);
    }

    public function test_seeder_creates_the_three_current_sections_and_is_idempotent(): void
    {
        (new LanguageSectionSeeder())->run();
        (new LanguageSectionSeeder())->run();

        $this->assertSame(3, LanguageSection::count());

        $english = LanguageSection::where('slug', 'english')->firstOrFail();
        $this->assertSame('English Tests', $english->label);
        $this->assertSame('English', $english->short_label);
        $this->assertSame('English Test Preparation', $english->heading);
        $this->assertSame('Languages', $english->icon_name);
        $this->assertSame(1, $english->sort_order);

        $this->assertSame(
            ['english', 'german', 'korean'],
            LanguageSection::active()->pluck('slug')->all()
        );
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php vendor/bin/phpunit --filter=LanguageSectionModelTest`
Expected: FAIL — `Class "App\Models\LanguageSection" not found`.

- [ ] **Step 3: Write the migration**

Create `russellsinternational-api/database/migrations/2026_08_05_000001_create_language_sections_table.php`:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('language_sections', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('label');                 // tab text on desktop
            $table->string('short_label')->nullable(); // tab text on mobile
            $table->string('heading');               // section heading under the tabs
            $table->text('subtitle')->nullable();
            $table->string('icon_name')->default('Globe');
            $table->string('color_class')->default('bg-blue-50 text-blue-600');
            $table->unsignedTinyInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('language_sections');
    }
};
```

- [ ] **Step 4: Write the model**

Create `russellsinternational-api/app/Models/LanguageSection.php`:

```php
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
        return filled($this->short_label) ? $this->short_label : (string) $this->label;
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
```

- [ ] **Step 5: Write the seeder**

Create `russellsinternational-api/database/seeders/LanguageSectionSeeder.php`. The values are copied verbatim from the `GROUPS` array currently in `src/components/LanguagesSection.tsx` so the live page does not change.

```php
<?php

namespace Database\Seeders;

use App\Models\LanguageSection;
use Illuminate\Database\Seeder;

class LanguageSectionSeeder extends Seeder
{
    /**
     * Mirrors the hardcoded GROUPS array the frontend used before sections became
     * editable. Keyed on slug so re-running never duplicates a section, and never
     * overwrites wording the owner has since edited.
     */
    public function run(): void
    {
        $sections = [
            [
                'slug' => 'english',
                'label' => 'English Tests',
                'short_label' => 'English',
                'heading' => 'English Test Preparation',
                'subtitle' => 'IELTS, PTE, LanguageCert and university-ready English coaching.',
                'icon_name' => 'Languages',
                'color_class' => 'bg-blue-50 text-blue-600',
                'sort_order' => 1,
            ],
            [
                'slug' => 'german',
                'label' => 'German Tests',
                'short_label' => 'German',
                'heading' => 'German Language & Exams',
                'subtitle' => 'A1 to B2 pathways plus Goethe, TestDaF and telc exam readiness.',
                'icon_name' => 'BookOpenText',
                'color_class' => 'bg-amber-50 text-amber-600',
                'sort_order' => 2,
            ],
            [
                'slug' => 'korean',
                'label' => 'Korean Tests',
                'short_label' => 'Korean',
                'heading' => 'Korean Language & EPS',
                'subtitle' => 'TOPIK, EPS-TOPIK and practical Korean for study or work.',
                'icon_name' => 'MessageCircle',
                'color_class' => 'bg-rose-50 text-rose-600',
                'sort_order' => 3,
            ],
        ];

        foreach ($sections as $section) {
            LanguageSection::query()->firstOrCreate(
                ['slug' => $section['slug']],
                $section
            );
        }
    }
}
```

- [ ] **Step 6: Run test to verify it passes**

Run: `php vendor/bin/phpunit --filter=LanguageSectionModelTest`
Expected: PASS (6 tests).

- [ ] **Step 7: Verify the whole suite and static analysis**

Run: `php vendor/bin/phpunit`
Expected: PASS — 67 existing tests plus the 6 new ones.

Run: `php vendor/bin/pint --dirty` then `php vendor/bin/phpstan analyse --memory-limit=1G`
Expected: no errors.

- [ ] **Step 8: Commit**

```bash
git add russellsinternational-api/database/migrations/2026_08_05_000001_create_language_sections_table.php russellsinternational-api/app/Models/LanguageSection.php russellsinternational-api/database/seeders/LanguageSectionSeeder.php russellsinternational-api/tests/Feature/LanguageSectionModelTest.php
git commit -m "Add language sections table, model and seed"
```

---

### Task 3: Link programs to sections and backfill

**Files:**
- Create: `russellsinternational-api/database/migrations/2026_08_05_000002_add_language_section_id_to_language_programs.php`
- Create: `russellsinternational-api/app/Support/LegacyLanguageCodeMap.php`
- Create: `russellsinternational-api/database/seeders/LanguageProgramSectionBackfillSeeder.php`
- Modify: `russellsinternational-api/app/Models/LanguageProgram.php` (add `icon_name` + `language_section_id` to `$fillable`, add `section()` relation)
- Create: `russellsinternational-api/tests/Feature/LanguageSectionBackfillTest.php`

**Interfaces:**
- Consumes: `App\Models\LanguageSection` and `LanguageSectionSeeder` from Task 2.
- Produces: `language_programs.language_section_id` (nullable FK, `nullOnDelete`), `language_programs.icon_name` (nullable), `LanguageProgram::section(): BelongsTo`, `LegacyLanguageCodeMap::slugFor(?string $code): string`, and `Database\Seeders\LanguageProgramSectionBackfillSeeder`.

`icon_name` is added here because the imported demo programs in Task 4 each had their own icon in the old hardcoded array, and `courses`/`services` already use this column name. When it is null the frontend falls back to the section's icon, which is exactly today's behaviour.

**Why the backfill is a seeder and the mapping is its own class.** Three constraints
force this shape, and burying the logic in the migration's `up()` violates all three:

- `RefreshDatabase` runs every migration before each test, so a test cannot re-run a
  migration to exercise it — `artisan migrate` only runs *pending* migrations and
  would be a silent no-op.
- Task 5 drops `language_code`, so any test that writes that column stops compiling
  the moment Task 5 lands. The mapping must be testable without the column.
- Seeding sections from inside a migration would leave all three sections present in
  every test, breaking assertions that count sections.

So: `LegacyLanguageCodeMap` is a pure function (testable forever),
`LanguageProgramSectionBackfillSeeder` is directly callable and idempotent, and the
migration only calls them. Section seeding stays out of this migration.

- [ ] **Step 1: Write the failing test**

Create `russellsinternational-api/tests/Feature/LanguageSectionBackfillTest.php`:

```php
<?php

namespace Tests\Feature;

use App\Models\LanguageProgram;
use App\Models\LanguageSection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class LanguageSectionBackfillTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_program_belongs_to_a_section(): void
    {
        $section = LanguageSection::create(['label' => 'Arabic Tests', 'heading' => 'H']);

        $program = LanguageProgram::create([
            'language_section_id' => $section->id,
            'flag_emoji' => 'SA',
            'title' => 'ALPT Preparation',
            'duration' => '8 Weeks',
            'badge' => 'New',
            'description' => 'Arabic proficiency coaching.',
            'benefits' => ['Reading', 'Writing'],
        ]);

        $this->assertSame($section->id, $program->section->id);
        $this->assertTrue($section->programs->contains($program));
    }

    public function test_deleting_a_section_nulls_the_foreign_key_rather_than_deleting_programs(): void
    {
        $section = LanguageSection::create(['label' => 'Arabic Tests', 'heading' => 'H']);
        $program = LanguageProgram::create([
            'language_section_id' => $section->id,
            'flag_emoji' => 'SA',
            'title' => 'ALPT Preparation',
            'duration' => '8 Weeks',
            'badge' => 'New',
            'description' => 'D',
            'benefits' => [],
        ]);

        $section->delete();

        $this->assertDatabaseHas('language_programs', ['id' => $program->id, 'language_section_id' => null]);
    }

    public function test_icon_name_is_optional(): void
    {
        $section = LanguageSection::create(['label' => 'Arabic Tests', 'heading' => 'H']);

        $program = LanguageProgram::create([
            'language_section_id' => $section->id,
            'flag_emoji' => 'SA',
            'title' => 'With Icon',
            'duration' => '8 Weeks',
            'badge' => 'New',
            'description' => 'D',
            'benefits' => [],
            'icon_name' => 'ScrollText',
        ]);

        $this->assertSame('ScrollText', $program->icon_name);
        $this->assertTrue(DB::getSchemaBuilder()->hasColumn('language_programs', 'icon_name'));
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php vendor/bin/phpunit --filter=LanguageSectionBackfillTest`
Expected: FAIL — no such column `language_section_id`.

- [ ] **Step 3: Write the migration**

Create `russellsinternational-api/database/migrations/2026_08_05_000002_add_language_section_id_to_language_programs.php`:

```php
<?php

use Database\Seeders\LanguageProgramSectionBackfillSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('language_programs', function (Blueprint $table) {
            if (! Schema::hasColumn('language_programs', 'language_section_id')) {
                $table->foreignId('language_section_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('language_sections')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('language_programs', 'icon_name')) {
                $table->string('icon_name')->nullable()->after('title');
            }
        });

        // Seeds the sections it needs, then maps each program onto one. Kept in a
        // seeder so tests can call it directly — re-running this migration is not
        // possible once it has been applied.
        (new LanguageProgramSectionBackfillSeeder())->run();
    }

    public function down(): void
    {
        Schema::table('language_programs', function (Blueprint $table) {
            if (Schema::hasColumn('language_programs', 'language_section_id')) {
                $table->dropConstrainedForeignId('language_section_id');
            }

            if (Schema::hasColumn('language_programs', 'icon_name')) {
                $table->dropColumn('icon_name');
            }
        });
    }
};
```

Create `russellsinternational-api/app/Support/LegacyLanguageCodeMap.php`:

```php
<?php

namespace App\Support;

/**
 * Maps the retired `language_code` values onto section slugs. `ielts`, `pte`,
 * `toefl` and `languagecert` fold into English because that is already how the
 * frontend grouped them before sections existed.
 *
 * Kept as a pure map so it stays testable after the column is dropped.
 */
class LegacyLanguageCodeMap
{
    private const CODE_TO_SLUG = [
        'english' => 'english',
        'ielts' => 'english',
        'pte' => 'english',
        'toefl' => 'english',
        'languagecert' => 'english',
        'german' => 'german',
        'goethe' => 'german',
        'testdaf' => 'german',
        'telc' => 'german',
        'korean' => 'korean',
        'topik' => 'korean',
        'eps-topik' => 'korean',
    ];

    public const FALLBACK_SLUG = 'english';

    /**
     * The section slug a legacy code belongs to. Unknown and blank codes fall back
     * to English rather than silently landing in Korean, which is what the old
     * frontend `normalizeGroup()` did.
     */
    public static function slugFor(?string $code): string
    {
        return self::CODE_TO_SLUG[strtolower(trim((string) $code))] ?? self::FALLBACK_SLUG;
    }

    /**
     * @return array<string, string>
     */
    public static function all(): array
    {
        return self::CODE_TO_SLUG;
    }
}
```

Create `russellsinternational-api/database/seeders/LanguageProgramSectionBackfillSeeder.php`:

```php
<?php

namespace Database\Seeders;

use App\Models\LanguageSection;
use App\Support\LegacyLanguageCodeMap;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LanguageProgramSectionBackfillSeeder extends Seeder
{
    /**
     * Points every program at a section. Idempotent: only rows with no section are
     * touched, so re-running cannot move content the owner has since re-filed.
     */
    public function run(): void
    {
        (new LanguageSectionSeeder())->run();

        $slugToId = LanguageSection::query()->pluck('id', 'slug');
        $fallbackId = $slugToId[LegacyLanguageCodeMap::FALLBACK_SLUG]
            ?? LanguageSection::query()->orderBy('sort_order')->value('id');

        if (! $fallbackId) {
            return;
        }

        $hasLegacyColumn = Schema::hasColumn('language_programs', 'language_code');
        $columns = $hasLegacyColumn ? ['id', 'language_code'] : ['id'];

        foreach (DB::table('language_programs')->whereNull('language_section_id')->get($columns) as $program) {
            $slug = $hasLegacyColumn
                ? LegacyLanguageCodeMap::slugFor($program->language_code)
                : LegacyLanguageCodeMap::FALLBACK_SLUG;

            DB::table('language_programs')
                ->where('id', $program->id)
                ->update(['language_section_id' => $slugToId[$slug] ?? $fallbackId]);
        }
    }
}
```

- [ ] **Step 4: Update the model**

In `russellsinternational-api/app/Models/LanguageProgram.php`, change `$fillable` to include the two new columns and add the relation. The `$fillable` array becomes:

```php
    protected $fillable = [
        'language_section_id', 'flag_emoji', 'language_code', 'title', 'duration',
        'badge', 'description', 'benefits', 'color_class',
        'icon_name', 'image', 'sort_order', 'is_active',
    ];
```

Add this import at the top:

```php
use Illuminate\Database\Eloquent\Relations\BelongsTo;
```

and this method after `scopeActive()`:

```php
    public function section(): BelongsTo
    {
        return $this->belongsTo(LanguageSection::class, 'language_section_id');
    }
```

`language_code` stays in `$fillable` until Task 5 drops the column.

- [ ] **Step 5: Run test to verify it passes**

Run: `php vendor/bin/phpunit --filter=LanguageSectionBackfillTest`
Expected: PASS (3 tests).

- [ ] **Step 6: Test the legacy mapping and the backfill seeder**

Add these tests to the same file. They target the pure map and the callable seeder
rather than re-running a migration, so they keep passing after Task 5 drops the
column.

```php
    public function test_legacy_codes_map_to_the_right_section_with_ielts_folded_into_english(): void
    {
        $this->assertSame('english', LegacyLanguageCodeMap::slugFor('english'));
        $this->assertSame('english', LegacyLanguageCodeMap::slugFor('ielts'));
        $this->assertSame('english', LegacyLanguageCodeMap::slugFor('IELTS'));
        $this->assertSame('english', LegacyLanguageCodeMap::slugFor(' pte '));
        $this->assertSame('german', LegacyLanguageCodeMap::slugFor('goethe'));
        $this->assertSame('korean', LegacyLanguageCodeMap::slugFor('eps-topik'));
    }

    public function test_an_unknown_or_blank_code_falls_back_to_english_not_korean(): void
    {
        // The old frontend normalizeGroup() dumped anything unrecognised into
        // Korean. English is the safe default and matches the fallback section.
        $this->assertSame('english', LegacyLanguageCodeMap::slugFor('arabic'));
        $this->assertSame('english', LegacyLanguageCodeMap::slugFor(''));
        $this->assertSame('english', LegacyLanguageCodeMap::slugFor(null));
    }

    public function test_the_backfill_seeder_assigns_every_program_a_section(): void
    {
        (new LanguageProgramSectionBackfillSeeder())->run();

        $english = LanguageSection::where('slug', 'english')->firstOrFail();

        // Created without a section, so the seeder must file it under the fallback.
        $orphan = LanguageProgram::create([
            'flag_emoji' => 'XX',
            'title' => 'Orphaned Program',
            'duration' => '8 Weeks',
            'badge' => 'B',
            'description' => 'D',
            'benefits' => [],
        ]);

        $this->assertNull($orphan->language_section_id);

        (new LanguageProgramSectionBackfillSeeder())->run();

        $this->assertSame($english->id, $orphan->refresh()->language_section_id);
        $this->assertSame(0, LanguageProgram::whereNull('language_section_id')->count());
    }

    public function test_the_backfill_seeder_never_refiles_a_program_that_already_has_a_section(): void
    {
        (new LanguageProgramSectionBackfillSeeder())->run();

        $korean = LanguageSection::where('slug', 'korean')->firstOrFail();
        $program = LanguageProgram::create([
            'language_section_id' => $korean->id,
            'flag_emoji' => 'KR',
            'title' => 'Deliberately Filed',
            'duration' => '8 Weeks',
            'badge' => 'B',
            'description' => 'D',
            'benefits' => [],
        ]);

        (new LanguageProgramSectionBackfillSeeder())->run();

        $this->assertSame($korean->id, $program->refresh()->language_section_id);
    }
```

Add these imports to the top of the test file:

```php
use App\Support\LegacyLanguageCodeMap;
use Database\Seeders\LanguageProgramSectionBackfillSeeder;
```

Run: `php vendor/bin/phpunit --filter=LanguageSectionBackfillTest`
Expected: PASS (7 tests).

- [ ] **Step 7: Verify the whole suite**

Run: `php vendor/bin/phpunit` then `php vendor/bin/pint --dirty` then `php vendor/bin/phpstan analyse --memory-limit=1G`
Expected: all green.

- [ ] **Step 8: Commit**

```bash
git add russellsinternational-api/database/migrations/2026_08_05_000002_add_language_section_id_to_language_programs.php russellsinternational-api/app/Models/LanguageProgram.php russellsinternational-api/tests/Feature/LanguageSectionBackfillTest.php
git commit -m "Link language programs to sections and backfill legacy codes"
```

---

### Task 4: Import the hardcoded demo programs and remove the lorem record

The frontend's `DEFAULT_PROGRAMS` array renders 8 cards the owner cannot edit. Import them as real records so nothing disappears when the array is deleted in Task 9, and drop the lorem test record that is live on the client's site.

**Files:**
- Create: `russellsinternational-api/database/seeders/LanguageProgramBackfillSeeder.php`
- Create: `russellsinternational-api/tests/Feature/LanguageProgramBackfillTest.php`

**Deliberately NOT a migration.** The original plan ran this import from a migration.
`RefreshDatabase` runs every migration before every test, so that would import 8
programs into every test database — and `ContentLifecycleTest:212` asserts
`data.0.title` on `/api/v1/language-programs` with a fixture at `sort_order = 1`,
which three imported programs also use. Ordering between equal `sort_order` values is
undefined, so the assertion would become flaky.

It is also unnecessary: after the lorem record is removed, English, German and Korean
each still hold one real program, so no tab goes empty and no content is lost when
`DEFAULT_PROGRAMS` is deleted in Task 9. The import is a convenience that hands the
owner 8 ready-made programs, so it runs once on production as an explicit step in
Task 10 rather than automatically everywhere.

**Interfaces:**
- Consumes: `LanguageSection`, `LanguageSectionSeeder` (Task 2); `language_section_id` and `icon_name` columns (Task 3).
- Produces: `Database\Seeders\LanguageProgramBackfillSeeder` — idempotent, matches on `(language_section_id, title)`.

- [ ] **Step 1: Write the failing test**

Create `russellsinternational-api/tests/Feature/LanguageProgramBackfillTest.php`:

```php
<?php

namespace Tests\Feature;

use App\Models\LanguageProgram;
use App\Models\LanguageSection;
use Database\Seeders\LanguageProgramBackfillSeeder;
use Database\Seeders\LanguageSectionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LanguageProgramBackfillTest extends TestCase
{
    use RefreshDatabase;

    private function seedSections(): void
    {
        (new LanguageSectionSeeder())->run();
    }

    public function test_it_imports_the_eight_former_hardcoded_programs(): void
    {
        $this->seedSections();
        (new LanguageProgramBackfillSeeder())->run();

        $this->assertSame(8, LanguageProgram::count());

        $english = LanguageSection::where('slug', 'english')->firstOrFail();
        $ielts = LanguageProgram::where('title', 'IELTS Preparation')->firstOrFail();

        $this->assertSame($english->id, $ielts->language_section_id);
        $this->assertSame('8 Weeks', $ielts->duration);
        $this->assertSame('Most Popular', $ielts->badge);
        $this->assertSame('Languages', $ielts->icon_name);
        $this->assertSame(
            ['Band score strategy', 'Writing task feedback', 'Speaking interview practice', 'Full-length mock exams'],
            $ielts->benefits
        );

        $this->assertSame(3, LanguageProgram::where('language_section_id', $english->id)->count());
        $this->assertSame(3, LanguageProgram::where('language_section_id', LanguageSection::where('slug', 'german')->value('id'))->count());
        $this->assertSame(2, LanguageProgram::where('language_section_id', LanguageSection::where('slug', 'korean')->value('id'))->count());
    }

    public function test_running_it_twice_creates_no_duplicates(): void
    {
        $this->seedSections();
        (new LanguageProgramBackfillSeeder())->run();
        (new LanguageProgramBackfillSeeder())->run();

        $this->assertSame(8, LanguageProgram::count());
        $this->assertSame(1, LanguageProgram::where('title', 'IELTS Preparation')->count());
    }

    public function test_it_never_overwrites_a_program_the_owner_already_edited(): void
    {
        $this->seedSections();
        $english = LanguageSection::where('slug', 'english')->firstOrFail();

        LanguageProgram::create([
            'language_section_id' => $english->id,
            'flag_emoji' => 'GB',
            'title' => 'IELTS Preparation',
            'duration' => 'OWNER EDITED',
            'badge' => 'Owner Badge',
            'description' => 'Owner wrote this.',
            'benefits' => ['Owner benefit'],
        ]);

        (new LanguageProgramBackfillSeeder())->run();

        $this->assertSame(1, LanguageProgram::where('title', 'IELTS Preparation')->count());
        $this->assertSame('OWNER EDITED', LanguageProgram::where('title', 'IELTS Preparation')->value('duration'));
    }

    public function test_it_removes_the_lorem_test_record(): void
    {
        $this->seedSections();
        $english = LanguageSection::where('slug', 'english')->firstOrFail();

        LanguageProgram::create([
            'language_section_id' => $english->id,
            'flag_emoji' => 'XX',
            'title' => 'Acton Kim',
            'duration' => 'Quam Nam cillum dolo',
            'badge' => 'Officiis enim labore',
            'description' => 'Qui unde mollit est',
            'benefits' => [],
        ]);

        (new LanguageProgramBackfillSeeder())->run();

        $this->assertSame(0, LanguageProgram::where('title', 'Acton Kim')->count());
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php vendor/bin/phpunit --filter=LanguageProgramBackfillTest`
Expected: FAIL — `Class "Database\Seeders\LanguageProgramBackfillSeeder" not found`.

- [ ] **Step 3: Write the seeder**

Create `russellsinternational-api/database/seeders/LanguageProgramBackfillSeeder.php`. Every value is copied verbatim from `DEFAULT_PROGRAMS` in `src/components/LanguagesSection.tsx:58-155`.

```php
<?php

namespace Database\Seeders;

use App\Models\LanguageProgram;
use App\Models\LanguageSection;
use Illuminate\Database\Seeder;

class LanguageProgramBackfillSeeder extends Seeder
{
    /**
     * Promotes the frontend's former DEFAULT_PROGRAMS fallback into real,
     * editable records so deleting that array changes nothing on the page.
     * Matches on (section, title) so it never duplicates or overwrites.
     */
    public function run(): void
    {
        // Lorem test data that reached the live site during earlier QA.
        LanguageProgram::query()->where('title', 'Acton Kim')->delete();

        foreach ($this->programs() as $slug => $programs) {
            $sectionId = LanguageSection::query()->where('slug', $slug)->value('id');

            if (! $sectionId) {
                continue;
            }

            foreach ($programs as $index => $program) {
                LanguageProgram::query()->firstOrCreate(
                    ['language_section_id' => $sectionId, 'title' => $program['title']],
                    $program + [
                        'language_section_id' => $sectionId,
                        'sort_order' => $index + 1,
                        'is_active' => true,
                    ]
                );
            }
        }
    }

    /**
     * @return array<string, array<int, array<string, mixed>>>
     */
    private function programs(): array
    {
        return [
            'english' => [
                [
                    'flag_emoji' => 'GB',
                    'title' => 'IELTS Preparation',
                    'duration' => '8 Weeks',
                    'badge' => 'Most Popular',
                    'description' => 'Complete coaching for listening, reading, writing and speaking with weekly mock tests.',
                    'benefits' => ['Band score strategy', 'Writing task feedback', 'Speaking interview practice', 'Full-length mock exams'],
                    'color_class' => 'bg-blue-50 text-blue-600',
                    'icon_name' => 'Languages',
                ],
                [
                    'flag_emoji' => 'GB',
                    'title' => 'PTE Academic',
                    'duration' => '6 Weeks',
                    'badge' => 'Fast Track',
                    'description' => 'Computer-based practice focused on scoring patterns, fluency, pronunciation and time control.',
                    'benefits' => ['AI-scored practice', 'Template drills', 'Speaking fluency sessions', 'Target-score roadmap'],
                    'color_class' => 'bg-cyan-50 text-cyan-600',
                    'icon_name' => 'ScrollText',
                ],
                [
                    'flag_emoji' => 'GB',
                    'title' => 'LanguageCert',
                    'duration' => '6 Weeks',
                    'badge' => 'Visa Ready',
                    'description' => 'Preparation for LanguageCert ESOL and SELT-style assessment routes.',
                    'benefits' => ['Exam format training', 'Grammar refreshers', 'Writing correction', 'Interview-style speaking'],
                    'color_class' => 'bg-indigo-50 text-indigo-600',
                    'icon_name' => 'Award',
                ],
            ],
            'german' => [
                [
                    'flag_emoji' => 'DE',
                    'title' => 'Goethe A1-B2',
                    'duration' => '12 Weeks per level',
                    'badge' => 'Visa Ready',
                    'description' => 'Goethe-aligned German classes for study, Ausbildung, family reunion and work pathways.',
                    'benefits' => ['A1 to B2 levels', 'Grammar and vocabulary labs', 'Model papers', 'Conversation practice'],
                    'color_class' => 'bg-amber-50 text-amber-600',
                    'icon_name' => 'BookOpenText',
                ],
                [
                    'flag_emoji' => 'DE',
                    'title' => 'TestDaF Preparation',
                    'duration' => '8 Weeks',
                    'badge' => 'University Track',
                    'description' => 'Academic German preparation for students targeting German university admission.',
                    'benefits' => ['Reading and listening drills', 'Academic writing', 'Speaking simulations', 'Timed practice tests'],
                    'color_class' => 'bg-red-50 text-red-600',
                    'icon_name' => 'ScrollText',
                ],
                [
                    'flag_emoji' => 'DE',
                    'title' => 'telc German',
                    'duration' => '8 Weeks',
                    'badge' => 'Exam Ready',
                    'description' => 'Structured telc preparation for everyday, professional and visa-focused German exams.',
                    'benefits' => ['Exam sections breakdown', 'Writing samples', 'Pair speaking practice', 'Level assessment'],
                    'color_class' => 'bg-yellow-50 text-yellow-700',
                    'icon_name' => 'Award',
                ],
            ],
            'korean' => [
                [
                    'flag_emoji' => 'KR',
                    'title' => 'TOPIK Preparation',
                    'duration' => '10 Weeks',
                    'badge' => 'Study Track',
                    'description' => 'From Hangul foundations to TOPIK I and II preparation for Korean study pathways.',
                    'benefits' => ['Hangul mastery', 'Vocabulary sets', 'Reading practice', 'Mock TOPIK papers'],
                    'color_class' => 'bg-rose-50 text-rose-600',
                    'icon_name' => 'MessageCircle',
                ],
                [
                    'flag_emoji' => 'KR',
                    'title' => 'EPS-TOPIK',
                    'duration' => '8 Weeks',
                    'badge' => 'EPS Ready',
                    'description' => 'Work-route Korean preparation with practical vocabulary and EPS-style question practice.',
                    'benefits' => ['Workplace vocabulary', 'Listening drills', 'EPS model tests', 'Application guidance'],
                    'color_class' => 'bg-emerald-50 text-emerald-600',
                    'icon_name' => 'Award',
                ],
            ],
        ];
    }
}
```

- [ ] **Step 4: Run test to verify it passes**

Run: `php vendor/bin/phpunit --filter=LanguageProgramBackfillTest`
Expected: PASS (4 tests).

- [ ] **Step 5: Confirm no migration imports this data**

Run: `grep -rn "LanguageProgramBackfillSeeder" russellsinternational-api/database/migrations/`
Expected: no matches. The seeder is invoked only by tests and by the explicit
production step in Task 10.

- [ ] **Step 6: Verify the whole suite**

Run: `php vendor/bin/phpunit` then `php vendor/bin/pint --dirty` then `php vendor/bin/phpstan analyse --memory-limit=1G`
Expected: all green.

- [ ] **Step 7: Commit**

```bash
git add russellsinternational-api/database/seeders/LanguageProgramBackfillSeeder.php russellsinternational-api/tests/Feature/LanguageProgramBackfillTest.php
git commit -m "Add a seeder that imports the hardcoded language programs"
```

---

### Task 5: Drop `language_code`

**Files:**
- Create: `russellsinternational-api/database/migrations/2026_08_05_000004_drop_language_code_from_language_programs.php`
- Modify: `russellsinternational-api/app/Models/LanguageProgram.php` (remove `language_code` from `$fillable`)
- Modify: `russellsinternational-api/app/Http/Controllers/Api/LanguageProgramController.php` (filter by section slug instead of legacy codes)
- Modify: `russellsinternational-api/tests/Feature/PublicApiTest.php` if it references `language_code` — grep first.

**Interfaces:**
- Consumes: `language_section_id` backfill (Task 3).
- Produces: `GET /api/v1/language-programs?section=<slug>` replaces `?code=<legacy>`.

- [ ] **Step 1: Find every remaining reference**

Run: `grep -rn "language_code" russellsinternational-api/app russellsinternational-api/tests russellsinternational-api/database src/`
Expected: hits in the model, the controller, `src/types/api.ts`, and `src/components/LanguagesSection.tsx`. The two frontend hits are handled in Task 9. Note the exact list before changing anything.

- [ ] **Step 2: Write the failing test**

Add to `russellsinternational-api/tests/Feature/LanguageSectionBackfillTest.php`:

```php
    public function test_language_code_column_is_gone_and_programs_filter_by_section_slug(): void
    {
        $this->assertFalse(
            \Illuminate\Support\Facades\DB::getSchemaBuilder()->hasColumn('language_programs', 'language_code'),
            'language_code must be dropped so the section relation is the only source of truth.'
        );

        (new \Database\Seeders\LanguageSectionSeeder())->run();
        (new \Database\Seeders\LanguageProgramBackfillSeeder())->run();

        $this->getJson('/api/v1/language-programs?section=german')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(3, 'data')
            ->assertJsonMissing(['title' => 'IELTS Preparation']);
    }
```

- [ ] **Step 3: Run test to verify it fails**

Run: `php vendor/bin/phpunit --filter=test_language_code_column_is_gone`
Expected: FAIL — the column still exists.

- [ ] **Step 4: Write the migration**

Create `russellsinternational-api/database/migrations/2026_08_05_000004_drop_language_code_from_language_programs.php`:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('language_programs', 'language_code')) {
            return;
        }

        Schema::table('language_programs', function (Blueprint $table) {
            $table->dropColumn('language_code');
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('language_programs', 'language_code')) {
            return;
        }

        Schema::table('language_programs', function (Blueprint $table) {
            $table->string('language_code')->default('english');
        });
    }
};
```

- [ ] **Step 5: Update the model and controller**

Remove `'language_code',` from `$fillable` in `app/Models/LanguageProgram.php`.

Replace the body of `index()` in `app/Http/Controllers/Api/LanguageProgramController.php`:

```php
    public function index(Request $request)
    {
        $query = LanguageProgram::active()->with('section');

        if ($request->filled('section')) {
            $query->whereHas('section', fn ($section) => $section->where('slug', $request->string('section')));
        }

        return response()->json(['success' => true, 'data' => $query->get()]);
    }
```

- [ ] **Step 6: Run test to verify it passes**

Run: `php vendor/bin/phpunit --filter=LanguageSectionBackfillTest`
Expected: PASS (5 tests).

- [ ] **Step 7: Verify the whole suite**

Run: `php vendor/bin/phpunit` then `php vendor/bin/pint --dirty` then `php vendor/bin/phpstan analyse --memory-limit=1G`
Expected: all green. If `PublicApiTest` fails on a `language_code` reference, update that fixture to set `language_section_id` instead.

- [ ] **Step 8: Commit**

```bash
git add russellsinternational-api/database/migrations/2026_08_05_000004_drop_language_code_from_language_programs.php russellsinternational-api/app/Models/LanguageProgram.php russellsinternational-api/app/Http/Controllers/Api/LanguageProgramController.php russellsinternational-api/tests/Feature/LanguageSectionBackfillTest.php
git commit -m "Drop language_code now that sections own the grouping"
```

---

### Task 6: `/api/v1/language-sections` endpoint

**Files:**
- Create: `russellsinternational-api/app/Http/Controllers/Api/LanguageSectionController.php`
- Modify: `russellsinternational-api/routes/api.php:53` (add the route beside the language-programs routes)
- Create: `russellsinternational-api/tests/Feature/LanguageSectionApiTest.php`

**Interfaces:**
- Consumes: `LanguageSection::active()`, `LanguageSection::programs()`, `LanguageProgram::active()`.
- Produces: `GET /api/v1/language-sections` → `{ success: true, data: LanguageSection[] }` where each section carries `id, slug, label, short_label, tab_label, heading, subtitle, icon_name, color_class, sort_order` and a nested `programs` array of `id, title, duration, badge, description, benefits, color_class, icon_name, image_url`.

- [ ] **Step 1: Write the failing test**

Create `russellsinternational-api/tests/Feature/LanguageSectionApiTest.php`:

```php
<?php

namespace Tests\Feature;

use App\Models\LanguageProgram;
use App\Models\LanguageSection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LanguageSectionApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // The Task 3 migration seeds the three real sections. They carry no
        // programs so the endpoint omits them, but clearing them keeps the slug
        // and count assertions below unambiguous.
        LanguageSection::query()->delete();
    }

    /**
     * The slug is passed explicitly: it is generated from the label otherwise, and
     * these tests assert on exact slugs.
     */
    private function section(array $overrides = []): LanguageSection
    {
        return LanguageSection::create($overrides + [
            'slug' => 'english',
            'label' => 'English Tests',
            'short_label' => 'English',
            'heading' => 'English Test Preparation',
            'subtitle' => 'IELTS, PTE and more.',
            'icon_name' => 'Languages',
            'color_class' => 'bg-blue-50 text-blue-600',
            'sort_order' => 1,
        ]);
    }

    private function program(LanguageSection $section, array $overrides = []): LanguageProgram
    {
        return LanguageProgram::create($overrides + [
            'language_section_id' => $section->id,
            'flag_emoji' => 'GB',
            'title' => 'IELTS Preparation',
            'duration' => '8 Weeks',
            'badge' => 'Most Popular',
            'description' => 'Complete coaching.',
            'benefits' => ['Band score strategy'],
            'color_class' => 'bg-blue-50 text-blue-600',
            'sort_order' => 1,
        ]);
    }

    public function test_it_returns_sections_with_their_programs_nested(): void
    {
        $english = $this->section();
        $this->program($english);

        $this->getJson('/api/v1/language-sections')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.slug', 'english')
            ->assertJsonPath('data.0.label', 'English Tests')
            ->assertJsonPath('data.0.tab_label', 'English')
            ->assertJsonPath('data.0.heading', 'English Test Preparation')
            ->assertJsonPath('data.0.icon_name', 'Languages')
            ->assertJsonPath('data.0.color_class', 'bg-blue-50 text-blue-600')
            ->assertJsonCount(1, 'data.0.programs')
            ->assertJsonPath('data.0.programs.0.title', 'IELTS Preparation')
            ->assertJsonPath('data.0.programs.0.benefits.0', 'Band score strategy');
    }

    public function test_sections_are_ordered_and_hidden_sections_are_excluded(): void
    {
        $third = $this->section(['slug' => 'korean', 'label' => 'Korean Tests', 'sort_order' => 30]);
        $hidden = $this->section(['slug' => 'hidden', 'label' => 'Hidden Tests', 'sort_order' => 1, 'is_active' => false]);
        $first = $this->section(['slug' => 'german', 'label' => 'German Tests', 'sort_order' => 10]);

        $this->program($third, ['title' => 'TOPIK']);
        $this->program($hidden, ['title' => 'Hidden Program']);
        $this->program($first, ['title' => 'Goethe']);

        $this->getJson('/api/v1/language-sections')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.label', 'German Tests')
            ->assertJsonPath('data.1.label', 'Korean Tests')
            ->assertJsonMissing(['label' => 'Hidden Tests']);
    }

    public function test_a_section_with_no_active_programs_is_omitted_so_visitors_never_see_an_empty_tab(): void
    {
        $withProgram = $this->section(['label' => 'English Tests', 'sort_order' => 1]);
        $this->program($withProgram);

        $empty = $this->section(['slug' => 'arabic', 'label' => 'Arabic Tests', 'sort_order' => 2]);
        $this->program($empty, ['title' => 'Inactive ALPT', 'is_active' => false]);

        $this->section(['slug' => 'french', 'label' => 'French Tests', 'sort_order' => 3]); // no programs at all

        $this->getJson('/api/v1/language-sections')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.label', 'English Tests');
    }

    public function test_inactive_programs_are_excluded_from_a_visible_section(): void
    {
        $english = $this->section();
        $this->program($english, ['title' => 'Visible', 'sort_order' => 2]);
        $this->program($english, ['title' => 'Hidden', 'is_active' => false, 'sort_order' => 1]);

        $this->getJson('/api/v1/language-sections')
            ->assertOk()
            ->assertJsonCount(1, 'data.0.programs')
            ->assertJsonPath('data.0.programs.0.title', 'Visible');
    }

    public function test_tab_label_falls_back_to_the_label_in_the_response(): void
    {
        $section = $this->section(['label' => 'Arabic Tests', 'short_label' => null]);
        $this->program($section, ['title' => 'ALPT']);

        $this->getJson('/api/v1/language-sections')
            ->assertOk()
            ->assertJsonPath('data.0.tab_label', 'Arabic Tests');
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php vendor/bin/phpunit --filter=LanguageSectionApiTest`
Expected: FAIL — 404, the route does not exist.

- [ ] **Step 3: Write the controller**

Create `russellsinternational-api/app/Http/Controllers/Api/LanguageSectionController.php`:

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LanguageSection;

class LanguageSectionController extends Controller
{
    /**
     * Active sections with their active programs nested — one request for the
     * whole Languages page. Sections with no active programs are omitted so a
     * visitor never lands on an empty tab.
     */
    public function index()
    {
        $sections = LanguageSection::active()
            ->with(['programs' => fn ($query) => $query->active()])
            ->get()
            ->filter(fn (LanguageSection $section) => $section->programs->isNotEmpty())
            ->values()
            ->map(fn (LanguageSection $section) => [
                'id' => $section->id,
                'slug' => $section->slug,
                'label' => $section->label,
                'short_label' => $section->short_label,
                'tab_label' => $section->tab_label,
                'heading' => $section->heading,
                'subtitle' => $section->subtitle,
                'icon_name' => $section->icon_name,
                'color_class' => $section->color_class,
                'sort_order' => $section->sort_order,
                'programs' => $section->programs->map(fn ($program) => [
                    'id' => $program->id,
                    'title' => $program->title,
                    'duration' => $program->duration,
                    'badge' => $program->badge,
                    'description' => $program->description,
                    'benefits' => $program->benefits,
                    'color_class' => $program->color_class,
                    'icon_name' => $program->icon_name,
                    'image_url' => $program->image_url,
                ])->values(),
            ]);

        return response()->json(['success' => true, 'data' => $sections]);
    }
}
```

- [ ] **Step 4: Register the route**

In `russellsinternational-api/routes/api.php`, add the import beside the other API controller imports:

```php
use App\Http\Controllers\Api\LanguageSectionController;
```

and add this line immediately above the existing `/language-programs` route (line 53):

```php
    Route::get('/language-sections', [LanguageSectionController::class,  'index']);
```

- [ ] **Step 5: Run test to verify it passes**

Run: `php vendor/bin/phpunit --filter=LanguageSectionApiTest`
Expected: PASS (5 tests).

- [ ] **Step 6: Verify the whole suite**

Run: `php vendor/bin/phpunit` then `php vendor/bin/pint --dirty` then `php vendor/bin/phpstan analyse --memory-limit=1G`
Expected: all green.

- [ ] **Step 7: Commit**

```bash
git add russellsinternational-api/app/Http/Controllers/Api/LanguageSectionController.php russellsinternational-api/routes/api.php russellsinternational-api/tests/Feature/LanguageSectionApiTest.php
git commit -m "Add the language-sections endpoint with nested programs"
```

---

### Task 7: Language Sections admin screen

**Files:**
- Create: `russellsinternational-api/app/Filament/Resources/LanguageSectionResource.php`
- Create: `russellsinternational-api/app/Filament/Resources/LanguageSectionResource/Pages/ListLanguageSections.php`
- Create: `russellsinternational-api/app/Filament/Resources/LanguageSectionResource/Pages/CreateLanguageSection.php`
- Create: `russellsinternational-api/app/Filament/Resources/LanguageSectionResource/Pages/EditLanguageSection.php`
- Create: `russellsinternational-api/app/Support/AdminChoices.php`
- Modify: `russellsinternational-api/app/Filament/Pages/LanguagePageContent.php:25-31` (add the sections card first in the list)
- Create: `russellsinternational-api/tests/Feature/LanguageSectionAdminTest.php`

**Interfaces:**
- Consumes: `App\Models\LanguageSection` (Task 2).
- Produces: `App\Support\AdminChoices::icons(): array<string,string>` (Lucide name → friendly label) and `AdminChoices::colors(): array<string,string>` (Tailwind class pair → colour name), reused by Task 8. `LanguageSectionResource` with routes `index`, `create`, `edit`.

The icon list must match `ICON_MAP` in `src/lib/icons.ts` from Task 1.

- [ ] **Step 1: Write the failing test**

Create `russellsinternational-api/tests/Feature/LanguageSectionAdminTest.php`:

```php
<?php

namespace Tests\Feature;

use App\Filament\Resources\LanguageSectionResource\Pages\CreateLanguageSection;
use App\Filament\Resources\LanguageSectionResource\Pages\EditLanguageSection;
use App\Filament\Resources\LanguageSectionResource\Pages\ListLanguageSections;
use App\Models\LanguageProgram;
use App\Models\LanguageSection;
use App\Models\User;
use App\Support\AdminChoices;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class LanguageSectionAdminTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::create([
            'name' => 'QA Admin',
            'email' => 'qa-language-admin@example.com',
            'password' => 'password',
        ]));
    }

    public function test_the_list_page_renders(): void
    {
        LanguageSection::create(['label' => 'English Tests', 'heading' => 'H']);

        Livewire::test(ListLanguageSections::class)->assertSuccessful();
    }

    public function test_an_owner_can_create_a_new_language_section(): void
    {
        Livewire::test(CreateLanguageSection::class)
            ->fillForm([
                'label' => 'Arabic Tests',
                'short_label' => 'Arabic',
                'heading' => 'Arabic Language & Exams',
                'subtitle' => 'ALPT and practical Arabic for work and study.',
                'icon_name' => 'Globe',
                'color_class' => 'bg-emerald-50 text-emerald-600',
                'sort_order' => 4,
                'is_active' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $section = LanguageSection::where('label', 'Arabic Tests')->first();

        $this->assertNotNull($section, 'The owner could not create a language section.');
        $this->assertSame('arabic-tests', $section->slug);
        $this->assertSame('Arabic', $section->short_label);
        $this->assertSame('Globe', $section->icon_name);
        $this->assertSame('bg-emerald-50 text-emerald-600', $section->color_class);
    }

    public function test_a_section_can_be_created_without_a_short_label(): void
    {
        Livewire::test(CreateLanguageSection::class)
            ->fillForm([
                'label' => 'French Tests',
                'heading' => 'French Language & Exams',
                'icon_name' => 'Globe',
                'color_class' => 'bg-blue-50 text-blue-600',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $section = LanguageSection::where('label', 'French Tests')->firstOrFail();

        $this->assertNull($section->short_label);
        $this->assertSame('French Tests', $section->tab_label);
    }

    public function test_label_and_heading_are_required(): void
    {
        Livewire::test(CreateLanguageSection::class)
            ->fillForm(['label' => '', 'heading' => ''])
            ->call('create')
            ->assertHasFormErrors(['label' => 'required', 'heading' => 'required']);
    }

    public function test_a_section_can_be_edited(): void
    {
        $section = LanguageSection::create(['label' => 'Arabic Tests', 'heading' => 'Old heading']);

        Livewire::test(EditLanguageSection::class, ['record' => $section->getKey()])
            ->fillForm(['heading' => 'New heading'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('New heading', $section->refresh()->heading);
    }

    public function test_an_empty_section_can_be_deleted(): void
    {
        $section = LanguageSection::create(['label' => 'Unused Tests', 'heading' => 'H']);

        Livewire::test(EditLanguageSection::class, ['record' => $section->getKey()])
            ->callAction('delete');

        $this->assertModelMissing($section);
    }

    public function test_deleting_a_section_that_still_has_programs_is_blocked(): void
    {
        $section = LanguageSection::create(['label' => 'English Tests', 'heading' => 'H']);
        LanguageProgram::create([
            'language_section_id' => $section->id,
            'flag_emoji' => 'GB',
            'title' => 'IELTS Preparation',
            'duration' => '8 Weeks',
            'badge' => 'B',
            'description' => 'D',
            'benefits' => [],
        ]);

        Livewire::test(EditLanguageSection::class, ['record' => $section->getKey()])
            ->callAction('delete');

        $this->assertModelExists($section);
        $this->assertDatabaseHas('language_programs', ['title' => 'IELTS Preparation', 'language_section_id' => $section->id]);
    }

    public function test_the_icon_choices_match_the_frontend_registry(): void
    {
        $registry = file_get_contents(base_path('../src/lib/icons.ts'));

        $this->assertNotFalse($registry, 'src/lib/icons.ts must be readable to keep the two lists in sync.');

        foreach (array_keys(AdminChoices::icons()) as $icon) {
            $this->assertStringContainsString(
                $icon,
                $registry,
                "Icon [{$icon}] is offered in the admin but missing from src/lib/icons.ts."
            );
        }
    }

    public function test_every_colour_choice_is_a_tailwind_background_and_text_pair(): void
    {
        foreach (AdminChoices::colors() as $classes => $label) {
            $this->assertMatchesRegularExpression(
                '/^bg-[a-z]+-\d{2,3} text-[a-z]+-\d{2,3}$/',
                $classes,
                "Colour [{$label}] must be a 'bg-… text-…' pair."
            );
        }
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php vendor/bin/phpunit --filter=LanguageSectionAdminTest`
Expected: FAIL — `Class "App\Support\AdminChoices" not found`.

- [ ] **Step 3: Write the shared choice lists**

Create `russellsinternational-api/app/Support/AdminChoices.php`:

```php
<?php

namespace App\Support;

/**
 * Curated pickers for the admin. The owner is not technical, so icons and
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
```

- [ ] **Step 4: Write the resource**

Create `russellsinternational-api/app/Filament/Resources/LanguageSectionResource.php`:

```php
<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LanguageSectionResource\Pages;
use App\Models\LanguageSection;
use App\Support\AdminChoices;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class LanguageSectionResource extends Resource
{
    protected static ?string $model = LanguageSection::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-group';

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $navigationGroup = 'Languages';

    protected static ?string $navigationLabel = 'Language Sections';

    protected static ?string $modelLabel = 'language section';

    protected static ?int $navigationSort = 0;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Tab')->schema([
                Forms\Components\TextInput::make('label')
                    ->label('Tab name')
                    ->helperText('The tab text on the Languages page, e.g. "Arabic Tests".')
                    ->required()
                    ->maxLength(60),
                Forms\Components\TextInput::make('short_label')
                    ->label('Short tab name (mobile)')
                    ->helperText('Shorter text used on small screens. Leave blank to reuse the tab name.')
                    ->maxLength(30),
            ])->columns(2),

            Forms\Components\Section::make('Section heading')->schema([
                Forms\Components\TextInput::make('heading')
                    ->label('Heading')
                    ->helperText('The large heading shown under the tabs, e.g. "Arabic Language & Exams".')
                    ->required()
                    ->maxLength(120),
                Forms\Components\Textarea::make('subtitle')
                    ->label('Short description')
                    ->helperText('One line under the heading. Optional.')
                    ->rows(2)
                    ->maxLength(300),
            ])->columns(1),

            Forms\Components\Section::make('Look and order')->schema([
                Forms\Components\Select::make('icon_name')
                    ->label('Icon')
                    ->helperText('Shown on the tab and on this section\'s cards.')
                    ->options(AdminChoices::icons())
                    ->default('Globe')
                    ->searchable()
                    ->required(),
                Forms\Components\Select::make('color_class')
                    ->label('Colour')
                    ->helperText('Background colour of the icon badge.')
                    ->options(AdminChoices::colors())
                    ->default('bg-blue-50 text-blue-600')
                    ->required(),
                Forms\Components\TextInput::make('sort_order')
                    ->label('Order')
                    ->helperText('Lower numbers appear first.')
                    ->numeric()->minValue(0)->maxValue(255)->default(0),
                Forms\Components\Toggle::make('is_active')
                    ->label('Visible on website')
                    ->helperText('Turn off to hide this tab without deleting it.')
                    ->default(true),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->reorderable('sort_order')
            ->defaultSort('sort_order')
            ->columns([
                Tables\Columns\TextColumn::make('label')->label('Tab')->searchable(),
                Tables\Columns\TextColumn::make('heading')->limit(40)->searchable(),
                Tables\Columns\TextColumn::make('icon_name')->label('Icon')->badge(),
                Tables\Columns\TextColumn::make('color_class')
                    ->label('Colour')
                    ->formatStateUsing(fn (?string $state) => AdminChoices::colors()[$state] ?? $state),
                Tables\Columns\TextColumn::make('programs_count')
                    ->label('Programs')
                    ->counts('programs')
                    ->badge()
                    ->color(fn (int $state) => $state > 0 ? 'success' : 'warning')
                    ->tooltip('A section with no programs is hidden from the website.'),
                Tables\Columns\TextColumn::make('sort_order')->label('Order')->sortable(),
                Tables\Columns\ToggleColumn::make('is_active')->label('Visible'),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')->label('Visible'),
            ])
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLanguageSections::route('/'),
            'create' => Pages\CreateLanguageSection::route('/create'),
            'edit' => Pages\EditLanguageSection::route('/{record}/edit'),
        ];
    }
}
```

- [ ] **Step 5: Write the three page classes with the delete guardrail**

Create `russellsinternational-api/app/Filament/Resources/LanguageSectionResource/Pages/ListLanguageSections.php`:

```php
<?php

namespace App\Filament\Resources\LanguageSectionResource\Pages;

use App\Filament\Resources\LanguageSectionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLanguageSections extends ListRecords
{
    protected static string $resource = LanguageSectionResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()->label('New section')];
    }
}
```

Create `russellsinternational-api/app/Filament/Resources/LanguageSectionResource/Pages/CreateLanguageSection.php`:

```php
<?php

namespace App\Filament\Resources\LanguageSectionResource\Pages;

use App\Filament\Resources\LanguageSectionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateLanguageSection extends CreateRecord
{
    protected static string $resource = LanguageSectionResource::class;
}
```

Create `russellsinternational-api/app/Filament/Resources/LanguageSectionResource/Pages/EditLanguageSection.php`:

```php
<?php

namespace App\Filament\Resources\LanguageSectionResource\Pages;

use App\Filament\Resources\LanguageSectionResource;
use App\Models\LanguageSection;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditLanguageSection extends EditRecord
{
    protected static string $resource = LanguageSectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                // The database nulls the foreign key on delete, which would drop
                // the programs off the website with no warning. Refuse instead and
                // tell the owner exactly what to do.
                ->before(function (Actions\DeleteAction $action, LanguageSection $record) {
                    $count = $record->programs()->count();

                    if ($count === 0) {
                        return;
                    }

                    Notification::make()
                        ->danger()
                        ->title('This section still has programs')
                        ->body("Move or delete this section's {$count} program(s) first, otherwise they would disappear from the website.")
                        ->persistent()
                        ->send();

                    $action->cancel();
                }),
        ];
    }
}
```

- [ ] **Step 6: Add the card to the Language Page hub**

In `russellsinternational-api/app/Filament/Pages/LanguagePageContent.php`, add the import:

```php
use App\Filament\Resources\LanguageSectionResource;
```

and insert this entry into the `sections` array immediately before the existing `resourceList(...)` line for programs:

```php
                    $this->resourceList('Language sections (tabs)', 'The tabs on the Languages page. Add a section here to offer a new language.', LanguageSectionResource::class, 'Manage Sections'),
```

- [ ] **Step 7: Run test to verify it passes**

Run: `php vendor/bin/phpunit --filter=LanguageSectionAdminTest`
Expected: PASS (9 tests).

- [ ] **Step 8: Verify the whole suite**

Run: `php vendor/bin/phpunit` then `php vendor/bin/pint --dirty` then `php vendor/bin/phpstan analyse --memory-limit=1G`
Expected: all green. `AdminPageHubTest` covers the hubs — if it asserts a card count for the Language page, update that expectation.

- [ ] **Step 9: Commit**

```bash
git add russellsinternational-api/app/Support/AdminChoices.php russellsinternational-api/app/Filament/Resources/LanguageSectionResource.php russellsinternational-api/app/Filament/Resources/LanguageSectionResource russellsinternational-api/app/Filament/Pages/LanguagePageContent.php russellsinternational-api/tests/Feature/LanguageSectionAdminTest.php
git commit -m "Add the Language Sections admin screen with a delete guardrail"
```

---

### Task 8: Point the program form at sections and replace its Tailwind input

**Files:**
- Modify: `russellsinternational-api/app/Filament/Resources/LanguageProgramResource.php:28-96`
- Create: `russellsinternational-api/tests/Feature/LanguageProgramAdminTest.php`

**Interfaces:**
- Consumes: `AdminChoices::icons()`, `AdminChoices::colors()` (Task 7); `LanguageProgram::section()` (Task 3).
- Produces: no new interface.

- [ ] **Step 1: Write the failing test**

Create `russellsinternational-api/tests/Feature/LanguageProgramAdminTest.php`:

```php
<?php

namespace Tests\Feature;

use App\Filament\Resources\LanguageProgramResource\Pages\CreateLanguageProgram;
use App\Filament\Resources\LanguageProgramResource\Pages\ListLanguagePrograms;
use App\Models\LanguageProgram;
use App\Models\LanguageSection;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class LanguageProgramAdminTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::create([
            'name' => 'QA Admin',
            'email' => 'qa-program-admin@example.com',
            'password' => 'password',
        ]));
    }

    public function test_the_list_page_renders(): void
    {
        Livewire::test(ListLanguagePrograms::class)->assertSuccessful();
    }

    public function test_a_program_is_created_against_a_section(): void
    {
        $section = LanguageSection::create(['label' => 'Arabic Tests', 'heading' => 'H']);

        Livewire::test(CreateLanguageProgram::class)
            ->fillForm([
                'language_section_id' => $section->id,
                'flag_emoji' => 'SA',
                'title' => 'ALPT Preparation',
                'duration' => '8 Weeks',
                'badge' => 'New',
                'description' => 'Arabic proficiency coaching.',
                'color_class' => 'bg-emerald-50 text-emerald-600',
                'icon_name' => 'Globe',
                'benefits' => [['item' => 'Reading practice']],
                'sort_order' => 1,
                'is_active' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $program = LanguageProgram::where('title', 'ALPT Preparation')->firstOrFail();

        $this->assertSame($section->id, $program->language_section_id);
        $this->assertSame('bg-emerald-50 text-emerald-600', $program->color_class);
        $this->assertSame('Globe', $program->icon_name);
        $this->assertSame(['Reading practice'], $program->benefits);
    }

    public function test_a_section_is_required(): void
    {
        Livewire::test(CreateLanguageProgram::class)
            ->fillForm([
                'flag_emoji' => 'SA',
                'title' => 'Orphan Program',
                'duration' => '8 Weeks',
                'badge' => 'New',
                'description' => 'D',
            ])
            ->call('create')
            ->assertHasFormErrors(['language_section_id' => 'required']);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php vendor/bin/phpunit --filter=LanguageProgramAdminTest`
Expected: FAIL — the form has no `language_section_id` field.

- [ ] **Step 3: Update the resource form**

In `russellsinternational-api/app/Filament/Resources/LanguageProgramResource.php`, add the import:

```php
use App\Support\AdminChoices;
```

Replace the `Select::make('language_code')` block with:

```php
            Forms\Components\Select::make('language_section_id')
                ->label('Language section')
                ->helperText('Which tab on the Languages page this program appears under.')
                ->relationship('section', 'label')
                ->searchable()
                ->preload()
                ->required(),
```

Replace `Forms\Components\TextInput::make('color_class')->default('bg-blue-50 text-blue-600'),` with:

```php
            Forms\Components\Select::make('color_class')
                ->label('Colour')
                ->helperText('Background colour of this card\'s icon badge.')
                ->options(AdminChoices::colors())
                ->default('bg-blue-50 text-blue-600'),
            Forms\Components\Select::make('icon_name')
                ->label('Icon')
                ->helperText('Leave blank to reuse the section\'s icon.')
                ->options(AdminChoices::icons())
                ->searchable(),
```

`flag_emoji` is **verified unused** for language programs — the only frontend reader
of a `flag_emoji` is `StudyDestinations.tsx`, which uses the `StudyDestination`
model's own column. Relabel it so the owner stops treating it as visible content:

```php
            Forms\Components\TextInput::make('flag_emoji')
                ->label('Internal short code')
                ->helperText('For your own reference in this list only. Not shown on the website.')
                ->required()
                ->placeholder('GB'),
```

- [ ] **Step 4: Update the table columns and filter**

Replace the `language_code` column and the `SelectFilter` with section-based equivalents:

```php
                Tables\Columns\TextColumn::make('section.label')->label('Section')->badge()->sortable(),
```

```php
            ->filters([
                Tables\Filters\SelectFilter::make('language_section_id')
                    ->label('Section')
                    ->relationship('section', 'label'),
            ])
```

- [ ] **Step 5: Run test to verify it passes**

Run: `php vendor/bin/phpunit --filter=LanguageProgramAdminTest`
Expected: PASS (3 tests).

- [ ] **Step 6: Verify the whole suite**

Run: `php vendor/bin/phpunit` then `php vendor/bin/pint --dirty` then `php vendor/bin/phpstan analyse --memory-limit=1G`
Expected: all green. `FilamentResourceCrudTest` likely creates a language program with `language_code` — update that fixture to `language_section_id`.

- [ ] **Step 7: Commit**

```bash
git add russellsinternational-api/app/Filament/Resources/LanguageProgramResource.php russellsinternational-api/tests/Feature/LanguageProgramAdminTest.php
git commit -m "Point the language program form at sections and replace its Tailwind input"
```

---

### Task 9: Render tabs from the API with a swipeable strip

**Files:**
- Modify: `src/types/api.ts:107-118` (replace `language_code` with section-aware types)
- Modify: `src/hooks/api/index.ts:91-99` (add `useLanguageSections`)
- Modify: `src/components/LanguagesSection.tsx` (full rewrite of the data layer and tab strip)
- Create: `src/components/LanguagesSection.test.tsx`

**Interfaces:**
- Consumes: `GET /api/v1/language-sections` (Task 6); `resolveIcon` (Task 1).
- Produces: `LanguageSection` and `LanguageProgram` types, `useLanguageSections()` hook.

- [ ] **Step 1: Add the types**

In `src/types/api.ts`, replace the `LanguageProgram` interface with:

```ts
export interface LanguageProgram {
  id: number;
  title: string;
  duration: string;
  badge: string;
  description: string;
  benefits: string[];
  color_class: string;
  icon_name: string | null;
  image_url: string | null;
}

export interface LanguageSection {
  id: number;
  slug: string;
  label: string;
  short_label: string | null;
  tab_label: string;
  heading: string;
  subtitle: string | null;
  icon_name: string;
  color_class: string;
  sort_order: number;
  programs: LanguageProgram[];
}
```

- [ ] **Step 2: Add the hook**

In `src/hooks/api/index.ts`, add `LanguageSection` to the type import from `@/types/api`, then add below `useLanguagePrograms`:

```ts
export function useLanguageSections() {
  return useQuery({
    queryKey: ['language-sections'],
    queryFn: () => api.get<ApiResponse<LanguageSection[]>>('/language-sections'),
    staleTime: 10 * 60 * 1000,
  });
}
```

- [ ] **Step 3: Write the failing test**

Create `src/components/LanguagesSection.test.tsx`:

```tsx
import { describe, expect, it, vi } from "vitest";
import { render, screen } from "@testing-library/react";
import LanguagesSection from "@/components/LanguagesSection";
import type { LanguageSection } from "@/types/api";

const mockUseLanguageSections = vi.fn();

vi.mock("@/hooks/api", () => ({
  useLanguageSections: () => mockUseLanguageSections(),
}));

vi.mock("@/hooks/useScrollReveal", () => ({
  useScrollReveal: () => ({ ref: { current: null }, visible: true }),
}));

function section(overrides: Partial<LanguageSection> = {}): LanguageSection {
  return {
    id: 1,
    slug: "english",
    label: "English Tests",
    short_label: "English",
    tab_label: "English",
    heading: "English Test Preparation",
    subtitle: "IELTS, PTE and more.",
    icon_name: "Languages",
    color_class: "bg-blue-50 text-blue-600",
    sort_order: 1,
    programs: [
      {
        id: 10,
        title: "IELTS Preparation",
        duration: "8 Weeks",
        badge: "Most Popular",
        description: "Complete coaching.",
        benefits: ["Band score strategy"],
        color_class: "bg-blue-50 text-blue-600",
        icon_name: null,
        image_url: null,
      },
    ],
    ...overrides,
  };
}

describe("LanguagesSection", () => {
  it("renders one tab per section from the API", () => {
    mockUseLanguageSections.mockReturnValue({
      data: {
        data: [
          section(),
          section({ id: 2, slug: "arabic", label: "Arabic Tests", tab_label: "Arabic", heading: "Arabic Language & Exams" }),
        ],
      },
      isLoading: false,
    });

    render(<LanguagesSection />);

    expect(screen.getByRole("tab", { name: /English/ })).toBeTruthy();
    expect(screen.getByRole("tab", { name: /Arabic/ })).toBeTruthy();
  });

  it("shows the active section's heading, subtitle and cards", () => {
    mockUseLanguageSections.mockReturnValue({ data: { data: [section()] }, isLoading: false });

    render(<LanguagesSection />);

    expect(screen.getByText("English Test Preparation")).toBeTruthy();
    expect(screen.getByText("IELTS, PTE and more.")).toBeTruthy();
    expect(screen.getByText("IELTS Preparation")).toBeTruthy();
  });

  it("renders nothing when there are no sections instead of an empty shell", () => {
    mockUseLanguageSections.mockReturnValue({ data: { data: [] }, isLoading: false });

    const { container } = render(<LanguagesSection />);

    expect(container.querySelector("section")).toBeNull();
  });

  it("does not fall back to hardcoded demo programs", () => {
    mockUseLanguageSections.mockReturnValue({ data: { data: [] }, isLoading: false });

    render(<LanguagesSection />);

    expect(screen.queryByText("PTE Academic")).toBeNull();
    expect(screen.queryByText("Goethe A1-B2")).toBeNull();
  });
});
```

- [ ] **Step 4: Run test to verify it fails**

Run: `npx vitest run src/components/LanguagesSection.test.tsx`
Expected: FAIL — `useLanguageSections` is not exported / no `tab` roles found.

- [ ] **Step 5: Rewrite the component**

Replace the whole of `src/components/LanguagesSection.tsx` with the version below. `GROUPS`, `DEFAULT_PROGRAMS` and `normalizeGroup` are gone.

```tsx
import { useEffect, useMemo, useRef, useState } from "react";
import { ArrowRight, Award, Clock } from "lucide-react";
import { useScrollReveal } from "@/hooks/useScrollReveal";
import DetailDrawer from "@/components/DetailDrawer";
import { useLanguageSections } from "@/hooks/api";
import { resolveIcon } from "@/lib/icons";
import type { LanguageProgram, LanguageSection } from "@/types/api";

const LanguagesSection = () => {
  const { ref, visible } = useScrollReveal();
  const { data, isLoading } = useLanguageSections();
  const [activeSlug, setActiveSlug] = useState<string | null>(null);
  const [drawerOpen, setDrawerOpen] = useState(false);
  const [selected, setSelected] = useState<LanguageProgram | null>(null);
  const tabRefs = useRef<Record<string, HTMLButtonElement | null>>({});

  const sections = useMemo<LanguageSection[]>(() => data?.data ?? [], [data?.data]);
  const active = sections.find((section) => section.slug === activeSlug) ?? sections[0];

  // Keep the selected tab visible when the strip scrolls horizontally.
  useEffect(() => {
    if (!active) return;
    tabRefs.current[active.slug]?.scrollIntoView({ block: "nearest", inline: "center", behavior: "smooth" });
  }, [active]);

  if (isLoading) {
    return (
      <section className="py-20 md:py-28">
        <div className="container mx-auto px-4 md:px-8">
          <div className="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
            {[...Array(3)].map((_, i) => (
              <div key={i} className="premium-card p-6 h-64 animate-pulse" />
            ))}
          </div>
        </div>
      </section>
    );
  }

  // Nothing to show is better than an empty shell with dangling headings.
  if (!active) return null;

  const SectionIcon = resolveIcon(active.icon_name);
  const [sectionBg, sectionFg] = active.color_class.split(" ");

  return (
    <>
      <section className="py-20 md:py-28">
        <div
          ref={ref}
          className={`container mx-auto px-4 md:px-8 transition-all duration-700 ${visible ? "opacity-100" : "opacity-0"}`}
        >
          <div className="text-center mb-10">
            <span className="section-label">Language Programs</span>
            <h2 className="section-title mt-3">Speak the World</h2>
            <p className="text-muted-foreground mt-4 max-w-2xl mx-auto">
              Exam-focused language training for study abroad, visa pathways, work routes and global careers.
            </p>
          </div>

          {/* Horizontal strip: stays one row however many sections exist, so extra
              languages never add vertical height on mobile. */}
          <div className="mb-10 -mx-4 px-4 md:mx-0 md:px-0">
            <div
              role="tablist"
              aria-label="Language sections"
              className="flex gap-1.5 overflow-x-auto snap-x snap-mandatory bg-muted rounded-2xl p-1.5 md:justify-center [scrollbar-width:none] [&::-webkit-scrollbar]:hidden"
            >
              {sections.map((section) => {
                const Icon = resolveIcon(section.icon_name);
                const isActive = section.slug === active.slug;

                return (
                  <button
                    key={section.slug}
                    ref={(el) => { tabRefs.current[section.slug] = el; }}
                    type="button"
                    role="tab"
                    aria-selected={isActive}
                    onClick={() => setActiveSlug(section.slug)}
                    className={`min-h-12 shrink-0 snap-start rounded-xl px-4 sm:px-5 text-xs sm:text-sm font-semibold transition-all duration-300 flex items-center justify-center gap-2 ${
                      isActive ? "bg-background text-foreground shadow-md" : "text-muted-foreground hover:text-foreground"
                    }`}
                  >
                    <Icon className="w-4 h-4 shrink-0" />
                    <span className="hidden sm:inline">{section.label}</span>
                    <span className="sm:hidden">{section.tab_label}</span>
                  </button>
                );
              })}
            </div>
          </div>

          <div className="mb-8 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
              <div className="inline-flex items-center gap-2 text-sm font-semibold text-accent mb-2">
                <SectionIcon className="w-4 h-4" />
                {active.tab_label}
              </div>
              <h3 className="font-heading text-2xl md:text-3xl font-extrabold text-foreground">{active.heading}</h3>
              {active.subtitle && <p className="text-muted-foreground mt-2 max-w-2xl">{active.subtitle}</p>}
            </div>
            <div className="text-sm font-semibold text-muted-foreground">
              {active.programs.length} {active.programs.length === 1 ? "program" : "programs"}
            </div>
          </div>

          <div className="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
            {active.programs.map((program) => {
              const CardIcon = resolveIcon(program.icon_name, SectionIcon);
              const [cardBg, cardFg] = (program.color_class || active.color_class).split(" ");

              return (
                <div
                  key={program.id}
                  className="premium-card p-6 group cursor-pointer"
                  onClick={() => {
                    setSelected(program);
                    setDrawerOpen(true);
                  }}
                >
                  <div className="flex items-start justify-between mb-5 gap-4">
                    <div className={`w-12 h-12 rounded-xl ${cardBg ?? sectionBg} flex items-center justify-center group-hover:scale-110 transition-transform duration-300`}>
                      <CardIcon className={`w-6 h-6 ${cardFg ?? sectionFg}`} />
                    </div>
                    {program.badge && (
                      <span className="text-[10px] font-bold uppercase tracking-wider px-2.5 py-1 rounded-full bg-accent/10 text-accent text-right">
                        {program.badge}
                      </span>
                    )}
                  </div>
                  <h4 className="font-bold text-foreground font-heading text-lg mb-2 group-hover:text-accent transition-colors">
                    {program.title}
                  </h4>
                  <div className="flex items-center gap-3 text-xs text-muted-foreground mb-4">
                    <span className="flex items-center gap-1"><Clock className="w-3.5 h-3.5" /> {program.duration}</span>
                  </div>
                  <p className="text-sm text-muted-foreground leading-relaxed mb-5">{program.description}</p>
                  <span className="inline-flex items-center gap-1.5 text-sm font-semibold text-accent group-hover:gap-2.5 transition-all">
                    Learn More <ArrowRight className="w-3.5 h-3.5" />
                  </span>
                </div>
              );
            })}
          </div>
        </div>
      </section>

      <DetailDrawer open={drawerOpen} onClose={() => setDrawerOpen(false)} title={selected?.title || "Language Program"}>
        {selected && (() => {
          const DrawerIcon = resolveIcon(selected.icon_name, SectionIcon);
          const [drawerBg, drawerFg] = (selected.color_class || active.color_class).split(" ");

          return (
            <div className="space-y-6">
              <div className={`w-16 h-16 rounded-2xl ${drawerBg ?? sectionBg} flex items-center justify-center`}>
                <DrawerIcon className={`w-8 h-8 ${drawerFg ?? sectionFg}`} />
              </div>
              <div>
                <h4 className="font-heading font-bold text-xl text-foreground mb-2">{selected.title}</h4>
                <p className="text-muted-foreground leading-relaxed">{selected.description}</p>
              </div>
              <div className="grid grid-cols-2 gap-4">
                <div className="bg-muted/50 rounded-xl p-4">
                  <div className="text-xs text-muted-foreground mb-1">Duration</div>
                  <div className="font-semibold text-foreground text-sm">{selected.duration}</div>
                </div>
                <div className="bg-muted/50 rounded-xl p-4">
                  <div className="text-xs text-muted-foreground mb-1">Certification</div>
                  <div className="font-semibold text-foreground text-sm flex items-center gap-1.5">
                    <Award className="w-4 h-4 text-accent" /> {selected.badge}
                  </div>
                </div>
              </div>
              {selected.benefits.length > 0 && (
                <div>
                  <h5 className="font-semibold text-foreground mb-3">What's Included</h5>
                  <ul className="space-y-2">
                    {selected.benefits.map((benefit) => (
                      <li key={benefit} className="flex items-start gap-2 text-sm text-muted-foreground">
                        <div className="w-1.5 h-1.5 rounded-full bg-accent mt-1.5 shrink-0" />
                        {benefit}
                      </li>
                    ))}
                  </ul>
                </div>
              )}
            </div>
          );
        })()}
      </DetailDrawer>
    </>
  );
};

export default LanguagesSection;
```

- [ ] **Step 6: Run test to verify it passes**

Run: `npx vitest run src/components/LanguagesSection.test.tsx`
Expected: PASS (4 tests).

- [ ] **Step 7: Verify the frontend as a whole**

Run: `npx vitest run`
Expected: PASS.

Run: `npx tsc --noEmit`
Expected: no errors. If `useLanguagePrograms` still references the removed `language_code` field, delete that hook — nothing consumes it now.

Run: `npm run build`
Expected: build succeeds.

- [ ] **Step 8: Commit**

```bash
git add src/types/api.ts src/hooks/api/index.ts src/components/LanguagesSection.tsx src/components/LanguagesSection.test.tsx
git commit -m "Render language tabs from the admin instead of a hardcoded array"
```

---

### Task 10: Mirror, deploy and verify end to end

**Files:**
- Modify: the outer `russellinternational-api` copy (mirror of every backend file touched in Tasks 2–8)
- Modify: `docs/superpowers/specs/2026-08-05-dynamic-language-sections-design.md` (record the `icon_name`-on-programs refinement)

**Interfaces:**
- Consumes: everything above.
- Produces: a verified production deployment.

- [ ] **Step 1: Mirror the backend changes to the outer codebase**

```bash
NEST=d:/russelinternational/russellsinternational/russellsinternational-api
OUT=d:/russelinternational/russellsinternational-api
cp $NEST/app/Models/LanguageSection.php $OUT/app/Models/
cp $NEST/app/Models/LanguageProgram.php $OUT/app/Models/
cp $NEST/app/Support/AdminChoices.php $OUT/app/Support/
cp $NEST/app/Http/Controllers/Api/LanguageSectionController.php $OUT/app/Http/Controllers/Api/
cp $NEST/app/Http/Controllers/Api/LanguageProgramController.php $OUT/app/Http/Controllers/Api/
cp $NEST/app/Filament/Resources/LanguageSectionResource.php $OUT/app/Filament/Resources/
cp -r $NEST/app/Filament/Resources/LanguageSectionResource $OUT/app/Filament/Resources/
cp $NEST/app/Filament/Resources/LanguageProgramResource.php $OUT/app/Filament/Resources/
cp $NEST/database/migrations/2026_08_05_*.php $OUT/database/migrations/
cp $NEST/database/seeders/LanguageSectionSeeder.php $OUT/database/seeders/
cp $NEST/database/seeders/LanguageProgramBackfillSeeder.php $OUT/database/seeders/
cp $NEST/tests/Feature/LanguageSection*.php $NEST/tests/Feature/LanguageProgram*.php $OUT/tests/Feature/
```

The outer copy has no `LanguagePageContent` hub, so do not copy that file. The
icon-sync test reads `base_path('../src/lib/icons.ts')`, which does not exist for
the outer copy — skip it there:

```php
        if (! is_file(base_path('../src/lib/icons.ts'))) {
            $this->markTestSkipped('The frontend registry lives beside the nested API copy only.');
        }
```

Add that guard to `test_the_icon_choices_match_the_frontend_registry` in **both**
copies before running the outer suite.

- [ ] **Step 2: Verify the outer suite**

```bash
cd d:/russelinternational/russellsinternational-api && php vendor/bin/phpunit
```
Expected: PASS. Fixtures there that set `language_code` need updating to `language_section_id`.

- [ ] **Step 3: Make migrations run on deploy — verify before trusting it**

This is the highest-risk step in the plan. The Railway service has **no start
command set**, and the repo's `nixpacks.toml` is dead config (Railway uses Railpack
here — `RAILPACK_PHP_*` variables are what take effect). Production has 23 of 23
migrations applied, but the newest dates from May and the media fix added none, so
nothing has actually exercised auto-migration on deploy.

If migrations do not run automatically, pushing this work ships code that queries
`language_sections` against a database without that table. Vercel and Railway both
deploy from the same `production` push, so the live Languages page would break.

Do not guess. Set an explicit start command so the ordering is guaranteed:

```bash
railway variables --set 'RAILPACK_PHP_ROOT_DIR=/app/public'
```

then in the Railway dashboard (Service → Settings → Deploy → Custom Start Command),
or via the API, set:

```
php artisan migrate --force && php artisan config:clear && <the Railpack default serve command>
```

Read the current default from the latest deploy log first — overwriting it with a
guessed serve command would take the site down. If the default cannot be determined,
use the safer alternative instead: leave the start command empty and run migrations
explicitly straight after the deploy in Step 4, checking the schema before declaring
success.

The frontend already degrades safely here: `useLanguageSections` returning nothing
makes `LanguagesSection` render `null`, so a missing table hides that one section
rather than erroring the page. That is a safety net, not a substitute for ordering.

- [ ] **Step 4: Back up production before deploying**

```bash
cd "C:/Users/HP/AppData/Local/Temp/claude/d--russelinternational/8e118020-fca7-4df7-baa5-3267ac4eeea7/scratchpad"
php pdo_dump.php sakura.proxy.rlwy.net 37288 railway root 'TFDainXJkYBOFGwEbBdSEJSEfddSewAX' railway_pre_language_sections.sql
```
Expected: a non-empty `.sql` file, with `language_programs` and `language_sections` row counts printed.

- [ ] **Step 5: Deploy**

```bash
cd d:/russelinternational/russellsinternational && git push production main
```

Wait for Railway to report SUCCESS, then confirm the deployed commit:

```bash
railway status
```

- [ ] **Step 6: Verify the API on production**

```bash
B=https://russellsinternational-production-production-c607.up.railway.app
curl -s --max-time 30 "$B/api/v1/language-sections" | head -c 1200
```
Expected: three sections (english, german, korean), each with programs, `tab_label` present, no `language_code` anywhere, and no program titled "Acton Kim".

- [ ] **Step 7: Verify the admin round trip on production**

In the browser at `$B/admin/website/language-page`:

1. Open **Language sections (tabs)** → confirm 3 sections with correct program counts.
2. Create a section: Tab name "Arabic Tests", heading "Arabic Language & Exams", icon Globe, colour Green, order 4. Save.
3. Confirm `/api/v1/language-sections` still returns 3 sections — the new one has no programs so it must be omitted.
4. Add a program to Arabic Tests, then confirm the API returns 4 sections and the Vercel site shows an Arabic tab.
5. Try to delete Arabic Tests while it has a program → expect the blocking notification and the section still present.
6. Delete the program, then delete the section → both gone.

- [ ] **Step 8: Verify mobile behaviour**

At 390px width on `/languages`: the tab strip is one row and swipes horizontally, the active tab scrolls into view, `document.documentElement.scrollWidth === clientWidth` (no horizontal overflow), and page height is no greater than the 2821px measured before this change.

- [ ] **Step 9: Re-run the admin regression crawl**

Re-run the 174-page admin sweep used during the earlier QA pass and confirm every page is HTTP 200 with no console errors and no broken thumbnails.

- [ ] **Step 10: Update the spec with the planning refinement**

The spec did not mention adding `icon_name` to `language_programs`. Add one line under **Data model** recording that programs carry an optional `icon_name` that falls back to the section's icon, so the imported demo programs keep their original per-card icons.

- [ ] **Step 11: Commit**

```bash
git add -A
git commit -m "Mirror language section changes to the outer copy and record the icon refinement"
git push production main
```

---

## Self-Review

**Spec coverage:**

| Spec requirement | Task |
|---|---|
| `language_sections` table, fields, conventions | 2 |
| `short_label` fallback | 2 (as `tab_label` accessor, so the admin field stays honest) |
| FK on programs, `nullOnDelete` | 3 |
| Backfill, `ielts` → English | 3 |
| Import 8 demos idempotently | 4 |
| Delete lorem record | 4 |
| Drop `language_code` | 5 |
| `/api/v1/language-sections`, nested, active-only, empty sections omitted | 6 |
| Shared icon registry + fallback | 1 |
| Swipeable tab strip | 9 |
| Remove `GROUPS` / `DEFAULT_PROGRAMS` / `normalizeGroup` | 9 |
| Admin section resource, pickers, helper text, program count | 7 |
| Delete guardrail | 7 |
| Hub card | 7 |
| Program form: relationship select, colour picker, `flag_emoji` clarity | 8 |
| Backend + frontend + browser + regression testing | every task, plus 10 |
| Both codebases in sync | 10 |
| Production backup before deploy | 10 |

No spec requirement is unassigned.

**Placeholder scan:** none — every code step contains the actual code, every test step names the command and the expected result.

**Type consistency:** `resolveIcon(name?, fallback?)` is defined in Task 1 and used with that signature in Tasks 1 and 9. `tab_label` is defined in Task 2 and consumed in Tasks 6 and 9. `AdminChoices::icons()` / `::colors()` are defined in Task 7 and consumed in Tasks 7 and 8. `language_section_id` and `icon_name` are added in Task 3 and used in Tasks 4, 6, 8 and 9. `LanguageSection.programs` is the nested key in Task 6's response and the field read in Task 9's component and test.

**Deviation from the spec, recorded deliberately:** the spec said `short_label` would fall back "in a model accessor". Overriding `short_label` itself would make the admin form display the label as though it were saved, so the fallback is exposed as a separate `tab_label` accessor and the raw column stays blank. Task 2's test asserts both halves of this.
