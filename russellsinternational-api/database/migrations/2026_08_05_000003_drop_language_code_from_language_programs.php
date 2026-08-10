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

    /**
     * Recreates the column but not its values — those are gone with the drop. The
     * section relation carries the same grouping, so nothing is functionally lost;
     * take a database backup before deploying if the old codes still matter.
     */
    public function down(): void
    {
        if (Schema::hasColumn('language_programs', 'language_code')) {
            return;
        }

        Schema::table('language_programs', function (Blueprint $table) {
            $table->string('language_code')->nullable();
        });
    }
};
