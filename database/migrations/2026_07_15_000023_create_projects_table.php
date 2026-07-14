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
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->json('name');
            $table->json('slug');
            $table->string('client_name')->nullable();
            $table->foreignId('project_category_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('industry_id')->nullable()->constrained()->nullOnDelete();
            $table->string('launch_year')->nullable();
            $table->string('live_url')->nullable();
            $table->json('overview')->nullable();
            $table->json('business_challenge')->nullable();
            $table->json('proposed_solution')->nullable();
            $table->json('engineering_challenges')->nullable();
            $table->json('results')->nullable();
            $table->string('main_image')->nullable();
            $table->string('desktop_mockup')->nullable();
            $table->string('mobile_mockup')->nullable();
            $table->string('preview_video')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->string('status')->default('draft'); // draft, published, archived
            $table->integer('order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
