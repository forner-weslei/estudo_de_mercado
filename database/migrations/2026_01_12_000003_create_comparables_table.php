<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('comparables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('study_id')->constrained('studies')->cascadeOnDelete();
            $table->string('title')->nullable();
            $table->string('address')->nullable();
            $table->decimal('price', 12, 2);
            $table->decimal('area_m2', 10, 2);
            $table->unsignedSmallInteger('bedrooms')->nullable();
            $table->unsignedSmallInteger('suites')->nullable();
            $table->unsignedSmallInteger('parking')->nullable();
            $table->unsignedSmallInteger('bathrooms')->nullable();
            $table->boolean('include_in_calc')->default(true);
            $table->string('main_photo_path')->nullable();
            $table->unsignedInteger('sort_order')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comparables');
    }
};
