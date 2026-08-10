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

        // From here on the section relation is the source of truth and nothing
        // writes language_code. It is NOT NULL with no default, so leaving it
        // required would break every create until it is dropped.
        if (Schema::hasColumn('language_programs', 'language_code')) {
            Schema::table('language_programs', function (Blueprint $table) {
                $table->string('language_code')->nullable()->default(null)->change();
            });
        }

        // Seeds the sections it needs, then maps each program onto one. Kept in a
        // seeder so tests can call it directly — re-running this migration is not
        // possible once it has been applied.
        (new LanguageProgramSectionBackfillSeeder)->run();
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
