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
            $table->string('label');                    // tab text on desktop
            $table->string('short_label')->nullable();  // tab text on mobile
            $table->string('heading');                  // heading under the tabs
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
