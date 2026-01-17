<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('studies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->string('owner_name');
            $table->string('owner_contact')->nullable();

            $table->string('subject_address');
            $table->string('subject_neighborhood')->nullable();
            $table->string('subject_city');
            $table->string('subject_state', 2);
            $table->decimal('subject_area_m2', 10, 2);
            $table->unsignedSmallInteger('subject_bedrooms')->nullable();
            $table->unsignedSmallInteger('subject_suites')->nullable();
            $table->unsignedSmallInteger('subject_parking')->nullable();
            $table->text('notes')->nullable();

            $table->string('scenario_base')->default('avg_total'); // avg_total, avg_m2, lowest_total, manual_total
            $table->decimal('manual_total_price', 12, 2)->nullable();
            $table->decimal('pct_optimistic', 6, 2)->default(5);
            $table->decimal('pct_market', 6, 2)->default(0);
            $table->decimal('pct_competitive', 6, 2)->default(-8);

            $table->boolean('override_branding')->default(false);
            $table->string('brand_company_name')->nullable();
            $table->string('brand_agent_name')->nullable();
            $table->string('brand_creci')->nullable();
            $table->string('brand_phone')->nullable();
            $table->string('brand_whatsapp')->nullable();
            $table->string('brand_email')->nullable();
            $table->string('brand_website')->nullable();
            $table->string('brand_footer_text')->nullable();
            $table->string('brand_color_primary')->nullable();
            $table->string('brand_color_secondary')->nullable();
            $table->string('brand_logo_path')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('studies');
    }
};
