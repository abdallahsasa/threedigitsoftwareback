<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('testimonials', function (Blueprint $table) {
            $table->id();
            $table->json('client_name');
            $table->json('company');
            $table->json('position')->nullable();
            $table->json('quote');
            $table->string('photo')->nullable();
            $table->string('company_logo')->nullable();
            $table->integer('rating')->default(5);
            $table->foreignId('project_id')->nullable()->constrained()->nullOnDelete();
            $table->string('language')->nullable();
            $table->string('status')->default('draft');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('testimonials');
    }
};
